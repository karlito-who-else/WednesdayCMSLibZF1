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
class MapperAbstract {
    const SYSTEM_NAMESPACE = "Wednesday\Models\\";
    const ENTITY_NAMESPACE = "Application\Entities\\";
    const _SKIP_PROPERTIES = 'created,updated,lft,rgt,lvl,root,locale,slug';
    const _RESTRICTED_PROPERTIES = 'id';

    protected $entityName;

    public $formMapping;

    protected $mappedConstants;

    protected $log;

    /**
     *
     * @param string $name
     * @param array $skip
     * @param array $restrict
     */
    public function __construct($name, $skip = false, $restrict = false) {
      $this->entityName = $name;

      #Get Logger
      $bootstrap = Front::getInstance()->getParam("bootstrap");
      $this->log = $bootstrap->getResource('Log');//->getContainer()->get('logger');

//      $this->log->debug(get_class($this).'::__construct('.$name.')');
      $this->init($name, $skip, $restrict);
    }

    /**
     *
     * @param string $name
     * @param array $skip
     * @param array $restrict
     */
    public function init($name, $skip, $restrict) {
        #Grab Annotations
        $reader = new AnnotationReader();
        $reflClass = new \ReflectionClass($this->entityName);
        $classAnnotations = $reader->getClassAnnotations($reflClass);

        #Build Annotation Map foreach property of the Entity
        $skipped = explode(',',  self::_SKIP_PROPERTIES);
        $restricted = explode(',',  self::_RESTRICTED_PROPERTIES);
        $skipped = ($skip!=false)?array_merge($skipped, $skip):$skipped;
        $restricted = ($restrict!=false)?array_merge($restricted, $restrict):$restricted;
        $reflProperties = $reflClass->getProperties();
        $this->mappedConstants = $reflClass->getConstants();
        $propAnnotations = array();

        foreach($reflProperties as $property) {
            if(in_array($property->name, $skipped)) {
                continue;
            }
            $reflProperties = new \ReflectionProperty($property->class, $property->name);
            $mappedAnnotation = (in_array($property->name, $restricted))?array('type'=>'hidden','required'=>false):$this->mapAnnotation($reader->getPropertyAnnotations($reflProperties));
            $propAnnotations[$this->entityName][$property->name] = $mappedAnnotation;
        }

        $this->formMapping = $propAnnotations;
        $this->log->debug(get_class($this).'::init('.$name.')');
//        $this->log->debug($this->formMapping);
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

    /**
     *
     * @param string $annotation
     */
    protected function mapAnnotation($annotation) {
        $mapped = array();
        $type = 'input';
        $targetEntity = $associated = $required = false;
        $options = null;
        foreach($annotation as $annotationNamespace) {
            switch(get_class($annotationNamespace)) {
                case 'Doctrine\ORM\Mapping\OneToOne':
                case 'Doctrine\ORM\Mapping\OneToMany':
                case 'Doctrine\ORM\Mapping\ManyToOne':
                case 'Doctrine\ORM\Mapping\ManyToMany':
                    $associated = true;
                    $type = 'select';
                    $options = $this->getEntityOptions($annotationNamespace->targetEntity);
                    $targetEntity = $annotationNamespace->targetEntity;
                    //array('entity'=>$annotationNamespace->targetEntity,'selected'=>'');
                    break;
                case 'Doctrine\ORM\Mapping\Column':
                    $type = $this->doctrineToForm($annotationNamespace->type);
                    break;
                case 'Wednesday\Mapping\Annotation\Form':
                    $type = $this->getRendererType($annotationNamespace->renderer);
                    $options = $this->getRendererOptions($annotationNamespace->renderer, $targetEntity);
                    $required = $annotationNamespace->required;
                    break;
            }
        }
        $mapped = array('type'=>$type,'required'=>$required,'options'=>$options,'associated'=>$associated);
        return $mapped;
    }

    /**
     *
     * @param string $type
     * @return string
     */
    protected function getRendererType($type) {
        $returnType = 'input';
        switch($type) {
            case 'text':
                $returnType = 'textarea';
                break;
            case 'rich':
                $returnType = 'rich';
                break;
            case 'checkbox':
                $returnType = 'checkbox';
                break;
            case 'radioyesno':
                $returnType = 'radioyesno';
                break;
            case 'datetime':
                $returnType = 'datepicker';
                break;
            case 'colourpicker':
                $returnType = 'colourpicker';
                break;
            case 'modulepicker':
                $returnType = 'modulepicker';
                break;
            case 'resourcepicker':
                $returnType = 'resourcepicker';
                break;
            case 'categorypicker':
                $returnType = 'categorypicker';
                break;
            case 'eachsubform':
                $returnType = 'eachsubform';
                break;
            case 'select':
            case 'selectconst':
                $returnType = 'select';
                break;
            case 'selectoptions':
                $returnType = 'selectoptions';
                break;
        }
        return $returnType;
    }

    /**
     *
     * @param string $type
     * @return string
     */
    protected function getRendererOptions($type, $targetEntity="") {
        $returnType = 'input';
        switch($type) {
            case 'text':
                $returnType = 'textarea';
                break;
            case 'selectconst':
                $returnType = array_flip($this->mappedConstants);
                break;
            case 'select':
                $returnType = array('name1'=>'value','name2'=>'value','name3'=>'value','name4'=>'value');
                break;
            case 'modulepicker':
                if($targetEntity != false) {
                    $returnType = array();
                }
//                $returnType = array('name'=>'Name', 'image'=>'Image', 'floorplan'=>'Floorplan', 'map'=>'Map', 'lifestyle'=>'Lifestyle', 'history'=>'History', 'register'=>'Register');
                break;
            case 'eachsubform':
                //die(print_r($type,true));
                $returnType = array('targetEntity'=>$targetEntity,'linked'=>$this->getEntityOptions($targetEntity));
                break;
            case 'resourcepicker':
                if($targetEntity != false) {
                    $returnType = array();
                    //$this->getEntityOptions($targetEntity);
                }
                break;
            case 'selectoptions':
                $optionsArray= array();
                foreach (explode(',', $options) as $option) {
                    $optionsArray[strtolower($option)] = $option;
                }
                $returnType = $optionsArray;
                break;
            case 'categorypicker':
                $returnType = array();
                break;
        }
        return $returnType;
    }

    /**
     *
     * @param string $type
     * @return string
     */
    protected function getEntityOptions($entity) {
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $optionArray = array();
        $ents = $em->getRepository($this->getEntityNamespace($entity))->findAll();

        foreach ($ents as $ent) {
          $optionArray[$ent->id] = $ent->title;
        }

        return $optionArray;
    }

    /**
     *
     * @param string $type
     * @return string
     */
    protected function doctrineToForm($type) {
      $returnType = 'input';
      switch($type) {
          case 'text':
          $returnType = 'textarea';
          break;
      }
      return $returnType;
    }

    protected function getEntityNamespace($entityName) {
        $this->log->crit($entityName);
        $entity = str_replace(array(
            '\\'.self::SYSTEM_NAMESPACE,
            '\\'.self::ENTITY_NAMESPACE), '', $entityName);
        $this->log->crit($entity);
        switch ($entity) {
            case 'Categories':
            case 'Tags':
            case 'MetaData':
            case 'Settings':
                $entityClass = self::SYSTEM_NAMESPACE . $entity;
                break;
            default:
                $entityClass = self::ENTITY_NAMESPACE . $entity;
                break;
        }
        return $entityClass;
    }

}

