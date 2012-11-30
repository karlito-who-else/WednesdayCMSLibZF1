<?php

//namespace Wednesday\Form\Element;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    Application\Entities\Resources,
    Wednesday\Renderers\ResourceHtml,
    Wednesday\Mapping\Form\EntityFormRenderer,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Controller_Front as Front;

/**
 * Description of GalleryPicker
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_LookShorter extends Zend_Form_Element {
    const GALLERY = "Application\Entities\MediaGalleries";
    const LOOKS = "Application\Entities\Looks";
    const COLLECTION = "Application\Entities\Collections";
    const ENTITY_NAMESPACE  = "Application\Entities\\";
    const WEDMODEL_NAMESPACE  = "Wednesday\Models\\";

    /**
     * Use formHidden view helper by default
     * @var string
     */
    public $helper = 'formHidden';

    /**
     * Initialize object; used by extending classes
     *
     * @return void
     */
    public function init() {
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $collectionId = $this->getValue();
        if (empty($collectionId) === false) {
            $collection = $em->getRepository(self::COLLECTION)->findOneById($collectionId);
        }
//        die(var_dump($collection->title));
        //$collection->title." ".
        $label = $this->getLabel();
        $this->setLabel($label);
    }

    protected function renderExtras($value, $attributes) {
//        $jqnc = JQueryViewHelper::getJQueryHandler();
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $config = $bootstrap->getContainer()->get('config');
       
        
        
        $log = $bootstrap->getResource('Log');
        $renderHtml = '';

        $elemid = $this->getId();
        $modalid = $this->getName() . "-modal";
        $collectionId = $this->getValue();
        $collection = $em->getRepository(self::COLLECTION)->findOneById($collectionId);

        
        
        
        $looksList = "";
        $looksList ='<div id="looksThumbnails">';
        $looksList .= '   <ul  class="thumbnails ui-sortable">';
        $looks =  $em->getRepository(self::LOOKS)->getCollectionRelated($collection->id);
        foreach ($looks as $look)
        {
            $productIds = array();
            foreach ($look->products as $product){
                array_push($productIds, $product->id);
            }
            
//            <i class="icon-edit" rel="tooltip" data-original-title="Edit this items settings"></i>
            $templateVars = array(  
                'url' => $look->featured->link,
                'title' => $look->title, 
                'slugTitle' => $look->slugtitle,
                'span' => 'span2',
                'icon' =>   array(
                                'look-editor'=>array(
                                    'modalClass' => 'icon-edit look-editor', 
                                    'iconTitle' =>'Edit Look'
                                ),
//                                'group-look-image-editor'=>array(
//                                    'modalId' => 'asset-manager',
//                                    'modalClass'=>'icon-picture manage-assets', 
//                                    'iconTitle' =>'Change Image',
//                                    'modalData' =>array(
//                                        'modal-type'=>'single',
//                                        'toggle'=>'modal'
//                                    )
//                                )
                            ), 
                'inputs' => array(
                                'lookID'=>array(
                                    'class'=>'look-id',
                                    'type'=>'hidden',
                                    'value'=> $look->id
                                ),
                                'productsID'=>array(
                                    'class'=>'products-id',
                                    'type'=>'hidden',
                                    'value'=> implode(',', $productIds)
                                ),
                                'order'=>array(
                                    'class'=>'look-order',
                                    'type'=>'hidden',
                                    'value'=> $look->order
                                ),
                    
                                'lookLink'=>array(
                                    'class'=>'look-link',
                                    'type'=>'hidden',
                                    'value'=> $look->link
                                ),
                                'resourceID'=>array(
                                    'class'=>'look-resource',
                                    'type'=>'hidden',
                                    'value'=> $look->featured->id
                                )
                            )
            );
            $looksList .= $this->getView()->partial('partials/items/generic-thumbnail.phtml', $templateVars);
            
        }
        $looksList .= "   </ul>";
        $looksList .= "</div>";
        
        
        $renderHtml = <<<SCR
        <div id="grouplookPicker">
            <div class="container gallery-container">
                <div class="row">
                    <div id="lookList" class="span4 gallery-thumbnails">
                        <div class="fieldlist-desccription">
                            <h3>Looks List</h3>
                            <a data-original-title="Product Creation" data-placement="bottom" html="true" href="http://en.wikipedia.org/wiki/Slug_(web_publishing)" rel="popover preview" data-content="
                                This will result in a product being created
                                &lt;br/&gt;&lt;br/&gt;
                                Donec ullamcorper nulla non metus auctor fringilla.&lt;br/&gt;&lt;br/&gt;
                                Donec ullamcorper nulla non metus auctor fringilla.
                                " class="notice" target="_blank">
                                <i class="icon-info-sign"></i>
                            </a>These, below, are a listings of your current saved products. To create a new product, please click on the "New Product" button located on the left.

                            <br/>
                            <a data-original-title="Updating Existing" data-placement="bottom" html="true" href="http://en.wikipedia.org/wiki/Slug_(web_publishing)" rel="popover preview" data-content="
                                Maecenas sed diam eget risus varius blandit sit amet non magna.
                                &lt;br/&gt;&lt;br/&gt;
                                Donec ullamcorper nulla non metus auctor fringilla.&lt;br/&gt;&lt;br/&gt;
                                Donec ullamcorper nulla non metus auctor fringilla.
                                " class="notice" target="_blank">
                                <i class="icon-info-sign"></i>
                            </a>To edit an exitisting item please click the edit icon<i class="icon-edit"></i> located within each thumbnail.
                        </div>
                        {$looksList}
                    </div>
                    <div class="span5 offset1">
                        <div class="grid-preview-controls">
                            <div class="control-group">
                                <button href="#group-look-editor" data-toggle="modal" data-modal-type="primary" data-id="" class="btn btn-success create-look" id="grids-items-add" type="button">Create Group Look</button>
                            </div>
                        </div>
                        <div id="lookProductList" class="item-editor">
                    </div>
                    </div>
                </div>
            </div>
        </div>
SCR;

        return $renderHtml;
    }

    protected function renderGallery($id) {

    }

    /**
     * Render form element
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null) {
        if ($this->_isPartialRendering) {
            return '';
        }

        if (null !== $view) {
            $this->setView($view);
        }

        $content = '';
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
//            if(get_class($decorator) == 'Zend_Form_Decorator_HtmlTag') {
//            if(get_class($decorator) == 'Zend_Form_Decorator_ViewHelper') {
            if (get_class($decorator) == 'EasyBib_Form_Decorator_BootstrapTag') {
                $content = $content . $this->renderExtras($this->getValue(), $this->getAttribs());
            }
            $content = $decorator->render($content);
        }
        return $content;
    }

}
