<?php
//namespace Wednesday\Form\Element;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    Application\Entities\Resources,
//    Wednesday\Renderers\GridRenderer,
//    Wednesday\Renderers\ResourceHtml,
//    Wednesday\Mapping\Form\EntityFormRenderer,
    \Wednesday_Form_Element_GridPicker as GridRenderer,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Controller_Front as Front;

/**
 * Description of GalleryPicker
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_GroupLookList extends Zend_Form_Element {
    const COLLECTION = "Application\Entities\Collections";
    /**
     * Use formHidden view helper by default
     * @var string
     */
    public $helper = 'formHidden';

    private $collectionId;

    /**
     * Initialize object; used by extending classes
     *
     * @return void
     */
    public function init()
    {
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $this->collectionId = $this->getValue();
        if(empty($this->collectionId)===false){
            $collection = $em->getRepository(self::COLLECTION)->findOneById($this->collectionId);
        }
        $label = $this->getLabel();
        $this->setLabel($label);
    }

    protected function renderExtras($value) {
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $log = $bootstrap->getResource('Log');
        $renderHtml = '';
        $decorators = array(
            'ViewHelper',
            'Errors',
            array('DivNestWrapper'/*, array('class' => 'controls')*/),
            //'Label',
            new Zend_Form_Decorator_HtmlTag(array('tag' => 'div', 'class' => 'control group'))
        );
        $elemid = $this->getId();
        $modalid = $this->getName()."-modal";

        if(empty($this->collectionId)===false){
            $collection = $em->getRepository(self::COLLECTION)->findOneById($this->collectionId);
            
            $gridelement = new GridRenderer(array(
                            'name' => 'grid',
                            'label' => 'Select Grid',
                            'class' => 'input-medium',
                            'value' => $collection->grid->id,
                            'required' => false
                        ));
            $gridelement->clearDecorators()->addPrefixPath('Wednesday_Form_Decorator', 'Wednesday/Form/Decorator/', 'decorator')->addDecorators($decorators);
            $gridHtml .= '<div class="span6">'."\n";
            $gridHtml .= $gridelement->render();
            $gridHtml .= '</div>';
//            $gridRenderer = new GridRenderer($collection->grid, array('type' => 'admin'));
//            $gridHtml .= '<div class="span6">'."\n";
//            $gridHtml .= '<div class="grid-flexible-container">'."\n";
//            $gridHtml .= $gridRenderer;
//            $gridHtml .= '</div>';
//            $gridHtml .= '</div>';
            $resourceKeys = '';
            $listGenerated= '';
            foreach ($collection->imagery as $groupLook) {
                $resourceKeyArray[] = $groupLook->id;
                $listGenerated .= "<li class='ui-state-default resource' id='{$groupLook->id}'><span class=\"count\"></span>{$this->getView()->resource($groupLook->featured,array('renderer' => 'uri', 'class' => 'newsthumb','height'=> '70'))} <span>{$groupLook->title}</span>
                                   </li>";
            }

            $resourceKeys = implode(',', $resourceKeyArray);
            $resourceKeys = rtrim($resourceKeys, ',');
        }

        $renderHtml = <<<HTML
			<div class="look-imagery" id="collectionid-{$this->collectionId}">
                {$gridHtml}
                <div class="ui-sortable span5">
                    <input type="hidden" name="{$elemid}[imagery]" class="imagery" id= "{$elemid}-imagery-{$this->collectionId}" value="{$resourceKeys}" />
                    <ul class="{$elemid}-element-selecable looks-grid" >
                        {$listGenerated}
                    </ul>
                </div>
            </div>
HTML;

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
//            if(get_class($decorator) == 'Zend_Form_Decorator_ViewHelper') {
            if(get_class($decorator) == 'EasyBib_Form_Decorator_BootstrapTag') {
                $content = $content.$this->renderExtras($this->getValue());
            }
            $content = $decorator->render($content);
        }
        return $content;
    }
}