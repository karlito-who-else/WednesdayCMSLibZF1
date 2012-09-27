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

/**
 * Description of StandardFooter
 * GalleriesFieldset
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
class GalleriesFieldset extends SubForm {

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
        #Pick button
        $this->addElement('button', 'pick', array(
            'label' 	=> 'Pick Element',
            'type' 		=> 'backbone modal',
            'class'     => 'btn btn-warning btn-backbone ',
            'ignore' 	=> true
        ));
        #Reset button
        $this->addElement('button', 'reset', array(
            'label' 	=> 'Exit Without Saving',
            'type' 		=> 'reset',
            'class'     => 'btn btn-warning',
            'ignore' 	=> true
        ));
//        #Delete button
//        $this->addElement('button', 'confirm-deletion-btn', array(
//            'label' => 'Delete',
//            'type' => 'submit',
//            'class' => 'btn btn-danger delete pull-right',
//            'ignore' => true,
//        ));

        //$options['options'][0] = 'Select parent';
        $stateopts['options']['saved'] = 'Saved';
        $stateopts['options']['active'] = 'Active';
        $stateopts['options']['xmlrpc'] = 'Feed';
        $stateopts['options']['cached'] = 'Cached';
        $stateopts['options']['published'] = 'Published';
        $stateopts['options']['unpublished'] = 'Unpublished';
        $stateopts['options']['deleted'] = 'Deleted';
        $this->addElement('select', 'state', array(
                'label' => 'State',
                'description'=> 'Select published to make your post live. Leave as Draft to continue editing later or select un-published to remove the post from the site.',
                'multiOptions' => $stateopts['options']
            ));
        $status = $this->getElement('state');
//        $status->addDecorator('HelpBlock', array('text' => 'Select published to make your post live. Leave as Draft to continue editing later or select un-published to remove the post from the site.'));

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
