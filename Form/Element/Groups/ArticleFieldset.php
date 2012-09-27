<?php
namespace Wednesday\Form\Element\Groups;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    \Wednesday_Form_Element_CategoryPicker,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Form as Form,
    \Zend_Form_SubForm as SubForm,
    \Wednesday_Form_Form as WednesdayForm,
    \Zend_Controller_Front as Front;

/**
 * Description of TaxonomyFieldset
 * TaxonomyFieldset
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
class ArticleFieldset extends FormGroupAbstract {

    const CSSCLASS = "fieldset-article";
    const NAME = "article";

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
        ))->setElementDecorators(WednesdayForm::getElementDecorators());

        return $this;
    }

    /**
     *
     */
    public function init() {
        $this->setLegend('Main Article');
        $this->setName(self::NAME);
        
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $this->log = $bootstrap->getResource('Log');
        
        if($this->getAttrib('hasUID')==true) {
            $this->addElement(
                'text', 'uid', array(
                    'label' => 'Page UID',
                    'class' => 'span8 disabled',
                    'disabled' => 'disabled'
            ));
        }
        $this->addElement('text', 'title', array(
            'label' => 'Title',
            'class' => 'span8',
            'required' => true,
            'filters' => array('StringTrim'),
        ));
        $popOver = WednesdayForm::getPopOverText('slug');
        $this->addElement('text', 'slug', array(
            'label' => 'Slug'.$popOver,
            'class' => 'span8 small disabled',
            'required' => false,
        ));
        $slug = $this->getElement('slug');
        $slug->getDecorator('Label')->setOption('escape',false);
        $this->addElement('textarea', 'summary', array(
            'label' => WednesdayForm::getPopOverLabel('Summary',"If pasting content from Word, please use the 5th icon along - indicated by the W icon."),
            'class' => 'wysiwyg custom-headers span8',
            'description'=> 'Please enter a short summary of around 20-30 words (or up to 200 characters) for the page content. This summary will be displayed in various locations on the site including listings pages, search results, RSS feeds and in the page metadata for SEO purposes, but depending on the template assigned to the page, the summary may not necessarily appear on every location on the site.<br /><br />Please also be sure to check the other template-specific fields in the "Content" tab if you are having difficulties updating content.',
            'rows' => '5',
            'required' => true,
            'filters' => array('StringTrim'),
        ));
        $summary = $this->getElement('summary');
        $summary->getDecorator('Label')->setOption('escape',false);
        $this->addElement('textarea', 'description', array(
            'label' => WednesdayForm::getPopOverLabel('Article Copy',"If pasting content from Word, please use the 5th icon along - indicated by the W icon."),
            'class' => 'wysiwyg custom-headers span8',
            'description'=> "Please enter a description of the page content - there is no particular word or character limit. For a news or blog article, this will be the main body of text that appears in the article's detail page. On other content types, the description may not be required, but may still be of use for providing an RSS-specific version of the content and for improving search results.<br /><br />Please also be sure to check the other template-specific fields in the \"Content\" tab if you are having difficulties updating content.",
            'rows' => '5',
            'required' => false,
            'filters' => array('StringTrim'),
        ));
        $description = $this->getElement('description');
        $description->getDecorator('Label')->setOption('escape',false);
    }

    public function getEntityMap() {
        $values = parent::getEntityMap();
//        $this->log->info(self::NAME.' '.self::CSSCLASS);
//        $this->log->info($values);
        return $values;
    }

}
