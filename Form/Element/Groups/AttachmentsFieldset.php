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
 * AttachmentsFieldset
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
class AttachmentsFieldset extends SubForm {

    const CSSCLASS = "form-attachments";
    const NAME = "attachments";

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
        $this->setLegend('Attachments');//attachments Footer Controls');
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
        #Reset button
        $this->addElement('button', 'reset', array(
            'label' 	=> 'Exit Without Saving',
            'type' 		=> 'reset',
            'class'     => 'btn btn-warning',
            'ignore' 	=> true
        ));

        //Add dates.
        $this->addElement('text', 'publishstart', array(
            'label' => 'Publish Start',
            'class' => 'small timepicker',
            'required' => true,
            'description'=> 'Select today\'s date to put live now. Select a future date to publish later, or choose a past date to back date a post.',
            'filters' => array('StringTrim'),
        ));
        $publishstart = $this->getElement('publishstart');
//        $publishstart->addDecorator('HelpBlock', array('text' => 'Select today\'s date to put live now. Select a future date to publish later, or choose a past date to back date a post.'));
        $publishstart->setDescription('Select today\'s date to put live now. Select a future date to publish later, or choose a past date to back date a post.');
        $this->addElement('text', 'publishend', array(
            'label' => 'Publish End',
            'class' => 'small timepicker',
            'description'=> 'Leave this blank to publish indefinitely or select the date you wish this to be published until.',
            'required' => false,
            'filters' => array('StringTrim'),
        ));
        $publishend = $this->getElement('publishend');
//        $publishend->addDecorator('HelpBlock', array('text' => 'Leave this blank to publish indefinitely or select the date you wish this to be published until.'));
        $publishend->setDescription('Leave this blank to publish indefinitely or select the date you wish this to be published until.');
        //$this->addSubForm($subpub, 'publish');

        //$options['options'][0] = 'Select parent';
        $statopts['options']['draft'] = 'Draft';
        $statopts['options']['published'] = 'Published';
        $statopts['options']['unpublished'] = 'Unpublished';
        $statopts['options']['deleted'] = 'Deleted';
        $this->addElement('select', 'status', array(
                'label' => 'Status',
                'description'=> 'Select published to make your post live. Leave as Draft to continue editing later or select un-published to remove the post from the site.',
                'multiOptions' => $statopts['options']
            ));
        $status = $this->getElement('status');
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
