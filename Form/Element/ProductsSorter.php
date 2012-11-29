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
class Wednesday_Form_Element_ProductsSorter extends Zend_Form_Element {
    const PRODUCTS = "Application\Entities\products";
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

        
        $productList = "";
        $productList ='<div>';
        $productList .= '   <ul id="productsThumbnails" class="thumbnails ui-sortable">';
        $products =  $em->getRepository(self::PRODUCTS)->getByCollection($collection,'title');
        foreach ($products as $product)
        {
            $resIds = array();
            foreach($product->showimages as $image)
            {
                array_push($resIds, $image->id);
            }
            $templateVars = array(  
                'url' => $product->showimages[0]->link,
                'title' => $product->title, 
                'slugTitle' => $product->itemtype,
                'span' => 'span2',
                'icon' =>   array(
                                'product-editor'=>  array(
                                    'modalClass'=>'icon-edit product-editor', 
                                    'iconTitle' =>'Edit Product'
                                ),
//                                'group-look-image-editor'=> array(
//                                    'modalId' => 'asset-manager',
//                                    'modalClass'=>'icon-picture manage-assets-thumbnail', 
//                                    'iconTitle' =>'Edit Image',
//                                    'modalData' =>array('modal-type'=>'single',
//                                                                                'toggle'=>'modal')
//                                )
                ),
                'inputs' => array(
                                'id'=>array(
                                    'class'=>'data-id',
                                    'type'=>'hidden',
                                    'value'=>$product->id
                                ),
                                'slug'=>array(
                                    'class'=>'data-slug',
                                    'type'=>'hidden',
                                    'value'=> $product->slug
                                ),
                                'monclerSKU'=>array(
                                    'class'=>'data-moncler-sku',
                                    'type'=>'hidden',
                                    'value'=> $product->monclersku
                                ),
                                'yookSKU'=>array(
                                    'class'=>'data-yoox-sku',
                                    'type'=>'hidden',
                                    'value'=> $product->yooxsku
                                ),
                                'type'=>array(
                                    'class'=>'data-type',
                                    'type'=>'hidden',
                                    'value'=> $product->itemtype
                                ),
                                'asset-id'=>array(
                                    'class'=>'data-asset-id',
                                    'type'=>'hidden',
                                    'value'=>  implode(',', $resIds)
                                ),
                            )
            );
            $productList .= $this->getView()->partial('partials/items/generic-thumbnail.phtml', $templateVars);
            
        }
        $productList .= "   </ul>";
        $productList .= "</div>";
        
        
        $renderHtml = <<<SCR
        <div class="container gallery-container">
            <div class="row">
        
                <div id="productList" class="span4 gallery-thumbnails">
                    <div>
                    test descriptions
                    </div>
                    {$productList}
                </div>

                <div class="span5  offset1">
                    <div class="grid-preview-controls">
                        <div class="control-group">
                            <button class="btn btn-success add-product" type="button">New Product</button>
                        </div>
                    </div>
                    <div id="productViewEditor" class="item-editor">
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
