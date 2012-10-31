<?php
namespace Wednesday\Form\Element\Groups;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    Application\Entities\Resources,
    Wednesday\Renderers\ResourceHtml,
    Wednesday\Mapping\Form\EntityFormRenderer,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Form as Form,
    \Zend_Form_SubForm as SubForm,
    \Zend_Controller_Front as Front,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper;

/**
 * Description of StandardFooter
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author mrhelly
 */
class WizardFooter extends SubForm {

    const CSSCLASS = "actions";
    const NAME = "actions";

    /**
     * Load the default decorators
     *
     * @return Zend_Form_SubForm
     */
    public function loadDefaultDecorators() {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }
        $this->clearDecorators();
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class' => self::CSSCLASS)),
            'Fieldset',
        ))->setElementDecorators(array(
            'ViewHelper'
        ));
        //->setElementDecorators(WednesdayForm::getElementDecorators());

        return $this;
    }

    /**
     *
     */
    public function init() {
        $this->setName(self::NAME);
        
        #Submit button
        $this->addElement('button', 'previous', array(
            'label' => "Previous",
            'type' => 'button',
            'class' => 'btn previous',
            'disabled'=>'disabled',
            'ignore' => true,
        ));
        $this->addElement('button', 'next', array(
            'label' => "Next",
            'type' => 'button',
            'class' => 'btn next primary pull-right',
            'enabled'=>'enabled',
            //'data-controls-modal' => 'asset-manager',
            'ignore' => true,
        ));
    }

}