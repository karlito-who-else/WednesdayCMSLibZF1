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
 * Description of PublishFieldset
 * TaxonomyFieldset
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
class PublishFieldset extends FormGroupAbstract {

    const CSSCLASS = "fieldset-publish";
    const NAME = "publish";

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
        $this->setLegend('Publication Settings');
        $this->setName(self::NAME);

        //Add dates.
        $statopts = array();
        $statopts['draft'] = 'Draft';
        $statopts['published'] = 'Published';
        $statopts['unpublished'] = 'Unpublished';
        $statopts['deleted'] = 'Deleted';
        $this->addElement('select', 'status', array(
            'label' => 'Status',
            'description'=> 'Select published to make your post live. Leave as Draft to continue editing later or select un-published to remove the post from the site.',
            'multiOptions' => $statopts
        ));
//        $status = $this->getElement('status');
//        $status->addDecorator('HelpBlock', array('text' => 'Select published to make your post live. Leave as Draft to continue editing later or select un-published to remove the post from the site.'));

        //Add dates.
        $this->addElement('text', 'publishstart', array(
            'label' => 'Publish Start',
            'class' => 'small timepicker',
            'required' => true,
            'description'=> 'Select today\'s date to put live now. Select a future date to publish later, or choose a past date to back date a post.',
            'filters' => array('StringTrim'),
        ));
//        $publishstart = $this->getElement('publishstart');
//        $publishstart->addDecorator('HelpBlock', array('text' => 'Select today\'s date to put live now. Select a future date to publish later, or choose a past date to back date a post.'));

        $this->addElement('text', 'publishend', array(
            'label' => 'Publish End',
            'class' => 'small timepicker',
            'description'=> 'Leave this blank to publish indefinitely or select the date you wish this to be published until.',
            'required' => false,
            'filters' => array('StringTrim'),
        ));
//        $publishend = $this->getElement('publishend');
//        $publishend->addDecorator('HelpBlock', array('text' => 'Leave this blank to publish indefinitely or select the date you wish this to be published until.'));

        //Add dates.
        $this->addElement('text', 'created', array(
            'label' => 'Created',
            'disabled' => 'disabled',
            'class' => 'small disabled',
            'description'=> 'The date this item was created.',
            'required' => false,
        ));

//        $created = $this->getElement('created');
//        $created->addDecorator('HelpBlock', array('text' => 'The date this collection was created.'));

        //Add dates.
        $this->addElement('text', 'updated', array(
            'label' => 'Last updated',
            'disabled' => 'disabled',
            'class' => 'small disabled',
            'description'=> 'The date this item was last updated.',
            'required' => false,
        ));
//        $updated = $this->getElement('updated');
//        $updated->addDecorator('HelpBlock', array('text' => 'The date this collection was last updated.'));

    }

    public function getEntityMap() {
        $values = parent::getEntityMap();
//        $this->log->info(self::NAME.' '.self::CSSCLASS);
//        $this->log->info($values);
        return $values;
    }
}
