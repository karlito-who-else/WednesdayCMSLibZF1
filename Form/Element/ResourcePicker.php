<?php

//namespace Wednesday\Form\Element;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    \Application\Entities\Resources,
    \Wednesday\Renderers\ResourceHtml,
    \Wednesday\Mapping\Form\EntityFormRenderer,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Controller_Front as Front;

/**
 * Description of ResourcePicker
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_ResourcePicker extends Zend_Form_Element {

    const RESOURCE = "Application\Entities\MediaResources";

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
        
    }

    protected function renderExtras($value) {
//        $jqnc = JQueryViewHelper::getJQueryHandler();
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
//        $log = $bootstrap->getResource('Log');
        $renderHtml = '';
        $elemid = $this->getId();
        $resourceId = $this->getValue();

//        $log->info($elemid." - ".$resourceId." = ".$value);
        
        if (empty($resourceId) === false) {
            $resourceInstance = $em->getRepository(self::RESOURCE)->findOneById($resourceId);
            $resourceFeature = $this->getView()->partial('partials/items/mediaresource.phtml', array('entity' => (object) array('resource' => $resourceInstance)));
        } else {
            $resourceFeature = $this->getView()->partial('partials/items/mediaresource.phtml');
            $resourceId = 0;
            $resourceInstance = (object) array('id' => $resourceId);
        }

        $renderHtml = <<<SCR
			<div class="gallery" id="resource-{$resourceId}">
                <p><button type="button" class="btn manage-assets" data-toggle="modal" href="#asset-manager" data-modal-type="single">Choose From Asset Manager</button></p>
                <span class="help-block">Upload assets or choose from the the assets that have already been uploaded.</span>

                <p class="help-block">Choose the featured image for this page.</p>
                <div class="row">
                    {$resourceFeature}
                </div>
                <input type="hidden" name="{$elemid}[resource]" id= "{$elemid}-resource-{$resourceId}" value="{$resourceInstance->id}" class="featured-resource" />
            </div>
SCR;

        return $renderHtml;
    }

    protected function renderResource($id) {
        
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
                $content = $content . $this->renderExtras($this->getValue());
            }
            $content = $decorator->render($content);
        }
        return $content;
    }

}
