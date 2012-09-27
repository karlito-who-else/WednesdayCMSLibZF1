<?php

/*
 * Wednesday London
 * Copyright 2011
 */

namespace Wednesday\Mapping\Form;

use ReflectionException,
    Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\ORM\Mapping\Column,
    Wednesday\Mapping\Annotation\Form,
    \Zend_Form_SubForm,
    \Zend_Form,
    \Zend_Form_Element,
    \Zend_Controller_Front as Front;

/**
 * Description of FormAbstract
 *  This class takes an entity and maps the properties to form elements and then returns a \Zend_Form_SubForm
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author james a helly <james@wednesday-london.com>
 *
 * TODO: Gedmo hide lvl, root etc ...
 * TODO: Custom form helpers include
 */
class EntityMapperAbstract {

    const _SKIP_PROPERTIES = 'created,updated,lft,rgt,lvl,root,locale,slug';
    const _RESTRICTED_PROPERTIES = 'id';

    protected $entity;
    protected $entityName;
    protected $classAnnotations;
    protected $reflProperties;
    protected $propertiesMapping;
    protected $entityMapping;
    protected $mappedProperties;
    protected $mappedConstants;
    protected $log;
    public $formMapping;

    /**
     *
     * @param string $name
     * @param array $skip
     * @param array $restrict
     */
    public function __construct($Entity, $skip = false, $restrict = false) {
        $this->entity = $Entity;
        $this->entityName = get_class($Entity);

        #Get Logger
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $this->log = $bootstrap->getResource('Log');
        $this->init($Entity, $skip, $restrict);
    }

    /**
     *
     * @param string $Entity
     * @param array $skip
     * @param array $restrict
     */
    public function init($Entity, $skip, $restrict) {
        #Grab Annotations
        $reflProperties = $this->checkEntityAnnotations($Entity);

        #Build Annotation Map foreach property of the Entity
        $this->entityMapping = $this->_checkClassAnnotation($this->classAnnotations);
        $this->propertiesMapping = $this->getPropertyAnnotations($this->reflProperties);
        $this->log->debug(get_class($this).'::init( )');
    }

    /**
     *
     * @param type $entityNamespace
     * @return type
     */
    protected function checkEntityAnnotations($entityNamespace = false) {
        if($entityNamespace == false){
            return false;
        }
        #TODO Find a way to allow entity to decide how it should interact with REST?
        $reader = new AnnotationReader();
        $reflClass = new \ReflectionClass($entityNamespace);
        $this->classAnnotations = $reader->getClassAnnotations($reflClass);
        $this->reflProperties = $reflClass->getProperties();
        $this->mappedConstants = $reflClass->getConstants();
//        return $this->mappedProperties;
        return $this->reflProperties;
    }

    private function _checkClassAnnotation($annotations) {
        #Class Annotations
//        $this->log->debug($annotations);
        foreach($annotations as $annotationNamespace) {
            switch(get_class($annotationNamespace)) {
                case 'Doctrine\ORM\Mapping\Entity':
//                    $element = $this->doctrineFormElement($annotationNamespace->repositoryClass);
//                    (repositoryClass="Application\Entities\PagesRepository")
                    break;
                case 'Gedmo\Mapping\Annotation\TranslationEntity':
//                    $element = $this->doctrineFormElement($annotationNamespace->class);
//                    (class="Application\Entities\PageTranslations")
                    break;
                case 'Gedmo\Mapping\Annotation\Tree':
//                    $element = $this->doctrineFormElement($annotationNamespace->type);
//                    (type="strategy")
                    break;
                case 'Wednesday\Mapping\Annotation\RestableActions':
//                    $element = $this->doctrineFormElement($annotationNamespace->type);
//                    (repositoryClass="Application\Entities\PagesRepository")
                    break;
                default:
                    break;
            }
        }
    }

