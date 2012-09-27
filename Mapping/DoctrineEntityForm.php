<?php

namespace Wednesday\Mapping\Form;

//use ReflectionException,
//    Doctrine\Common\Annotations\AnnotationReader,
//    \Zend_Form_Element,
//    \Zend_Controller_Front as Front,
//    \Wednesday\Controller\AdminAction as AdminControllerAbstract,
//    \Zend_Controller_Action_Exception as ActionControllerException,
//    \Doctrine\Common\Collections\ArrayCollection,
//    \Zend_Paginator as Paginator,
//    \Wednesday\Mapping\Form\Element,
//    \Wednesday_Form_Element_PagePicker,
//    \Wednesday_Form_Element_ResourcePicker,
//    \Wednesday_Form_Element_GalleryPicker,
//    \Wednesday_Form_Element_TabPicker,
//    \PageRenderer_Form_EditPage as EditPageForm,
//    \Application\Entities\TemplateVariables as TmplVars;

use \ReflectionException,
    \Doctrine\ORM\Mapping\Column,
    \Doctrine\Common\Collections\ArrayCollection,
    \Doctrine\Common\Annotations\AnnotationReader,
    \Doctrine\Common\Util\Debug as DoctrineDebug,
    \Zend_Form,
    \EasyBib_Form,
    \Zend_Form_SubForm,
    \Zend_Form_Element,
    \Zend_Form_Element_Text,
    \Zend_Form_Element_Textarea,
    \Zend_Form_Element_Select,
    \Zend_Form_Element_Hidden,
    \Zend_Form_Decorator_HtmlTag,
    \Wednesday_Form_Form as WednesdayForm,
    \EasyBib_Form_Decorator as EasyBibFormDecorator,
    \Wednesday\Mapping\Annotation\Form,
    \Wednesday\Form\Element\Groups as FormGroups,
//    \Wednesday\Form\Element\Groups\StandardFooter as ActionsGroup,
    \Zend_Controller_Front as Front;

/**
 * Description of DoctrineEntityForm
 *
 * @author jamesh
 */
class DoctrineEntityForm extends WednesdayForm {

    const SYSTEM_NAMESPACE = "Wednesday\Models\\";
    const ENTITY_NAMESPACE = "Application\Entities\\";

    protected $pickers = array(
        '\Wednesday\Models\Categories' => 'Wednesday_Form_Element_CategoryPicker',
        '\Application\Entities\Resources' => 'Wednesday_Form_Element_ResourcePicker',
        '\Application\Entities\Templates' => 'Wednesday_Form_Element_TemplatePicker',
    );

    protected $cmf;
    protected $cmd;
    protected $em;
    protected $log;
    protected $annotations;

    public function __construct($entityName) {

        $this->model = $entityName;

        #Get Logger
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $this->log = $bootstrap->getResource('Log');
        $this->em = $bootstrap->getContainer()->get('entity.manager');
        $this->cmf = $this->em->getMetadataFactory();
        $this->cmd = $this->cmf->getMetadataFor($this->model);

        $reader = new AnnotationReader();
        $this->annotations = (object) array();
        $this->annotations->classAnnotations = $reader->getClassAnnotations($this->cmd->reflClass);
        $this->annotations->reflProperties = $this->cmd->reflClass->getProperties();
        $this->annotations->mappedConstants = $this->cmd->reflClass->getConstants();
        $this->annotations->mappedProperties = array();

        foreach ($this->annotations->reflProperties as $property) {
            $reflProperties = new \ReflectionProperty($property->class, $property->name);
            $annotations = $reader->getPropertyAnnotations($reflProperties);
            $this->annotations->mappedProperties[$property->name] = $annotations;
        }
//        $this->log->info($this->cmd);
//        $this->log->info($this->annotations);
        $this->log->info(get_class($this) . '::construct(' . $this->model . ')');

        parent::__construct();
    }

    public function init() {
        $ent = str_replace(array(self::ENTITY_NAMESPACE, self::SYSTEM_NAMESPACE), '', $this->model);
        $this->setMethod('post');
        $this->addAttribs(array(
            'id' => $ent . '-edit',
            'class' => 'tabs-container form-horizontal'// form-stacked
        ))->setAttrib('accept-charset', 'utf-8');
        $this->setElementDecorators($this->getElementDecorators())
                ->setLegend('Entity');
        $this->buildFormElements();
        
        $actions = new FormGroups\StandardFooter();
        $this->addSubForm($actions, FormGroups\StandardFooter::NAME);
        
    }

