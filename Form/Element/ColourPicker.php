<?php
//namespace Wednesday\Form\Element;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    Application\Entities\Resources,
    \Wednesday\Renderers\GridRenderer,
    \EasyBib_Form,
    \EasyBib_Form_Decorator as EasyBibFormDecorator,        
    \Zend_Form_Element_Select,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Controller_Front as Front;

/**
 * Description of ResourcePicker
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_ColourPicker extends Zend_Form_Element {
    const GRIDS         = "Application\Entities\Grids";

    /**
     * Use formHidden view helper by default
     * @var string
     */
    public $helper = 'formText';

    /**
     * Initialize object; used by extending classes
     *
     * @return void
     */
    public function init()
    {
//        $this->setValue($this->getName());
    }

    protected function renderExtras($value) {
        $jqnc = JQueryViewHelper::getJQueryHandler();
        $name = "bgcolour";
        $elemid = 'bgpicker-'.$this->getId();
        $jqnc = JQueryViewHelper::getJQueryHandler();
        $this->getView()->headLink()->appendStylesheet('/library/js/farbtastic/v1.2/farbtastic.css', 'screen');
        $value = $this->getValue();
        if(isset($value)===false) {
            $value = "#ff00ff";
            $this->setValue($value);
        }
        $inlineScript = <<<SOA
        /* <![CDATA[ */
            \$LAB
            .script(window.CMS.config.site.uri + 'library/js/farbtastic//v1.2/farbtastic.js')
            .wait
            (        
              function()
                {
                    {$jqnc}('#{$elemid}').farbtastic('#{$this->getId()}');
                }
            );
        /* ]]> */
SOA;
        $this->getView()->inlineScript()->appendScript($inlineScript, 'text/javascript');
        $renderHtml = <<<EOT
                        <div id="{$elemid}"></div>
EOT;
        return $renderHtml;
        
        
        
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