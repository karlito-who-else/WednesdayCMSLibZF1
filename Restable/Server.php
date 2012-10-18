<?php
namespace Wednesday\Restable;

use Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\ORM\ORMException,
    \Zend_Controller_Request_Abstract as RequestAbstract,
    \Zend_Controller_Front as Front;

/**
 * Description of Server
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Server {

    /**
     * Doctrine\ORM\EntityManager object wrapping the entity environment
     * @var Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     *
     * Access to Zend_Log.
     * @var Zend_Log
     */
    private $log;

    /**
     *
     * Access to Zend_Config_Ini.
     * @var Zend_Config_Ini
     */
    private $config;

    /**
     *
     * Request Parser.
     * @var Wednesday\Restable\RequestParser
     */
    private $request;

    /**
     *
     * Request Parser.
     * @var RequestAbstract
     */
    private $originalrequest;

    /**
     *
     * Method.
     * @var string
     */
    private $method;

    /**
     *
     * Aliases.
     * @var array
     */
    private $aliases;

    /**
     *
     * Actions.
     * @var array
     */
    private $actions;

    /**
     *
     * Currently active method.
     * @var string
     */
    private $active;

    /**
     *
     * Currently active method.
     * @var string
     */
    private $serviceType;

    /**
     *
     * @param RequestAbstract $request
     */
    public function __construct(RequestAbstract $request) {
    	#Get bootstrap object
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        #Get Logger
        $this->log = $bootstrap->getResource('Log');
        #Get bootstrap object
        $this->config = $bootstrap->getContainer()->get('config');
    	#Get Doctrine Entity Manager
        $this->em = $this->getD2EntityManager();//$bootstrap->getContainer()->get('entity.manager');
//        $this->log->info($request);
        #Set Request
        $this->originalrequest = $request;
        #Set Request Parser
        $this->request = new RequestParser($request);
//        #Get Method
//        $this->method = $this->request->getMethod();
        #Log.
        $this->log->debug(get_class($this)."::construct()");
    }

    public function execute() {
        $this->log->debug(get_class($this)."::execute");
        if(isset($this->aliases)===false) {
            throw new \Exception('REST Server not configured.', 500);
        }
        $this->repository = false;
        $params = $this->getRequestParams();
        foreach ($this->aliases as $alias => $namespace) {
            if($params['entity'] == $alias){
                $this->active = $alias;
                $this->serviceType = 'entity';
                $this->repository = $this->em->getRepository($namespace);
                $this->checkEntityAnnotations($namespace);
            }
        }
        foreach ($this->actions as $action => $namespace) {
            if($params['entity'] == $action) {
                $this->active = $action;
                $this->serviceType = 'action';
                $this->repository = new $namespace($this->originalrequest,$this->request);
            }
        }
        return $this->repository;
    }

    protected function getD2EntityManager($dbname = 'default', $listeners = true) {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $doctrine = $bootstrap->getResource('doctrine');
        return $doctrine->getEntityManager($dbname, $listeners);
    }
    
    protected function checkEntityAnnotations($entityNamespace = false) {
        if($entityNamespace == false){
            $entityNamespace = $this->getActiveNamespace();
        }
        #TODO Find a way to allow entity to decide how it should interact with REST?
        $reader = new AnnotationReader();
        $reflClass = new \ReflectionClass($entityNamespace);
        $reflProperties = $reflClass->getProperties();
//        $classAnnotations = $this->_checkClassAnnotation($reader->getClassAnnotations($reflClass));
//        $mappedConstants = $reflClass->getConstants();
        $mappedProperties = array();
//        $this->log->debug($classAnnotations);
        foreach($reflProperties as $property) {
            $reflProperties = new \ReflectionProperty($property->class, $property->name);
            $annotations = $reader->getPropertyAnnotations($reflProperties);
            $mappedProperties[$property->name] = $this->_checkPropertyAnnotation($annotations);
//            $this->   ($property);
        }
//        $this->log->debug($mappedProperties);
//        $this->log->debug("EO Props");
        $params = $this->getRequestParams();
        $filters = array();
        $filters['properties'] = $mappedProperties;
        foreach ($params['get'] as $key => $value) {
            $filter = ($key == 'q')?'title':$key;
            $filters[$key]['filter'] = $filter;
            $filters[$key]['value'] = $value;
        }
//        $this->log->debug($filters);
        return $filters;
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

    private function _checkPropertyAnnotation($annotations) {
        #Property Annotations
        $options = array();
//        $this->log->debug($annotations);
        foreach($annotations as $annotationNamespace) {
            switch(get_class($annotationNamespace)) {
                case 'Gedmo\Mapping\Annotation\Locale':
                case 'Gedmo\Mapping\Annotation\TreeRoot':
                case 'Gedmo\Mapping\Annotation\TreeLeft':
                case 'Gedmo\Mapping\Annotation\TreeRight':
                case 'Gedmo\Mapping\Annotation\TreeParent':
                    $options['exclude'] = true;
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
                    $options['associated'] = true;
//                    $element = $this->doctrineFormElement($annotationNamespace->targetEntity);
                    $targetEntity = $annotationNamespace->targetEntity;
                    break;
                case 'Doctrine\ORM\Mapping\Column':
//                    $element = $this->doctrineFormElement($annotationNamespace->targetEntity);
                    $options['type'] = $annotationNamespace->type;
                    $options['nullable'] = $annotationNamespace->nullable;
                    $options['name'] = $annotationNamespace->name;
                    $options['length'] = $annotationNamespace->length;
                    $options['associated'] = false;
                    break;
                 case 'Wednesday\Mapping\Annotation\Form':
//                    $element = $this->wednesdayFormElement($annotationNamespace->renderer);
                    $options['renderer'] = $annotationNamespace->renderer;
                    $options['required'] = $annotationNamespace->required;
                    break;
                case 'Wednesday\Mapping\Annotation\Restable':
//                    $element = $this->wednesdayFormElement($annotationNamespace->forwardTo);
                    $options['forwardTo'] = $annotationNamespace->forwardTo;
                    $options['exclude'] = $annotationNamespace->exclude;
                    break;
                case 'Wednesday\Mapping\Annotation\LuceneIndex':
//                    $element = $this->wednesdayFormElement($annotationNamespace->type);
                    $options['required'] = $annotationNamespace->indexes;
                    $options['follow'] = $annotationNamespace->follow;
                    break;
                default:
                    break;
            }
        }
//        $this->log->debug($options);
        return $options;
    }

    public function getTreeFilter() {
        $params = $this->getRequestParams();
        $restrictions = array();
        foreach ($params['get'] as $key => $value) {
            $restrictions[$key]['property'] = $key;
            $restrictions[$key]['filter'] = $value;
        }
        return $restrictions;
    }

	/*
	*
	*	Returns the same data as getFilteredEntity but adding the children content, not just the ids. This is useful to retrieve all directory info in one call.
	*   IT ALWAYS RETURN A JSON
	*/
    public function getFilteredEntityWithChildrenInfo($tree = false) {    

    	$params = $this->getRequestParams();
        $filters = $this->checkEntityAnnotations();
        $this->log->debug(get_class($this)."::getFilteredEntityWithChildrenInfo()");

       if((count($filters)>1)&&($tree==false)) {
            $ent = $this->repository->findOneBy($filters['q']['filter'], $filters['q']['value']);
        } else {
            $id = $params['id'];
            $ent = $this->repository->findOneById($id);
        }
                
		$entity_info = $ent->toJsonObject(true,true);        
        $children_info = array();
//        $entity_children = $entity_info->children;
        $entity_info->children = array();

        $this->log->debug($entity_info);
        foreach ($ent->children as $child) {
            //if we are listing an image, we show the newsthumb image for the preview instead the original image
            if($child->type == 'image') {
                $child->setVariation('homepagesmall');
                $child->linkhomepagesmall = $child->link;
            }            
            $children_info[] = $child->toJsonObject(true,true);           
        }
        $entity_info->children = $children_info;
        $this->log->debug($entity_info);
        return $entity_info;        
    }
        
    /**
     *
     * @param type $asJson
     * @param type $tree
     * @return type 
     */
    public function getFilteredEntity($asJson = true, $tree = false) {
        $params = $this->getRequestParams();
        $filters = $this->checkEntityAnnotations();
        $this->log->info(get_class($this)."::getFilteredEntity()");
//        $this->log->debug($filters);
        if((count($filters)>1)&&($tree==false)) {
            $ent = $this->repository->findOneBy($filters['q']['filter'], $filters['q']['value']);
//            if (method_exists($ent,'setTranslatableLocale')) {
//                $ent->setTranslatableLocale($this->locale->__toString());
//                $this->em->refresh($entity);
//            }
        } else {
            $id = $params['id'];
            if(is_numeric($id)) {
                $ent = $this->repository->findOneById($id);
            } else {
                throw new \Exception('Bad request', 400);
            }
        }
        if(!isset($ent)) {
            return null;
        }
        //in case it's video, we need the return the hover play coords for the admin area.
        if (preg_match('/video/i',$ent->mimetype) && count($ent->metadata) > 0) {
            
            $coords = $ent->getMetadata('video_resource_coords_hover_play');
            $ent->video_hover_play_coords = unserialize($coords->content);
//            foreach ($ent->metadata as $metadata) {
//                if ($metadata->title == 'video_resource_coords_hover_play') {
//                    $ent->video_hover_play_coords = unserialize($metadata->content);
//                    break;
//                }
//            }
        }
        $this->log->info(get_class($ent)."(".$ent->id.")");
        if($asJson==true) {
            $hasMeta = false;
            if(isset($ent->metadata)) {
                $hasMeta = ($ent->metadata->count() > 0)?true:false;
            }
            if($hasMeta) {
                return $ent->toJsonObject(true,false,$filters);
                
            } else {
                return $ent->toJsonObject(false,true,$filters);
            }
        }
        return $ent;
    }

    /**
     *
     * @param type $asJson
     * @param type $tree
     * @return type 
     */
    public function getFilteredEntities($asJson = true, $tree = false) {
        $filters = $this->checkEntityAnnotations();
        $this->log->debug(get_class($this)."::getFilteredEntities()");
        $this->log->debug($filters);
        if((count($filters)>1)&&($tree==false)) {
            try {
                $ent = $this->repository->searchFor($filters['q']['filter'], $filters['q']['value']);
            } catch (\BadMethodCallException $exc) {
                $ent = $this->repository->findBy(array($filters['q']['filter'] => $filters['q']['value']));
            }
        } else if($tree==true) {
            $em = $this->getD2EntityManager('default',false);
            $repository = $em->getRepository($this->getActiveNamespace());
            return $repository->getRootNodes();
        } else {
            $ent = $this->repository->findAll();
        }
        if($asJson==true) {
            return $ent->toJsonObject(true,true);
        }
        return $ent;
    }

    public function getRenderMode() {
        $params = $this->request->getParams();
        $mode = $params['action'];
        $this->log->debug(get_class($this)."::getRenderMode(:".$mode.":)");
        return $mode;
    }

    public function getActiveNamespace() {
        return $this->aliases[$this->active];
    }

    public function setActions(array $actions) {
        $this->actions = $actions;
        $this->log->debug(get_class($this)."::setActions()");
        return $this;
    }

    public function setAction($action, $class) {
        $this->actions[$action] = $class;
        $this->log->debug(get_class($this)."::setAction()");
        return $this;
    }

    public function setAliases(array $aliases) {
        $this->aliases = $aliases;
        $this->log->debug(get_class($this)."::setAliases()");
        return $this;
    }

    public function setAlias($alias, $class) {
        $this->aliases[$alias] = $class;
        $this->log->debug(get_class($this)."::setAlias()");
        return $this;
    }

    public function getResponseActionName() {
        $this->log->debug(get_class($this)."::getResponseActionName()");
        return $this->request->getMethod();
    }

    public function getRequestParams() {
        $this->log->debug(get_class($this)."::getResponseParams()");
        return $this->request->getParams();
    }

    public function getActionType() {
        $this->log->debug(get_class($this)."::getActionType()");
        return $this->serviceType;
    }
}