    public function populate($EntityItem) {
        if (is_object($EntityItem)) {
            $filteredvalues = $EntityItem->toArray(true, true);
            foreach ($this->annotations->mappedProperties as $propname => $property) {
//            $this->log->info($propname);
//            $this->log->err($property);
                foreach ($property as $prop) {
                    switch (get_class($prop)) {
                        case "Doctrine\ORM\Mapping\OneToOne":
                        case "Doctrine\ORM\Mapping\ManyToOne":
                            $filteredvalues[$propname] = $EntityItem->$propname->id;
                            break;
                        case "Doctrine\ORM\Mapping\ManyToMany":
                            $filteredvalues[$propname] = $EntityItem->$propname->getKeys();
                            break;
                    }
                }
            }
        } else {
            //Assume its already an array.
            $filteredvalues = $EntityItem;
        }
        return $this->setDefaults($filteredvalues);
    }

    protected function buildFormElements() {
        $subform = $this->newSubForm();
        $subform->setDecorators($this->getSubFormDecorators('basic'))
                ->setElementDecorators($this->getElementDecorators())
                ->setLegend('Information');
        foreach ($this->annotations->mappedProperties as $name => $property) {
            $this->log->info($name);
            $element = $this->getElementForProperty($name, $property);
            if ($element != false) {
                $subform->addElement($element);
            }
        }
        $this->addSubForm($subform, 'basic');
        $this->log->info(get_class($this) . '::buildFormElements(' . $this->model . ')');
    }

    private function getElementForProperty($name, $property) {
        $elementOptions = false;
        $elementSettings = (object) array(
                    'options' => array(
                        'required' => false,
                        'label' => ucfirst($name),
                        'class' => 'span8'
                    ),
                    'element' => "Zend_Form_Element_Text"
        );
        foreach ($property as $prop) {
            switch (get_class($prop)) {
                case "Doctrine\ORM\Mapping\Column":
                    $elementSettings->options['required'] = !$prop->nullable;
                    break;
                case "Wednesday\Mapping\Annotation\Form":
                    switch ($prop->renderer) {
                        case "none":
                            return false;
                            break;
                        case "disabled":
                            $elementSettings->options['disabled'] = 'disabled';
                            $elementSettings->options['class'] .= ' disabled';
                            break;
                        case "text":
                            break;
                        case "textarea":
                            $elementSettings->element = "Zend_Form_Element_Textarea";
                            break;
                        case "rich":
                            $elementSettings->element = 'Zend_Form_Element_Textarea';
                            $elementSettings->options['class'] .= ' wysiwyg custom-headers';
                            break;
                        case 'radioyesno':
                            $elementSettings->element = 'Zend_Form_Element_Radio';
                            break;
                        case 'eachsubform':
                            if (in_array($prop->options, array_keys($this->pickers))) {
                                $elementSettings->element = $this->pickers[$prop->options];
                            } else {
                                $elementSettings->element = 'Zend_Form_Element_MultiSelect';
                                $elementOptions = array(0 => "None");
                                $entities = $this->em->getRepository($prop->options)->findAll();
                                foreach ($entities as $entity) {
                                    $title = ($entity->title != '') ? $entity->title : "Entity: " . $entity->id;
                                    $elementOptions[$entity->id] = $title;
                                }
                            }
                            break;
                        case 'entitypicker':
                            if (in_array($prop->options, array_keys($this->pickers))) {
                                $elementSettings->element = $this->pickers[$prop->options];
                            } else {
                                $elementSettings->element = 'Zend_Form_Element_Select';
                                $elementOptions = array(0 => "None");
                                $entities = $this->em->getRepository($prop->options)->findAll();
                                foreach ($entities as $entity) {
                                    $title = ($entity->title != '') ? $entity->title : "Entity: " . $entity->id;
                                    $elementOptions[$entity->id] = $title;
                                }
                            }
                            break;
                        case 'categorypicker':
                            $elementSettings->element = 'Wednesday_Form_Element_CategoryPicker';
                            break;
                        case "selectconst":
                            $elementSettings->element = 'Zend_Form_Element_Select';
                            $elementOptions = array();
                            foreach ($this->annotations->mappedConstants as $const) {
                                $elementOptions[$const] = ucfirst($const);
                            }
                            break;
                        case "selectoptions":
                            $elementSettings->element = 'Zend_Form_Element_Select';
                            $opts = explode(",", $prop->options);
                            $elementOptions = array();
                            foreach ($opts as $option) {
                                $elementOptions[$option] = $option;
                            }
                            break;
                    }
                    break;
                case "Gedmo\Mapping\Annotation\Slug":
                case "Gedmo\Mapping\Annotation\TreeLeft":
                case "Gedmo\Mapping\Annotation\TreeRight":
                case "Gedmo\Mapping\Annotation\TreeLevel":
                case "Gedmo\Mapping\Annotation\TreeRoot":
                    //Don't  show these fields.
                    return false;
                    break;
                case "Doctrine\ORM\Mapping\Id":
                case "Doctrine\ORM\Mapping\GeneratedValue":
                    $elementSettings->element = "Zend_Form_Element_Hidden";
                    $elementSettings->options['label'] = "";
                    return false;
                    break;
//                case "Doctrine\ORM\Mapping\OneToOne":
//                case "Doctrine\ORM\Mapping\ManyToOne":
//                case "Doctrine\ORM\Mapping\ManyToMany":
//                case "Doctrine\ORM\Mapping\OrderBy":
//                case "Gedmo\Mapping\Annotation\Translatable":
//                case "Gedmo\Mapping\Annotation\Timestampable":
//                case "Gedmo\Mapping\Annotation\Locale":
//                case "Gedmo\Mapping\Annotation\TreeParent":
                default:
                    break;
            }
//            $this->log->info(get_class($prop));
        }
        $elementOptions = (!is_array($elementOptions)) ? false : $elementOptions;
        $element = new $elementSettings->element($name);
        $element->setOptions($elementSettings->options);
        if ($elementOptions != false) {
            $element->addMultiOptions($elementOptions);
        }
        $element->setDecorators($this->getElementDecorators());
        return $element;
    }
    
