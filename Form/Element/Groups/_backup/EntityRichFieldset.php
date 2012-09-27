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
    \Zend_Controller_Front as Front;

/**
 * Description of StandardFooter
 * GalleriesFieldset
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
class EntityRichFieldset extends SubForm {

    const CSSCLASS = "form-galleries";
    const NAME = "galleries";

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
            //'Fieldset',
        ))->setElementDecorators(array(
            'ViewHelper'
        ));

        return $this;
    }

    /**
     *
     */
    public function init() {
        $this->setLegend('Galleries');//galleries Footer Controls');
        $this->setName(self::NAME);
//        #Submit button
//        $this->addElement('button', 'submit', array(
//            'label' => "Save changes",
//            'type' => 'submit',
//            'class' => 'btn btn-primary',
//            'ignore' => true,
//        ));
//        #Pick button
//        $this->addElement('button', 'pick', array(
//            'label' 	=> 'Pick Element',
//            'type' 		=> 'backbone modal',
//            'class'     => 'btn btn-warning btn-backbone ',
//            'ignore' 	=> true
//        ));
//        #Reset button
//        $this->addElement('button', 'reset', array(
//            'label' 	=> 'Exit Without Saving',
//            'type' 		=> 'reset',
//            'class'     => 'btn btn-warning',
//            'ignore' 	=> true
//        ));

//        #Delete button
//        $this->addElement('button', 'confirm-deletion-btn', array(
//            'label' => 'Delete',
//            'type' => 'submit',
//            'class' => 'btn btn-danger delete pull-right',
//            'ignore' => true,
//        ));

        $this->addElement('textarea', 'summary', array(
            'label' => 'Summary',
            'class' => 'wysiwyg custom-headers span8',
            'description'=> 'The summary will appear on the article list page only. Recommended summary length: 20-30 words. If pasting content from Word, please use the 5th icon along - indicated by the W icon.',
            'rows' => '5',
            'required' => true,
            'filters' => array('StringTrim'),
        ));

        $summary = $this->getElement('summary');
//        $summary->addDecorator('HelpBlock', array('text' => 'The summary will appear on the article list page only. Recommended summary length: 20-30 words. If pasting content from Word, please use the 5th icon along - indicated by the W icon.'));
        $summary->setDescription('The summary will appear on the article list page only. Recommended summary length: 20-30 words. If pasting content from Word, please use the 5th icon along - indicated by the W icon.');
//EntityRichFieldset
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
