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
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
class CoreFields extends SubForm {

    const CSSCLASS = "basic";

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

        return $this;
    }

    /**
     *
     */
    public function init() {
        $this->setLegend('Main Article');
        $this->addElement('text', 'title', array(
            'label' => 'Title',
            'class' => 'span13',
            'required' => true,
            'filters' => array('StringTrim'),
        ));
        $this->addElement('textarea', 'summary', array(
            'label' => 'Summary',
            'class' => 'wysiwyg custom-headers span13',
            'rows' => '5',
            'required' => true,
            'filters' => array('StringTrim'),
        ));
        $summary = $this->getElement('summary');
        $summary->addDecorator('HelpBlock', array('text' => 'The summary will appear on the article list page only. Recommended summary length: 20-30 words. If pasting content from Word, please use the 5th icon along - indicated by the W icon.'));
        $this->addElement('textarea', 'description', array(
            'label' => 'Description',
            'class' => 'wysiwyg custom-headers span13',
            'rows' => '5',
            'required' => false,
            'filters' => array('StringTrim'),
        ));
        $description = $this->getElement('description');
        $description->addDecorator('HelpBlock', array('text' => 'The description will appear on the article detail page. If pasting content from Word, please use the 5th icon along - indicated by the W icon.'));
    }

}