    public function entityDataMap($postdata) {
//        $bootstrap = Front::getInstance()->getParam("bootstrap");
//        $this->log = $bootstrap->getResource('Log');
//        $this->log->info($this->getUnfilteredValues());
//        $this->log->info($this->getValidValues());
//        $this->log->info($this->getValues());
//        $this->log->err($this->getErrorMessages());
//        $this->log->info(self::NAME.' '.self::CSSCLASS);
        $this->setDefaults($postdata);
        if($this->valid()) {
            //Fine!
        }
        $this->log->info($this->getErrorMessages());
        $raw = $this->getValues();
        $this->log->info($raw);
        $filtered = $this->getValidValues($raw);
        $filtered = $filtered['basic'];
        $this->log->info($filtered);
        //Map Associations
        foreach ($this->annotations->mappedProperties as $propname => $property) {
//            $this->log->info($propname);
//            $this->log->err($property);
            foreach ($property as $prop) {
                switch (get_class($prop)) {
                    case "Doctrine\ORM\Mapping\OneToOne":
                    case "Doctrine\ORM\Mapping\ManyToOne":
                        $this->log->info($propname);
                        $this->log->err($filtered[$propname]);
                        $this->log->info($prop->targetEntity);
                        $entref = $this->em->getRepository($prop->targetEntity)->find($filtered[$propname]);
                        $filtered[$propname] = $entref;
                        break;
                    case "Doctrine\ORM\Mapping\ManyToMany":
                        $this->log->info($propname);
                        $this->log->info($prop->targetEntity);
                        $this->log->err($filtered[$propname]);
                        $refClass = ((substr_compare($prop->targetEntity, self::ENTITY_NAMESPACE, 0, strlen(self::ENTITY_NAMESPACE))==0)||(substr_compare($prop->targetEntity, self::SYSTEM_NAMESPACE, 0, strlen(self::SYSTEM_NAMESPACE))==0))?$prop->targetEntity:self::ENTITY_NAMESPACE.$prop->targetEntity;
                        foreach($filtered[$propname] as $pos => $entid) {
                            $entref = $this->em->getRepository($refClass)->find($entid);
                            $filtered[$propname][$pos] = $entref;
                        }
                        //$filtered[$propname] = $EntityItem->$propname->getKeys();
                        break;
                }
            }
        }        
        return $filtered;
    }
    
}
