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
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author mrhelly
 */
class FormGroupAbstract extends SubForm {

    const CSSCLASS = "fieldset-formgroup";
    const NAME = "formgroup";
    
    public $log;
    
    public function getEntityMap() {
//        $bootstrap = Front::getInstance()->getParam("bootstrap");
//        $this->log = $bootstrap->getResource('Log');
//        $this->log->info($this->getUnfilteredValues());
//        $this->log->info($this->getValidValues());
//        $this->log->info($this->getValues());
//        $this->log->err($this->getErrorMessages());
//        $this->log->info(self::NAME.' '.self::CSSCLASS);
        $this->valid();
//        $this->log->info($this->getErrorMessages());
        $raw = $this->getValues();
//        $this->log->info($raw);
        $filtered = $this->getValidValues($raw);
        return $filtered;
    }

}
