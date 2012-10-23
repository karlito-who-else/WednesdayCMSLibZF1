<?php
namespace Wednesday\Mapping\Form;

use ReflectionException,
    Doctrine\Common\Annotations\AnnotationReader,
    \Zend_Form_Element,
    \Zend_Controller_Front as Front,
    \Wednesday\Controller\AdminAction as AdminControllerAbstract,
    \Zend_Controller_Action_Exception as ActionControllerException,
    \Doctrine\Common\Collections\ArrayCollection,
    \Doctrine\Common\Util\Debug as DoctrineDebug,
    \Zend_Paginator as Paginator,
    \Zend_Form_Element_Text,
    \Zend_Form_Element_Textarea,
    \Zend_Form_Element_Select,
    \Zend_Form_Element_Hidden,
    \Zend_Form_Decorator_HtmlTag,
    \Wednesday\Mapping\Form\Element,
    \Wednesday_Form_Element_PagePicker,
    \Wednesday_Form_Element_ResourcePicker,
    \Wednesday_Form_Element_GalleryPicker,
    \Wednesday_Form_Element_TabPicker,
    \PageRenderer_Form_EditPage as EditPageForm,
    \Application\Entities\TemplateVariables as TmplVars,
    \EasyBib_Form,
    \EasyBib_Form_Decorator as EasyBibFormDecorator;

/**
 * Description of Element
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
class Element extends EntityMapperAbstract {
    const USERS = 'Application\Entities\Users';
    const PAGES = "Application\Entities\Pages";
    const TVARS = "Application\Entities\TemplateVariables";
    const TVARCONTS = "Application\Entities\VariablesContent";
    const RESOURCE = "Application\Entities\MediaResources";
    const GALLERY = "Application\Entities\MediaGalleries";
    const GALLERYRESOURCES = "Application\Entities\GalleryMediaOrdered";
    const TAGS = "Wednesday\Models\Tags";
    const WIDGETS = "Application\Entities\Widgets";

    public function getTvarElement($tvar, $tvdata) {
        $element = false;
//        $decorators = array(
//            array('ViewHelper'),
//            array('BootstrapErrors'),
//            array('Description', array(
//                    'tag'   => 'p',
//                    'class' => 'help-block span8',
//                    'style' => 'color: #999;'
//                )
//            ),
//            array('BootstrapTag', array(
//                    'class' => 'controls'
//                )
//            ),
//            array('Label', array(
//                    'class' => 'control-label'
//                )
//            ),
//            array('DivNestWrapper', array('class' => 'control-group'))
//        );
        switch($tvar->type) {
            case TmplVars::TYPE_AGGREGATE:
            case TmplVars::TYPE_ENTITY:
                switch($tvar->options) {
                    case self::PAGES:
                        #TODO Insert Gallery Element into Gallery subform.
                        $element = new Wednesday_Form_Element_PagePicker(array(
                            'name' => ''.$tvdata->id.'',
                            'value' => $tvdata->value,
                            'label' => $tvar->label,
                            'class' => 'span8',
                            'required' => false
                        ));
                        break;
                    case self::RESOURCE:
                        #TODO Insert Gallery Element into Gallery subform.
                        $element = new Wednesday_Form_Element_ResourcePicker(array(
                            'name' => ''.$tvdata->id.'',
                            'value' => $tvdata->value,
                            'label' => $tvar->label,
                            'class' => 'span8',
                            'required' => false
                        ));
                        break;
                    case self::WIDGETS:
                        $element =  new Wednesday_Form_Element_TabPicker(array(
                            'name' => ''.$tvdata->id.'',
                            'value' => $tvdata->value,
                            'label' => $tvar->label,
                            'class' => 'span8',
                            'required' => false
                        ));
                        break;
                    case self::GALLERY:
                        #TODO Insert Gallery Element into Gallery subform.
                         $element = new Wednesday_Form_Element_GalleryPicker(array(
                            'name' => ''.$tvdata->id.'',
                            'value' => $tvdata->value,
                            'label' => $tvar->label,
                            'class' => 'span8',
                            'required' => false
                        ));
                        break;
                    default:
                        $element = new Zend_Form_Element_Text(''.$tvdata->id.'');
                        $element->setLabel($tvar->label)
                                ->setRequired(false)
                                ->setValue($tvdata->value)
                                ->setAttrib('class', 'span8');
                        break;
                }
                break;
            case TmplVars::TYPE_RICHTEXT:
                $element = new Zend_Form_Element_Textarea(''.$tvdata->id.'');
                $element->setLabel($tvar->label)
                        ->setRequired(false)
                        ->setValue($tvdata->value)
                        ->setAttrib('class', 'span8 wysiwyg custom-headers');
                break;
            case TmplVars::TYPE_STATIC:
            case TmplVars::TYPE_TEXT:
                $element = new Zend_Form_Element_Text(''.$tvdata->id.'');
                $element->setLabel($tvar->label)
                        ->setRequired(false)
                        ->setValue($tvdata->value)
                        ->setAttrib('class', 'span8');
                break;
            case TmplVars::TYPE_CUSTOM:
                $this->log->debug($tvdata->id." - ".$tvdata->value);
                if(is_string($tvar->options)) {
                    $element = new $tvar->options(''.$tvdata->id.'');
                    if($tvdata->value=="") {
                        $tvdata->value = " ";
                    }
                } else {
                    $element = new Zend_Form_Element_Text(''.$tvdata->id.'');
                }
                $element->setLabel($tvar->label)
                        ->setRequired(false)
                        ->setValue($tvdata->value);
                break;
            case TmplVars::TYPE_LIST:
//                $this->log->debug($tvar->options);
                $element = new Zend_Form_Element_Select(''.$tvdata->id.'');
                $element->setLabel($tvar->label)
                        ->setRequired(false)
                        ->setValue($tvdata->value)
                        ->setMultiOptions($tvar->options);
                break;
            case TmplVars::TYPE_FORM:
            default:
                $element = new Zend_Form_Element_Text(''.$tvdata->id.'');
                $element->setLabel($tvar->label)
                        ->setRequired(false)
                        ->setValue($tvdata->value);
                break;
        }
        return $element;
    }

    private function createResourceElement($nameId,$value,$labelName)
    {
        #TODO Insert Gallery Element into Gallery subform.
        $element = new Wednesday_Form_Element_ResourcePicker(array(
            'name' => ''.$nameId.'',
            'value' => $value,
            'label' => $labelName,
            'class' => 'span8',
            'required' => false
        ));
    }

    private function createGalleryElement($nameId,$value,$labelName)
    {
        #TODO Insert Gallery Element into Gallery subform.
        $element = new Wednesday_Form_Element_GalleryPicker(array(
            'name' => ''.$nameId.'',
            'value' => $value,
            'label' => $labelName,
            'class' => 'span8',
            'required' => false
        ));
        return $element;
    }
}
