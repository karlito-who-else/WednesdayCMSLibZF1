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
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_HomepageitemsPicker extends Zend_Form_Element {
    const GALLERY = "Application\Entities\MediaGalleries";
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
    public function init()
    {
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $galleryId = $this->getValue();
        if(empty($galleryId)===false){
            $galleryInstance = $em->getRepository(self::GALLERY)->findOneById($galleryId);
        }
        //$galleryInstance->title." ".
        $label = $this->getLabel();
        $this->setLabel($label);
    }

    protected function renderExtras($value) {
//        $jqnc = JQueryViewHelper::getJQueryHandler();
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $log = $bootstrap->getResource('Log');
        $renderHtml = '';

        $elemid = $this->getId();
        $modalid = $this->getName()."-modal";
        $galleryId = $this->getValue();
//        $log->debug('GalleryPicker::[elem: '.$elemid."][model:".$modalid."][val:".$galleryId."]");
//        $log->debug($galleryId);
        if(empty($galleryId)===false){
            $galleryInstance = $em->getRepository(self::GALLERY)->findOneById($galleryId);
            //$galleryFeature = $this->getView()->partial('partials/items/mediaresource.phtml', array('entity'=>(object) array('resource'=>$galleryInstance->featured)));
            $galleryItems = $this->getView()->partialLoop('partials/items/mediagriditems.phtml', $galleryInstance->items);
            $resourceKeys = '';
            foreach ($galleryInstance->items as $resource) {
                $resourceKeyArray[] = $resource->resource->id;//"{},";
            }
            
//            array_reverse($resourceKeyArray);
            
            $resourceKeys = implode(',', $resourceKeyArray);
            
            $resourceKeys = rtrim($resourceKeys, ','); 
        }

        //<label>{}</label>
        $renderHtml = <<<SCR
			<div class="gallery" id="gallery-{$galleryId}">
				<button type="button" class="btn manage-assets" data-toggle="modal" href="#asset-manager" data-modal-type="assets">Choose From Asset Manager</button>
				<p class="help-block">Upload assets into this gallery, or choose from the the assets that have already been uploaded.</p>
				<br />
				<h4>Gallery Sorter</h4>
                <p class="help-block">Drag and drop the asset thumbnails to set their display order within the gallery.</p>
                <br />
	            <div class="well">
					<ul class="thumbnails">
						{$galleryItems}
					</ul>
				</div>
                <input type="hidden" name="{$elemid}[featured][{$galleryId}]" id= "{$elemid}-featured-{$galleryId}" value="{$galleryInstance->featured->id}" class="featured-resource" />
                <input type="hidden" name="{$elemid}[resources][{$galleryId}]" id= "{$elemid}-resources-{$galleryId}" value="{$resourceKeys}" class="resources" />
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
    public function render(Zend_View_Interface $view = null)
    {
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
            if(get_class($decorator) == 'EasyBib_Form_Decorator_BootstrapTag') {
//            if(get_class($decorator) == 'Zend_Form_Decorator_ViewHelper') {
                $content = $content.$this->renderExtras($this->getValue());
            }
            $content = $decorator->render($content);
        }
        return $content;
    }
}
