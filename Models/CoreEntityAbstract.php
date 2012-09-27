<?php
namespace Wednesday\Models;
//namespace Application\Entities;

use \Zend_Controller_Front as Front,
    \Doctrine\ORM\PersistentCollection,
    \Doctrine\Common\Collections\ArrayCollection,
    \Doctrine\Common\Annotations\AnnotationReader,
    \Gedmo\Mapping\Annotation AS Gedmo,
	\Doctrine\ORM\Mapping AS ORM,
    \Wednesday\Mapping\Annotation AS WED;

/**
 * CoreItems
 *
 */
abstract class CoreEntityAbstract implements CoreEntityInterface
{

    /**
     *
     * @param type $name
     * @param type $value
     */
    public function __set($name, $value) {
        if(property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    /**
     *
     * @param type $name
     * @return type
     */
    public function __get($name) {
        $skip = array('_entityPersister','_identifier','__isInitialized__');
        if((property_exists($this, $name))&&(!in_array($name,$skip))) {
            return $this->$name;
        }
        return false;
    }

    /**
     * toArray
     *
     * return Object
     */
    public function toArray($short = false, $nested = false, $filters = false) {
//        return get_object_vars($this);
        $filtered = get_object_vars($this);
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        #Get Logger
        $this->log = $bootstrap->getResource('Log');
        #Get EntityManager
        $em = $bootstrap->getContainer()->get('entity.manager');
        
        if((method_exists($this, "__isInitialized"))) {
            if(!$this->__isInitialized()) {
                $this->__load();
            }
        }
        if(substr(get_class($this), 0,strlen('Proxies\__CG__'))=='Proxies\__CG__') {
            $metaClass = str_replace('Proxies\__CG__\\', '', get_class($this));
            //Proxies\__CG__\Wednesday\Models\Categories
        } else {
            $metaClass = get_class($this);
        }
        
        $meta = $em->getClassMetadata($metaClass);
//        $reader = new AnnotationReader();
//        $reflClass = new \ReflectionClass(get_class($this));
//        $this->classAnnotations = $reflClass->getProperties();//$reader->getClassAnnotations($reflClass);
//        $this->log->debug(array_keys($filtered));
        if(!$short) {
            foreach($meta->associationMappings as $association) {
//                $this->log->debug("::-- ".$association['fieldName']." => ".$association['targetEntity']);
                if(isset($filtered[$association['fieldName']])===true) {
//                    $this->log->debug(get_class($this->$association['fieldName']).' - '.$association['fieldName']);
                    if($this->$association['fieldName'] instanceof PersistentCollection) {
//                        $this->log->debug($association['fieldName']);
                        $fields = array();
//                        $this->log->debug($filters['properties'][$association['fieldName']]);
//                        $this->log->debug($association['fieldName']);
                        foreach($this->$association['fieldName'] as $field) {
                            //TODO Get annotation data for all types of annotations so we can read Wed Annotations.
//                            $this->log->debug("Roll Em:: ".get_class($field)."::".$filters[$field->id]['forwardTo']);
                            if(isset($filters['properties'][$association['fieldName']]['forwardTo'])!==false) {
                                $forward = $filters['properties'][$association['fieldName']]['forwardTo'];
////                                $entref = $field->$forward;
////                                $this->log->debug(get_class($entref));
//                                $fields[$field->sortorder] = $entref->toArray($short,$nested,$filters);
//                                if($nested) {
                                    $fields[$field->id] = $field->$forward->toArray($short,$nested,$filters);
//                                } else {
//                                    $fields[$field->id] = $field->$forward->id;
//                                }
                            } else {
                                if($nested) {
                                    $fields[$field->id] = $field->toArray($short,$nested,$filters);
                                } else {
                                    $fields[$field->id] = $field->id;
                                }
                            }
                        }
                        $filtered[$association['fieldName']] = $fields;
                    } else {
//                        $tmp = $this->$association['fieldName']->id;
//                        $filtered[$association['fieldName']] = $this->$association['fieldName']->toArray(true,$nested);
//	                    $this->log->debug($association['fieldName']);
                        if(!$this->$association['fieldName']->__isInitialized__) {
//                            $this->$association['fieldName']->__load();
//                            $filtered[$association['fieldName']] = $this->$association['fieldName']->toArray(true,$nested);
                        } else {
                            $filtered[$association['fieldName']] = $this->$association['fieldName']->toArray(true,$nested);
                        }
                    }
                }
            }
        } else {
            foreach($meta->associationMappings as $association) {
//                $this->log->debug("::-- ".$association['fieldName']." => ".$association['targetEntity']);
                //$this->log->debug($association);
                if(isset($filtered[$association['fieldName']])===true) {
                    $istrue = ($this->$association['fieldName'] instanceof PersistentCollection)?"true":"false";
//                    $this->log->debug(get_class($this->$association['fieldName'])." - ".$istrue);
                    if($this->$association['fieldName'] instanceof PersistentCollection) {
//                        $this->log->debug($association['fieldName']);
                        $fields = array();
//                        $this->log->debug($filters['properties'][$association['fieldName']]);
//                        $this->log->debug($association['fieldName']);
                        foreach($this->$association['fieldName'] as $field) {
//                            $this->log->debug("Roll Em:: ".get_class($field)."::".$filters['properties'][$association['fieldName']]['forwardTo']);
//                            $this->log->debug($association['fieldName']);
//                            $this->log->debug($meta->getFieldMapping($association['fieldName']));
                            if(isset($filters['properties'][$association['fieldName']]['forwardTo'])!==false) {
                                $forward = $filters['properties'][$association['fieldName']]['forwardTo'];
//                                $entref = $field->$forward;
////                                $this->log->debug(get_class($entref));
//                                $fields[$field->sortorder] = $entref->id;
                                $fields[$field->id] = $field->$forward->id;
                            } else {
                                $fields[$field->id] = $field->id;
//                                if($nested) {
//                                    $fields[$field->id] = $field->toArray($short,$nested,$filters);
//                                } else {
//                                    $fields[$field->id] = $field->id;
////                                    $fields[$field->id] = $field->id;
//                                }
                            }
                        }
                        $filtered[$association['fieldName']] = $fields;
                    } else {
//	                    $this->log->debug($association['fieldName']);
						if(!$this->$association['fieldName']->__isInitialized__) {
//							$this->$association['fieldName']->__load();
//                            $filtered[$association['fieldName']] = $this->$association['fieldName']->id;
						} else {
                            $ent = $this->$association['fieldName'];
                            $filtered[$association['fieldName']] = $ent->id;
                        }
                    }
                }
            }
        }
//        $this->log->debug($filtered);
        foreach($filtered as $key => $val){
            if($val instanceof \DateTime){
                $filtered[$key] = $val->format('d/m/Y');
            }
        }
        return $filtered;
    }

    /**
     * toXml
     *
     * return Object
     */
    public function toXml($nested = false, $short = false, $filters = false) {
        $values = $this->toArray($short, $nested, $filters);
        $properties = get_object_vars($this);
        $xml = "";
        foreach(array_keys($properties) as $property) {
            $xml .= "<{$property}>".$values[$property]."</{$property}>"."\n";
        }
        //print_r(array_keys($properties),true)
        return "<entity>"."\n".$xml."\n"."</entity>"."\n";
    }

    /**
     * toJsonObject
     *
     * return Object
     */
    public function toJsonObject($nested = false, $short = false, $filters = false) {
        return (object) $this->toArray($short, $nested, $filters);
    }

    /**
     * createAction
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function createAction($reqVars) {
        $this->put($reqVars);
    }

    /**
     * readAction
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function readAction($reqVars) {
        $this->toArray();
    }

    /**
     * updateAction
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function updateAction($reqVars) {
        $this->put($reqVars);
    }

    /**
     * deleteAction
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function deleteAction($reqVars) {
        $this->delete($reqVars);
    }

    /**
     * configure
     *
     * @return void
     */
    public function configure($reqVars) {

    }

    /**
     * delete Action
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function options($reqVars) {
        return get_class_methods(get_class($this));
    }

    /**
     * short Action
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function head($reqVars) {
        $this->toArray(true);
    }

    /**
     * read Action
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function get($reqVars) {
        $this->toArray(false);
    }

    /**
     * create Action
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function post($reqVars) {
        $this->put($reqVars);
    }

    /**
     * update Action
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function put($reqVars) {
    	#Get bootstrap object.
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        #Get Logger
        $this->log = $bootstrap->getResource('Log');
//        $current = $this->toArray();
//        foreach ($current as $key => $value) {
        $current = get_object_vars($this);
        foreach ($current as $key => $value) {
            //$this->log->info("Key {$key}");
            if((isset($reqVars[$key])!==false)&&(empty($reqVars[$key])===false)) {
                //$this->log->debug("Set {$key}");
                if($this->$key instanceof \DateTime) {
                    #TODO Handle Locale correctly.
                    $date = new \Zend_Date($reqVars[$key], \Zend_Date::DATES, 'en_GB');
                    $this->$key = new \DateTime();
                    $this->$key->setTimestamp($date->getTimestamp());
                } else {
                    $this->$key = $reqVars[$key];
//                    if(in_array($key, self::$_purifyFields)) {
//                        $this->log->debug("purifyText {$key}");
//                        $this->$key = $this->purifyText($reqVars[$key]);
//                    }
                }
            } else {
//                $this->log->debug("Can't set {$key} to '{$reqVars[$key]}' ");
            }
        }
//        $this->log->debug("Set all done");
    }

    /**
     * deleteAction
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function delete($reqVars) {
        $this->id;
    }

/*    */
}
