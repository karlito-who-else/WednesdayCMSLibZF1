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
class Wednesday_Form_Element_GroupListShorter extends Zend_Form_Element {
    const GALLERY = "Application\Entities\MediaGalleries";
    const LOOKS = "Application\Entities\Looks";
    const IMAGERY       = "Application\Entities\GroupedLooks";
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

        
        
        
        $groupedLooksList = "";
        $groupedLooksList ='<div id="groupedLooksThumbnails">';
        $groupedLooksList .= '   <ul  class="thumbnails ui-sortable">';
        $groupedLooks =  $em->getRepository(self::IMAGERY)->getByCollection($collection->id);
        $groupedLookIDs = array();
        foreach ($collection->imagery as $groupedLook)
        {
            array_push($groupedLookIDs, $groupedLook->id);
            $lookIds = array();
            foreach($groupedLook->looks as $look)
            {
                array_push($lookIds, $look->id);
            }
            
            
            $templateVars = array(  
                'url' => $groupedLook->featured->link,
                'thumbnailId' => 'groupedLook'.$groupedLook->id,
                'icon' => array('group-look-edit'=>array('modalId' => 'grouped-look-picker','modalClass'=>'edit-grouped-look', 'iconTitle' =>'Edit Grouped Look')), 
                'title' => $groupedLook->title, 
                'slugTitle' => $groupedLook->slugtitle,
                'span' => 'span2',
                'icon' => array(
//                                    'group-look-image-editor'=>array(
//                                                            'modalId' => 'asset-manager',
//                                                            'modalClass'=>'icon-picture manage-assets', 
//                                                            'iconTitle' =>'Edit Grouped Look',
//                                                            'modalData' =>array('modal-type'=>'single',
//                                                                                'toggle'=>'modal')
//                                                            ),
                    'group-look-edit'=>array(
//                        'modalId' => 'grouped-look-picker',
                        'modalClass'=>'icon-edit edit-grouped-look non-modal', 
                        'iconTitle' =>'Edit Grouped Look'),
//                    'group-look-image-editor'=>array(
//                        'modalId' => 'asset-manager',
//                        'modalClass'=>'icon-picture grouped-look manage-assets', 
//                        'iconTitle' =>'Change Image',
//                        'modalData' =>array(
//                            'modal-type'=>'single',
//                            'toggle'=>'modal'
//                        )
//                    )
                ), 
                'inputs' => array(
                                'looks-ids'=>array(
                                    'class'=>'grouped-look-ids',
                                    'type'=>'hidden',
                                    'value'=> implode(',', $lookIds)
                                ),
                                'id'=>array(
                                    'class'=>'id',
                                    'type'=>'hidden',
                                    'value'=>$groupedLook->id
                                )
                            )
            );
            
            
            
            
            $groupedLooksList .= $this->getView()->partial('partials/items/generic-thumbnail.phtml', $templateVars);
            
        }
        $groupedLooksList .= "   </ul>";
        $groupedLooksList .= "</div>";
        $groupedLookIDs = implode(',', $groupedLookIDs);
        
        $renderHtml = <<<SCR
        <div id="grouplookPicker">
            <div class="container gallery-container">
                <div class="row">
                    <div id="groupedLookList" class="span4 gallery-thumbnails">
                        <input type="hidden" name="groupedLooks[order]" class="grouped-look-list-ids" value="{$groupedLookIDs}">
                        <div class="grid-preview-controls">
                            <div class="control-group">
                                <button class="btn create-grouped-look non-modal" type="button">create New Grouped Look</button>
        </div>
                        </div>
                        {$groupedLooksList}
                    </div>
                    <div id="lookRelated" class="span5">
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