    public function getPropertyAnnotations($reflProperties) {
        $this->mappedProperties = array();
        $reader = new AnnotationReader();
        foreach($reflProperties as $property) {
            $reflProperties = new \ReflectionProperty($property->class, $property->name);
            $annotations = $reader->getPropertyAnnotations($reflProperties);
            $this->mappedProperties[] = $this->_checkPropertyAnnotation($annotations);
        }
        return $this->mappedProperties;
    }

    private function _checkPropertyAnnotation($annotations) {
        #Property Annotations
//        $this->log->debug($annotations);
        foreach($annotations as $annotationNamespace) {
            switch(get_class($annotationNamespace)) {
                case 'Gedmo\Mapping\Annotation\Locale':
                case 'Gedmo\Mapping\Annotation\TreeRoot':
                case 'Gedmo\Mapping\Annotation\TreeLeft':
                case 'Gedmo\Mapping\Annotation\TreeRight':
                case 'Gedmo\Mapping\Annotation\TreeParent':
                    $exclude = true;
                    break;
               case 'Gedmo\Mapping\Annotation\Timestampable':
//                    $element = $this->doctrineFormElement($annotationNamespace->targetEntity);
//                    (on="update")(on="create")
                    break;
                case 'Gedmo\Mapping\Annotation\Slug':
//                    $element = $this->doctrineFormElement($annotationNamespace->targetEntity);
//                    (fields={"title"})
                    break;
                case 'Doctrine\ORM\Mapping\OneToOne':
                case 'Doctrine\ORM\Mapping\OneToMany':
                case 'Doctrine\ORM\Mapping\ManyToOne':
                case 'Doctrine\ORM\Mapping\ManyToMany':
                    $associated = true;
//                    $element = $this->doctrineFormElement($annotationNamespace->targetEntity);
                    $targetEntity = $annotationNamespace->targetEntity;
                    break;
                case 'Doctrine\ORM\Mapping\Column':
//                    $element = $this->doctrineFormElement($annotationNamespace->targetEntity);
                    $type = $annotationNamespace->type;
                    $nullable = $annotationNamespace->nullable;
                    $name = $annotationNamespace->name;
                    $length = $annotationNamespace->length;
                    $associated = false;
                    break;
                 case 'Wednesday\Mapping\Annotation\Form':
//                    $element = $this->wednesdayFormElement($annotationNamespace->renderer);
                    $renderer = $annotationNamespace->renderer;
                    $required = $annotationNamespace->required;
                    break;
                case 'Wednesday\Mapping\Annotation\Restable':
//                    $element = $this->wednesdayFormElement($annotationNamespace->forwardTo);
                    $forwardTo = $annotationNamespace->forwardTo;
                    $exclude = $annotationNamespace->exclude;
                    break;
                case 'Wednesday\Mapping\Annotation\LuceneIndex':
//                    $element = $this->wednesdayFormElement($annotationNamespace->type);
                    $required = $annotationNamespace->indexes;
                    $follow = $annotationNamespace->follow;
                    break;
                default:
                    break;
            }
        }
    }

