<?php
namespace Wednesday\Form\Element\Groups;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    \Application\Entities\Resources,
    \Wednesday\Renderers\ResourceHtml,
    \Wednesday\Mapping\Form\EntityFormRenderer,
    \Wednesday\Form\Element\Groups\StandardFooter as ActionsGroup,
    \Wednesday\Form\Element\Groups\WizardFooter as WizardGroup,
    \Wednesday\Form\Element\Groups\EntityCoreFieldset as EntityCoreGroup,
    \Wednesday\Form\Element\Groups\EntityRichFieldset as EntityRichGroup,
    \Wednesday\Form\Element\Groups\EntityBasicFieldset as EntityBasicGroup,

    \Wednesday\Form\Element\Groups\BlogitemCoreFieldset as BlogitemGroup,
    \Wednesday\Form\Element\Groups\BlogitemPressFieldset as PressGroup,
    \Wednesday\Form\Element\Groups\BlogitemNewsFieldset as NewsGroup,
    \Wednesday\Form\Element\Groups\BlogitemArticleFieldset as ArticleGroup,
    \Wednesday\Form\Element\Groups\BlogitemEntityFieldset as BlogitemEntityGroup,

    \Wednesday\Form\Element\Groups\GalleriesFieldset as GalleriesGroup,
    \Wednesday\Form\Element\Groups\WidgetsFieldset as WidgetsGroup,
    \Wednesday\Form\Element\Groups\AttachmentsFieldset as AttachmentsGroup,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Form as Form,
    \Zend_Form_SubForm as SubForm,
    \Zend_Controller_Front as Front;
//    \Wednesday\Form\Element\Groups\EntityCoreFieldset as EntityCoreGroup,
//    \Wednesday\Form\Element\Groups\EntityRichFieldset as EntityRichGroup,
//    \Wednesday\Form\Element\Groups\EntityBasicFieldset as EntityBasicGroup,
//
//    \Wednesday\Form\Element\Groups\BlogitemCoreFieldset as BlogitemGroup,
//    \Wednesday\Form\Element\Groups\BlogitemPressFieldset as PressGroup,
//    \Wednesday\Form\Element\Groups\BlogitemNewsFieldset as NewsGroup,
//    \Wednesday\Form\Element\Groups\BlogitemArticleFieldset as ArticleGroup,
//    \Wednesday\Form\Element\Groups\BlogitemEntityFieldset as BlogitemEntityGroup,
//
//    \Wednesday\Form\Element\Groups\Press ItemFieldset as Press ItemGroup,
//    \Wednesday\Form\Element\Groups\WidgetsFieldset as WidgetsGroup,
//    \Wednesday\Form\Element\Groups\AttachmentsFieldset as AttachmentsGroup,

/**
 * Description of StandardFooter
 * Press ItemFieldset
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
class BlogitemPressFieldset extends SubForm {

    const CSSCLASS = "form-pressitem";
    const NAME = "pressitem";

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
        $this->setLegend('Press Item');//pressitem Footer Controls');
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
////        #Delete button
//        $this->addElement('button', 'confirm-deletion-btn', array(
//            'label' => 'Delete',
//            'type' => 'submit',
//            'class' => 'btn btn-danger delete pull-right',
//            'ignore' => true,
//        ));

        #gallery
//        $this->addElement('text', 'title', array(
//            'label' => 'Title',
//            'class' => 'span8',
//            'required' => true,
//            'filters' => array('StringTrim'),
//        ));
        #enclosure
//        $this->addElement('textarea', 'description', array(
//            'label' => 'Description',
//            'class' => 'wysiwyg custom-headers span8',
//            'description'=> 'The description will appear on the article detail page. If pasting content from Word, please use the 5th icon along - indicated by the W icon.',
//            'rows' => '5',
//            'required' => false,
//            'filters' => array('StringTrim'),
//        ));

        $description = $this->getElement('description');
//        $description->addDecorator('HelpBlock', array('text' => 'The description will appear on the article detail page. If pasting content from Word, please use the 5th icon along - indicated by the W icon.'));
        $description->setDescription('The description will appear on the article detail page. If pasting content from Word, please use the 5th icon along - indicated by the W icon.');
        #End Core Fieldset

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