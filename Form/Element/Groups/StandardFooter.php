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
    \Wednesday_Form_Form as WednesdayForm,
    \Zend_Controller_Front as Front;

/**
 * Description of StandardFooter
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
class StandardFooter extends SubForm {

    const CSSCLASS = "form-actions";
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
            array('HtmlTag', array('tag' => 'footer', 'class' => self::CSSCLASS)),
            //'Fieldset',
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
        $this->setLegend('Footer Controls');
        $this->setName(self::NAME);
        #Submit button
        $this->addElement('button', 'submit', array(
            'label' => "Save changes",
            'type' => 'submit',
            'class' => 'btn btn-primary',
            'ignore' => true,
        ));
        #Reset button
        $this->addElement('button', 'exit-without-saving', array(
            'label' 	=> 'Exit Without Saving',
            'type' 		=> 'reset',
            'class'     => 'btn btn-warning',
            'onClick'   => 'document.location.replace($(this).attr("href"))',
            'ignore' 	=> true
        ));
        #Delete button
        $this->addElement('button', 'confirm-deletion-btn', array(
            'label' => 'Delete',
            'type' => 'submit',
            'class' => 'btn btn-danger delete pull-right',
            'ignore' => true,
        ));
    }
    
    /**
     * Add "Clone" button to the form buttons
     */
    public function addCloneButton(){
        $this->addElement('button', 'clone', array(
            'label' => "Clone this page",
            'type' => 'submit',
            'class' => 'btn',
            'ignore' => true,
        ));        
    }
}