    /**
     *
     * @param string $entity
     * @return array
     */
    public function getElements($entity) {
        if(!isset($this->formMapping)) {
            $this->init($entity);
        }
        return $this->formMapping[$entity];
    }
//
//    /**
//     *
//     * @param string $annotation
//     */
//    protected function mapAnnotation($annotation) {
//        $mapped = array();
//        $type = 'input';
//        $targetEntity = $associated = $required = false;
//        $options = null;
//        foreach($annotation as $annotationNamespace) {
//            $this->log->debug('Get Entity: '.get_class($annotationNamespace)." - ");
//            switch(get_class($annotationNamespace)) {
//                case 'Doctrine\ORM\Mapping\OneToOne':
//                case 'Doctrine\ORM\Mapping\OneToMany':
//                case 'Doctrine\ORM\Mapping\ManyToOne':
//                case 'Doctrine\ORM\Mapping\ManyToMany':
//                    $associated = true;
//                    $type = 'select';
//                    //if(empty($entity)===false){
//                        $options = $this->getEntityOptions($annotationNamespace->targetEntity);
////                    } else {
////                        $options = array();
////                    }
//                    $targetEntity = $annotationNamespace->targetEntity;
//                    break;
//                case 'Doctrine\ORM\Mapping\Column':
//                    $type = $this->doctrineToForm($annotationNamespace->type);
//                    break;
//                case 'Wednesday\Mapping\Annotation\Form':
//                    $type = $this->getRendererType($annotationNamespace->renderer);
//                    $options = $this->getRendererOptions($annotationNamespace->renderer, $annotationNamespace->options);
//                    $required = $annotationNamespace->required;
//                    break;
//            }
//        }
//        $mapped = array('type'=>$type,'required'=>$required,'options'=>$options,'associated'=>$associated);
//        return $mapped;
//    }
//
//    /**
//     *
//     * @param string $type
//     * @return string
//     */
//    protected function getRendererType($type) {
//        $returnType = 'input';
//        switch($type) {
//            case 'text':
//                $returnType = 'textarea';
//                break;
//            case 'rich':
//                $returnType = 'rich';
//                break;
//            case 'checkbox':
//                $returnType = 'checkbox';
//                break;
//            case 'radioyesno':
//                $returnType = 'radioyesno';
//                break;
//            case 'datetime':
//                $returnType = 'datepicker';
//                break;
//            case 'colourpicker':
//                $returnType = 'colourpicker';
//                break;
//            case 'modulepicker':
//                $returnType = 'modulepicker';
//                break;
//            case 'resourcepicker':
//                $returnType = 'resourcepicker';
//                break;
//            case 'categorypicker':
//                $returnType = 'categorypicker';
//                break;
//            case 'eachsubform':
//                $returnType = 'eachsubform';
//                break;
//            case 'select':
//            case 'selectconst':
//                $returnType = 'select';
//                break;
//            case 'selectoptions':
//                $returnType = 'selectoptions';
//                break;
//        }
//        return $returnType;
//    }
//
//    /**
//     *
//     * @param string $type
//     * @return string
//     */
//    protected function getRendererOptions($type, $targetEntity="") {
//        $returnType = 'input';
//        switch($type) {
//            case 'text':
//                $returnType = 'textarea';
//                break;
//            case 'selectconst':
//                $returnType = array_flip($this->mappedConstants);
//                break;
//            case 'select':
//                $returnType = array('name1'=>'value','name2'=>'value','name3'=>'value','name4'=>'value');
//                break;
//            case 'modulepicker':
//                if($targetEntity != false) {
//                    $returnType = array();
//                }
//                break;
//            case 'eachsubform':
//                $returnType = array('targetEntity'=>$targetEntity,'linked'=>$this->getEntityOptions($targetEntity));
//                break;
//            case 'resourcepicker':
//                if($targetEntity != false) {
//                    $returnType = array();
//                }
//                break;
//            case 'selectoptions':
//                $optionsArray= array();
//                foreach (explode(',', $targetEntity) as $option) {
//                    $optionsArray[strtolower($option)] = $option;
//                }
//                $returnType = $optionsArray;
//                break;
//            case 'categorypicker':
//                $returnType = array();
//                break;
//        }
//        return $returnType;
//    }
//
//    /**
//     *
//     * @param string $type
//     * @return string
//     */
//    protected function getEntityOptions($entity) {
//        $bootstrap = Front::getInstance()->getParam("bootstrap");
//        $em = $bootstrap->getContainer()->get('entity.manager');
//        $optionArray = array();
//        if(empty($entity)===true){
//            return $optionArray;
//        }
//        $this->log->debug('Get Entity: '.$entity);
//        $ents = $em->getRepository("Application\Entities\\".$entity)->findAll();
//        foreach ($ents as $ent) {
//            $optionArray[$ent->id] = $ent->title;
//        }
//        return $optionArray;
//    }
//
//    /**
//     *
//     * @param string $type
//     * @return string
//     */
//    protected function doctrineToForm($type) {
//        $returnType = 'input';
//        switch($type) {
//            case 'text':
//                $returnType = 'textarea';
//                break;
//        }
//        return $returnType;
//    }

}
