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
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author mrhelly
 */
class TaxonomyFieldset extends FormGroupAbstract {

    const CSSCLASS = "form-taxonomy";
    const NAME = "taxonomy";
//    const ENTITY = "

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
        $this->setLegend('Taxonomy');
        $this->setName(self::NAME);

        //Add dates.
        $this->addElement('text', 'tags', array(
            'label' => 'Tags',
            'class' => 'span8 tags',
            'required' => false,
            'description'=> 'Specify tags for this item',
            'filters' => array('StringTrim'),
        ));

        $catElem = new Wednesday_Form_Element_CategoryPicker('categories');
        $catElem->setLabel('Choose Category')
                    ->setRequired(false)
                    ->setAttrib('class', 'category')
                    ->clearDecorators()
                    ->addPrefixPath('Wednesday_Form_Decorator', 'Wednesday/Form/Decorator/', 'decorator')
                    ->addPrefixPath('EasyBib_Form_Decorator', 'EasyBib/Form/Decorator', 'decorator')
                    ->addDecorators(WednesdayForm::getElementDecorators());
        $this->addElement($catElem);
    }

    public function getEntityMap() {
        $static = (object) array(
            'tags' => 'tagid',
            'category' => 'catid',
            '' => ''
        );
        $values = parent::getEntityMap();
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $this->log = $bootstrap->getResource('Log');
        $this->log->info($static);
//        $this->log->info($this->getValidValues());
//        $this->log->info($this->getValues());
        $this->valid();
        $this->log->err($this->getErrorMessages());
        $this->log->info(self::NAME.' '.self::CSSCLASS);
//        $this->log->info($this->getErrorMessages());
        $raw = $this->getValues();
        $this->log->warn($raw);
        $this->log->info(self::NAME.' '.self::CSSCLASS);
        return $values;
    }

    //Build Default Elements from Entity Definition
    protected function buildForm($entity,$context) {

    }

    //Parse Default ZendForm Return Structure
    protected function parseForm($entity,$context) {

    }
}
