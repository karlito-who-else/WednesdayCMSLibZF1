<?php
namespace Wednesday\Backbone;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Annotations\AnnotationReader,
    \Zend_Rest_Controller as ZendRestController,
    \Zend_Session_Namespace,
    \Zend_Auth;

/**
 * RestActionController - The rest error controller class
 * Description of AdminAction
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 * @version    $Id: 1.7.4 RC1, jameshelly $
 */
class ActionController extends ZendRestController {

    const RESOURCES 		= "Application\Entities\MediaResources";
    const VARIATIONS 		= "Application\Entities\MediaVariations";
    const GALLERIES 		= "Application\Entities\MediaGalleries";
    const PAGES 			= "Application\Entities\Pages";
    const USERMENUITEMS     = "Application\Entities\UserMenuItems";
    const TEMPLATES 		= "Application\Entities\Templates";
    const TVARS 			= "Application\Entities\TemplateVariables";
    const TVARCONTENTS 		= "Application\Entities\VariablesContent";
    const USERS 			= "Application\Entities\Users";
    const DISCOUNTS 		= "Application\Entities\Discounts";
    const BLOGITEMS 		= "Application\Entities\BlogItems";
    const HCARDS 			= "Application\Entities\Hcards";
    const ADDRESSES 		= "Application\Entities\Addresses";
    const HOMEPAGEITEMS 	= "Application\Entities\HomepageItems";
    const HOMEPAGE 			= "Application\Entities\Homepage";
    const BRANDS 			= "Application\Entities\Brands";
    const PRODUCTS 			= "Application\Entities\Products";
    const LOOKS 			= "Application\Entities\Looks";
    const GRIDS 			= "Application\Entities\Grids";
    const ACLUSERROLES 		= "Application\Entities\AclUserRoles";
    const COLLECTIONS 		= "Application\Entities\Collections";
    const CATEGORIES 		= "Wednesday\Models\Categories";
    const TAGS 				= "Wednesday\Models\Tags";
    const SETTINGS 			= "Wednesday\Models\Settings";
    const METADATA 			= "Wednesday\Models\MetaData";
    const MEDIABROWSER 		= "Wednesday\Restable\Action\MediaBroswer";
    const AUTH 				= "Wednesday\Restable\Action\Auth";
    const SEARCH 			= "Wednesday\Restable\Action\Search";
    const MEDIA_VARIATIONS 	= "Wednesday\Restable\Action\Variations";
    const RESOURCES_VIDEOS  = "Wednesday\Restable\Action\Videos";

    const CACHE_TREE_VARIABLE = 'browsetree';
    const CACHE_DIR = '/../private/data/cache/';
    const CACHE_LIFETIME = 7200; //2 hours

    /**
     *
     * @var \Wednesday\Restable\Server
     */
    private $_server;

    /**
     *
     * @var string
     */
    protected $method;

    /**
     * This action handles
     *    - Default Action Initialisation
     *  Ignight fuse, start burn.
     */
    public function init() {
        #Get bootstrap object.
        $bootstrap = $this->getInvokeArg('bootstrap');
        #Get Logger
        $this->log = $bootstrap->getResource('Log');
        #Get Doctrine Entity Manager
        $this->em = $bootstrap->getContainer()->get('entity.manager');
        #Get config
        $this->config = $bootstrap->getContainer()->get('config');

        #Init REST Server
        $this->_server = new Server($this->getRequest());
        $aliases = array(
            'resources' 	=> self::RESOURCES,
            'variations' 	=> self::VARIATIONS,
            'galleries' 	=> self::GALLERIES,
            'pages' 		=> self::PAGES,
            'menuitems'         => self::USERMENUITEMS,
            'templates' 	=> self::TEMPLATES,
            'tvars' 		=> self::TVARS,
            'tvarconts' 	=> self::TVARCONTENTS,
            'users' 		=> self::USERS,
            'blog' 		=> self::BLOGITEMS,
            'discounts' 	=> self::DISCOUNTS,
            'vcard' 		=> self::HCARDS,
            'categories' 	=> self::CATEGORIES,
            'tags' 		=> self::TAGS,
            'settings' 		=> self::SETTINGS,
            'addresses' 	=> self::ADDRESSES,
            'metadata' 		=> self::METADATA,
            'homepageitems'     => self::HOMEPAGEITEMS,
            'homepage' 		=> self::HOMEPAGE,
            'brands'		=> self::BRANDS,
            'products'		=> self::PRODUCTS,
            'looks'			=> self::LOOKS,
            'grids'			=> self::GRIDS,
            'acluserroles'	=> self::ACLUSERROLES,
            'collections'       => self::COLLECTIONS,
        );
        $actions = array(
            'auth' => self::AUTH,
            'mediabrowser' => self::MEDIABROWSER,
            'mediavariations' => self::MEDIA_VARIATIONS,
            'search' => self::SEARCH,
            'videos' => self::RESOURCES_VIDEOS
        );
        $this->_server->setAliases($aliases);
        $this->_server->setActions($actions);
        $this->method = $this->_server->getResponseActionName();
        $this->_repo = $this->_server->execute();

        #Enable Context Switching
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch
                ->addActionContext('index', array('xml', 'json'))
                ->addActionContext('get', array('xml', 'json'))
                ->addActionContext('put', array('xml', 'json'))
                ->addActionContext('post', array('xml', 'json'))
                ->addActionContext('delete', array('xml', 'json'))
                ->addActionContext('head', array('xml', 'json'))
                ->addActionContext('option', array('xml', 'json'))
                ->setAutoJsonSerialization(true)
                ->initContext();
        #disable view rendering
        //$this->_helper->viewRenderer->setNeverRender(true);
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        #Set Action Name for Request.
        if ($this->_server->getActionType() == 'action') {
            $this->method = 'method';
        }
        #Authentication
//        $this->_server->setPassword($password)->setUsername($username);
        #Security Options ::
        #Get Acl Object
        #TODO Set auth request inside server to allow you to use auth
        #Get Zend Auth.
//        $this->auth = Zend_Auth::getInstance();
//        if(!$this->auth->hasIdentity()) {
//            $this->_redirect('/admin/auth/login/');
//        }
        $this->getRequest()->setActionName($this->method);
        $this->view->response = "";
        $this->log->info(get_class($this) . '::init(' . $this->method . ')');
        #Allow xml to use view rendering.
        if ($this->getRequest()->getParam('format') == 'xml') {
            $this->_helper->viewRenderer->setNoRender(false);
        }
//        #TODO Find a way to allow entity to decide how it should interact with REST?
//        $reader = new AnnotationReader();
//        $reflClass = new \ReflectionClass($this->_server->getActiveNamespace());
//        $classAnnotations = $reader->getClassAnnotations($reflClass);
//        $reflProperties = $reflClass->getProperties();
//        $mappedConstants = $reflClass->getConstants();
//        $this->log->info($classAnnotations);
//        foreach($reflProperties as $property) {
//            $reflProperties = new \ReflectionProperty($property->class, $property->name);
//            $annotations = $reader->getPropertyAnnotations($reflProperties);
//            $this->log->info($property);
//            $this->log->info($annotations);
//        }
//        $this->log->info("EO Props");

        $this->messageQueue = $this->_helper->getHelper('FlashMessenger');
        $this->view->messages = $this->messageQueue->getMessages();

        $frontendOptions = array(
            'lifetime' => self::CACHE_LIFETIME, // cache lifetime of 2 hours
            'automatic_serialization' => true
        );

        $backendOptions = array(
            'cache_dir' => WEB_PATH . self::CACHE_DIR // Directory where to put the cache files
        );

        // getting a Zend_Cache_Core object
        $this->cache = \Zend_Cache::factory('Core',
                                     'File',
                                     $frontendOptions,
                                     $backendOptions);
    }

    /**
     * Store bookstrap
     * @see Controller/Zend_Controller_Action::preDispatch()
     */
    public function preDispatch() {
        $this->view->clearVars();
        $this->view->response = "";
        $this->log->info(get_class($this) . '::preDispatch(' . $this->method . ')');
    }

    /**
     * Store bookstrap
     * @see Controller/Zend_Controller_Action::preDispatch()
     */
    public function postDispatch() {
        #init live plugins.
        if ($this->getRequest()->getParam('format') == 'xml') {
            $this->_helper->viewRenderer->setNoRender(false);
            $this->view->response->xml = $this->toXml($this->view->response->data);
        }
        $this->log->info(get_class($this) . '::postDispatch(' . $this->method . ')');
    }

    /**
     *
     * @param type $message
     * @param type $code
     */
    public function respondError($message='Internal Server Error', $code=500) {
        $this->view->clearVars();
        $this->getResponse()->setHttpResponseCode($code);
        $this->view->response = (object) array('status' => false, 'code' => $code, 'message' => $message, 'method' => $this->method);
        $this->log->info(get_class($this) . '::indexAction(' . $this->method . ')');
    }

    /**
     *
     */
    public function methodAction() {
        if (isset($this->_repo) === false) {
            $this->respondError('no repo');
        } else {

            $api = $this->_server->getRequestParams();

            $service = $api['id'];
            #Run methods

            $this->view->response = $this->_repo->$service($this->_server->getRequestParams());
        }
        $this->log->info(get_class($this) . '::methodAction(' . $this->method . ')');
    }

    /**
     *
     */
    public function indexAction() {

        $params = $this->_server->getRequestParams();
        $entityName = $this->_server->getActiveNamespace();

        $this->log->info(get_class($this) . "indexAction('" . $this->method . "')");
        if ($this->_repo) {
            $ents = array();
            $mode = $this->_server->getRenderMode();

            #Handle index actions with get params.

            if($mode=='tree') {
                //we try to retrieve the tree from cache first. If we find it, we don't need to do any call to the DB and we don't need to build the jstree object
                //since it's already stored on cache. This cache variable will be deleted when the user modifies the tree, adding, updating or deleting new folders.
//                if( ($ents = $this->cache->load(self::CACHE_TREE_VARIABLE)) === false ) {
                    $entities = $this->_server->getFilteredEntities(false, true);
//                }
            } else {
                $entities = $this->_server->getFilteredEntities(false);
            }

            if (empty($entities) && $this->cache->load(self::CACHE_TREE_VARIABLE) === false) {
                $this->respondError('Not Found', 404);
                return;
            }

            $this->log->info(get_class($this).'::indexAction("'.$mode.'")');

            //if the entity is collections, we return them grouped by season/year
            if($entityName == self::COLLECTIONS) {
                foreach ($entities as $entity) {
                    $entity->imgsrc = $entity->gallery->featured->link;
                    $ents[$entity->season . $entity->year][] = $entity->toJsonObject(true, true);
                }

                $this->view->response = "";
                $this->view->response->data = $ents; //array((object) $ents);
                $this->view->response->status = true;
                $this->view->response->code = 200;
                $this->getResponse()->setHttpResponseCode(200);

            }
            else {
                if($mode=='tree') {
//                    //if we already have got the tree from the cache, we don't need to process anything here
//                    if (($this->cache->load(self::CACHE_TREE_VARIABLE)) === false) {
                        //$this->log->info($entities);
//                        var_dump($entities);
//                        die();
                        $ents = $this->formatTreetoJson($entities,$this->_server->getTreeFilter());
//                        $this->cache->save($ents, self::CACHE_TREE_VARIABLE);
//                    }
//                    $this->view->response->data = array((object) $entities);//$this->formatTreeJson($entities); //array((object) $entities);
                    $this->view->response = "";
                    $this->view->response->data = $ents; //array((object) $ents);
                    $this->view->response->status = true;
                    $this->view->response->code = 200;
                    $this->getResponse()->setHttpResponseCode(200);
                } else {
                    foreach ($entities as $entity) {
                        $ents[] = $entity->toJsonObject(true, true);
                    }
                    $this->view->response = "";
                    $this->view->response->data = $ents; //array((object) $ents);
                    $this->view->response->status = true;
                    $this->view->response->code = 200;
                    $this->getResponse()->setHttpResponseCode(200);
                }
            }
        } else {
            $this->respondError('no repo');
        }
        $this->log->info(get_class($this) . '::indexAction(' . $this->method . ')');
    }

    public function getAction() {

        $entityName = $this->_server->getActiveNamespace();


//        $this->respondError('no');
//        return;
        if ($this->_repo) {
            $filtered = $this->_server->getRequestParams();
            $this->log->info($filtered);
            $this->view->response = "";
            $mode = $this->_server->getRenderMode();
            $this->log->info(get_class($this) . '::getAction("' . $mode . '")');
            if ($mode == 'dir') {

                $entity = $this->_server->getFilteredEntityWithChildrenInfo(true);
                $this->view->response->data = $entity;

            } elseif ($mode == 'tree') {
//                unset($this->view->response);
                $this->view->response->data = $this->toJsTreeObject(array($this->_server->getFilteredEntity(false, true)), $this->_server->getTreeFilter());
            } else {
                $this->view->response->data = $this->_server->getFilteredEntity();
                $this->view->response->status = true;
                $this->view->response->code = 200;
                $this->getResponse()->setHttpResponseCode(200);
            }
        } else {
            $this->respondError();
        }
        $this->log->info(get_class($this) . '::getAction(' . $this->method . ')');
    }

    public function postAction() {
//        $this->respondError('no');
//        return;


        if($this->_repo) {
            $entNamespace = $this->_server->getActiveNamespace();
            $entity = new $entNamespace();
            #TODO Mapper -> map linkages
//            $postData = (isset($_POST['entityform'])===true)?$_POST['entityform']:$_POST;


            $rawdata = file_get_contents("php://input");

            $filtered = $this->_server->getRequestParams();
            switch ($filtered['format']) {
                case "json":
                    $this->log->info('STARTING ====== json');
                    $rawdata = json_decode($rawdata);
//                    $this->log->info($rawdata);
//
                    $postData = array();
                    foreach ($rawdata as $key => $val) {
                        $postData[$key] = $val;
                    }

                    break;
                default :
                    $postData = (isset($_POST['entityform']) === true) ? $_POST['entityform'] : $_POST;
                    break;
            }



            $filtered = $this->handleAssocciations($postData, $entity);//mapAssocciations($postData,$entity);
//            $this->log->info($filtered);
            $entity->post($filtered);


            if (!isset($entity)) {
                $this->respondError('Not Found', 404);
                return;
            }
            try {

                $this->em->persist($entity);
                $this->em->flush();
            } catch (Exception $e) {
                $this->respondError($e->getMessage(), 500);
                return;
            }

            //delete the cache tree variable, since it could have been mofidied
            $this->cache->remove(self::CACHE_TREE_VARIABLE);
            $this->view->response = "";
            $this->view->response->data = $entity->toJsonObject(true, true);
            $this->view->response->status = true;
            $this->view->response->code = 200;
            $this->getResponse()->setHttpResponseCode(200);
        } else {
            $this->respondError();
        }
        $this->log->info(get_class($this) . '::postAction(' . $this->method . ')');
    }

    public function putAction() {


        $mode = $this->_server->getRenderMode();
            #Handle index actions with get params.



//        $this->respondError('no');
//        return;
        if ($this->_repo) {
            $filtered = $this->_server->getRequestParams();

            $id = $filtered['id'];
            $entity = $this->_repo->findOneById($id);


//            $postData = (isset($_POST['entityform'])===true)?$_POST['entityform']:$_POST;
            #Zend Hack to get PUT data
            $dataobject = array();
            $rawdata = file_get_contents("php://input");
            $entNamespace = $this->_server->getActiveNamespace();

            if($mode=='tree') {
                parse_str($rawdata, $dataobject);

                if (isset($dataobject['parent'])) {
                    $parent = $this->em->getRepository($entNamespace)->findOneById($dataobject['parent']);
                    $dataobject['parent'] = $parent;
                }

                if (isset($dataobject['id_previous']) and $dataobject['id_previous'] != null) {
                    $previous = $this->_repo->findOneById($dataobject['id_previous']);
                }

                if (isset($dataobject['id_next']) and $dataobject['id_next'] != null) {
                    $next = $this->_repo->findOneById($dataobject['id_next']);
                }

                $entity->put($dataobject);
            }
            else {
                switch ($filtered['format']) {
                    case "json":


                        $this->log->info('STARTING ====== json');
                        $rawdata = json_decode($rawdata);
                        $this->log->info($rawdata);

                        if (is_object($rawdata)) {
                            $rawdata = get_object_vars($rawdata);
                        }

                        foreach ($rawdata as $key => $val) {
    //                        if ($key == 'parent')
    //                        {
    //                            $val = (int)$val;
    //                            $parent = $this->em->getRepository($entNamespace)->findOneById($val);
    //                            $dataobject[$key] = $parent;
    //                            continue;
    //                        }
                            $dataobject[$key] = $val;
                        }

    //
                        if($entity->type == 'video'){
                            $dataobject['link'] = $entity->link;
                        }

                        $dataobject['parent'] = NULL;
                        //$dataobject['metadata'] = NULL;
    //                   $dataobject['metadata'] = (array) $dataobject['metadata'];


                        $dataobject['tags'] = NULL;
                        $dataobject['children'] = NULL;
                        $dataobject['categories'] = NULL;

                        $dataobject['metadata']->autoplayjs = null;
                        $dataobject['metadata']->statejs = null;
                        $dataobject['metadata']->orientationjs = null;
                        $dataobject['metadata']->newsletterjs = null;

                        $dataobject = $this->handleAssocciations($dataobject, $entity);

                        $this->log->info('=trrated the objects');

                        $entity->put($dataobject);

                        $this->log->info('ENDING ==== json');

                        break;
                    default :

                        $this->log->info('STARTING ====== query');

                        $postData = "";
                        parse_str(rawurldecode($rawdata), $postData);
                        $filtered = $this->mapAssocciations($postData);
                        $entity->put($filtered);
                        $this->log->info($filtered);
                        $this->log->info('ENDING ====== query');

                        break;
                }
            }


            if (!isset($entity)) {
                $this->respondError('Not Found', 404);
                return;
            }
            try {

                //if it's a nested tree we'll have to keep the node order
                if ($mode == 'tree') {
                    if (isset($previous)) {
                        $this->em->getRepository($entNamespace)->persistAsNextSiblingOf($entity,$previous);
                    }
                    elseif(isset($next)) {
                        $this->em->getRepository($entNamespace)->persistAsPrevSiblingOf($entity,$next);
                    }
                    else {
                        $this->em->persist($entity);
                    }
                }
                else {
                    $this->em->persist($entity);
                }

                $this->em->flush();
                $this->em->refresh($entity);
            } catch (Exception $e) {
                $this->respondError($e->getMessage(), 500);
                return;
            }

            //delete the cache tree variable, since it could have been mofidied
            $this->cache->remove(self::CACHE_TREE_VARIABLE);
            $this->view->response = "";
            $this->view->response->data = $entity->toJsonObject(true, true);
            $this->view->response->status = true;
            $this->view->response->code = 200;
            $this->getResponse()->setHttpResponseCode(200);
        } else {
            $this->respondError();
        }
        $this->log->info(get_class($this) . '::putAction(' . $this->method . ')');
    }

    public function deleteAction() {
//        $this->respondError('no');
//        return;
        if ($this->_repo) {
            $filtered = $this->_server->getRequestParams();
            $id = $filtered['id'];

            //multiple deletion
            if (!is_numeric($id) and $id == 'multiple_ids') {
                if (isset($filtered['ids'])) {
                    $array_ids = $filtered['ids'];

                    foreach ($array_ids as $array_id) {

                        $entity = $this->_repo->findOneById($array_id);
                        $entity_title = $entity->title;

                        if ($entity_title == null) {
                            $entity_title = 'Not set';
                        }

                        if (empty($entity)) {
                            $this->messageQueue->addMessage("The item you are trying to delete doesn't exist.");
                        }
                        try {

                            $this->em->remove($entity);
                            $this->em->flush();
                            $this->messageQueue->addMessage("You have successfully deleted the item '$entity_title'.");
                        } catch (\Exception $e) {
                            $this->messageQueue->addMessage("An error ocurred while trying to delete the item '$entity_title'. Please check that the item is not related with other existing items.");
                        }
                    }
                }

                $this->view->response = "";
                $this->view->response->status = true;
                $this->view->response->code = 200;
                $this->getResponse()->setHttpResponseCode(200);
                $this->view->messages = $this->messageQueue->getMessages();
                return;
            }


            //single deletion
            $entity = $this->_repo->findOneById($id);
            if (empty($entity)) {
                $this->respondError('Not Found', 404);
                return;
            }
            try {
                $this->em->remove($entity);
                $this->em->flush();
            } catch (Exception $e) {
                $this->respondError($e->getMessage(), 500);
                return;
            }


            //delete the cache tree variable, since it could have been mofidied
            $this->cache->remove(self::CACHE_TREE_VARIABLE);
            $session = new Zend_Session_Namespace('labelux-cms');

            $this->messageQueue->addMessage("You have successfully deleted the item '$entity_title'.");

            $this->view->response = "";
            $this->view->response->data = $entity;
            $this->view->response->status = true;


            $this->view->response->code = 200;
            $this->getResponse()->setHttpResponseCode(200);
            $this->view->messages = $this->messageQueue->getMessages();
        } else {
            $this->respondError();
        }
        $this->log->info(get_class($this) . '::deleteAction(' . $this->method . ')');
    }

    public function headAction() {
        $params = $this->_getAllParams();
        $this->getResponse()->setBody(null);
        $this->view->clearVars();
        $this->getResponse()->setHeader('Last-Modified', date(DATE_RFC822));
        if (isset($this->_repo) === false) {
            $this->respondError('no repo');
        }
        $this->log->info(get_class($this) . '::headAction(' . $this->method . ')');
    }

    public function optionAction() {
        $this->getResponse()->setBody(null);
        #TODO Hook ACL into this method.
        $this->getResponse()->setHeader('Allow', 'OPTIONS, HEAD, INDEX, GET, POST, PUT, DELETE');
        $this->view->clearVars();
        if (isset($this->_repo) === false) {
            $this->respondError('no repo');
        }
        $this->log->info(get_class($this) . '::optionAction(' . $this->method . ')');
    }

    protected function formatTreetoJson($entities, $restrict = "dir") {
        $this->log->info(get_class($this) . '::formatTreetoJson(' . $restrict . ')');
        $this->log->info($restrict);
        $this->log->info(count($entities));
//        $this->log->info($entities);
        if(is_array($restrict)) {
            $restricted = $restrict['type']['filter'];
        } else {
            $restricted = $restrict;
        }
        $this->log->info(get_class($this) . '::formatTreetoJson(' . $restricted . ')');
        $jsonReturn = array();
        //if(count($entities) > 0) {
            foreach ($entities as $ent) {
                if($ent['type'] == $restricted) {
                    $this->log->info($ent['type']." - ".$ent['title']);
                    $jsonReturn[] = (object) array(
                        "data" => $ent['title'],
                        "attr" => (object) array(
                            "id" => "node-" . $ent['id'],
                            "data-type" => $ent['mimetype'],
//                            "children_ids" => implode(',',$children_ids)
                        ),
                        "children" => $this->formatTreetoJson($ent['children'], $restricted)
                    );
                }
            }
        //}
        $this->log->info($jsonReturn);
        return $jsonReturn;
    }
//    /**
//     * @method formatTreeJson
//     * @param array $entities
//     * @return array
//     */
//    protected function formatTreeJson($entities,$restrict = "dir") {
//        $jsonReturn = array();
//        foreach ($entities as $ent) {
//            if($ent->type == $restrict) {
//                $this->log->info($ent->title);
//                $children_ids = array();
//                foreach ($ent->children as $child) {
//                    $children_ids[] = $child->id;
//                }
//                $jsonReturn[] = (object) array(
//                    "data" => $ent->title,
//                    "attr" => (object) array(
//                        "id" => "node-" . $ent->id,
//                        "data-type" => $ent->mimetype,
//                        "children_ids" => implode(',',$children_ids)
//                    ),
//                    //"children" => $this->formatTreeJson($ent->children, $restrict)
//                );
//                unset($children_ids);
//            }
//        }
//        return $jsonReturn;
//    }

    protected function toJsTreeObject($entities, $restrictions = false) {
        $jsonReturn = array();
        $this->log->info(get_class($this) . "::toJsTreeObject('" . count($entities) . "')");
        foreach ($entities as $ent) {
            $this->log->info($ent->title);
            $show = true;
            if ($restrictions != false) {
                #Restrict things.
                foreach ($restrictions as $restriction) {
                    $prop = $restriction['property'];
                    /*                     $this->log->info("".$prop.": ".$ent->$prop." = ".$restriction['filter']); */
                    if ($ent->$prop != $restriction['filter']) {
                        $show = false;
                    }
                }
            }
            if ($show) {

                $children_ids = $this->getRecursiveChildrenIds($ent);


                $jsonReturn[] = (object) array(
                            "data" => $ent->title,
                            "attr" => (object) array(
                                "id" => "node-" . $ent->id,
                                "data-type" => $ent->mimetype,
                                "children_ids" => implode(',',$children_ids)
                            ),
                            "children" => $this->toJsTreeObject($ent->children, $restrictions)
                );
            }
        }
        return $jsonReturn;
    }

    protected function getRecursiveChildrenIds($entity) {

        foreach ($entity->children as $child) {
            $children_ids[] = $child->id;
            $this->getRecursiveChildrenIds($child);
        }

       /* foreach ($entity->children as $child) {

            $array_ids = $this->getRecursiveChildrenIds($child);

            foreach ($array_ids as $elem) {
                $children_ids[] = $elem;
            }
        }
        */

        return $children_ids;
    }



    /**
     * @method parseRestRequest
     * @param array $params
     * @return array
     */
    protected function mapAssocciations($postdata, $EntityItem) {
        $meta = $this->em->getClassMetadata(get_class($EntityItem));
        $this->log->info('COunting meta ==================================== ');
        foreach ($meta->associationMappings as $association) {

            if (isset($postdata[$association['fieldName']]) === true) {
                $this->log->info('COunting meta ====== ' . $association['fieldName']);
                if (is_numeric($postdata[$association['fieldName']])) {
                    #Get findOneById
                    $refd = $this->em->getRepository($association['targetEntity'])->findOneById($postdata[$association['fieldName']]);
                    if (($association['fieldName'] == 'categories') || (DoctrineClassMetaInfo::INHERITANCE_TYPE_JOINED != $association['type']) && (DoctrineClassMetaInfo::INHERITANCE_TYPE_NONE != $association['type'])) {
                        $refd = array($refd);
                    }
                    $ref = $refd;
                } else if (is_array($postdata[$association['fieldName']])) {
                    #Can't map data? clear it to prevent errors.
                    $ref = null;
                } else if (strpos($postdata[$association['fieldName']], ',') !== false) {
                    #Get find(array('id',explode(',',$postdata[$association['fieldName'])))
                    $ids = explode(',', $postdata[$association['fieldName']]);
                    $ref = $this->em->getRepository($association['targetEntity'])->findBy(array('id' => $ids));
                } else {
                    #Can't map data, so clear data.
                    $ref = null;
                }
                $postdata[$association['fieldName']] = $ref;
            }
        }

        return $postdata;
    }

    protected function handleAssocciations($postdata, $entityitem) {

        if (isset($postdata['parent']) === true) {
            $entityitem->parent = $this->_repo->findOneById($postdata['parent']);
        }
        if (isset($postdata['cdn']) === true) {
            $entityitem->cdn = (boolean) $postdata['cdn'];
        }

        $meta = $this->em->getClassMetadata(get_class($entityitem));
        $this->log->info('COunting meta ==================================== ');

        foreach ($meta->associationMappings as $association) {

            if (isset($postdata[$association['fieldName']]) === true) {
                $this->log->info('COunting meta ====== ' . $association['fieldName']);



                if (is_numeric($postdata[$association['fieldName']])) {
                    #Get findOneById
                    $refd = $this->em->getRepository($association['targetEntity'])->findOneById($postdata[$association['fieldName']]);

//                    if (($association['fieldName'] == 'categories') || (DoctrineClassMetaInfo::INHERITANCE_TYPE_JOINED != $association['type']) && (DoctrineClassMetaInfo::INHERITANCE_TYPE_NONE != $association['type'])) {
//                        $refd = array($refd);
//                    }
                    $ref = $refd;
                } else if (is_array($postdata[$association['fieldName']])) {

                } else if (is_object($postdata[$association['fieldName']])) {

                    $items = $entityitem->$association['fieldName'];
                    $ref = array();
                    $objectArray = (array) get_object_vars((object) $postdata[$association['fieldName']]);

                    $skip_keys = array_keys($this->config['settings']['application']['asset']['manager']['size']);
                    array_push($skip_keys, 'autoplayjs', 'statejs', 'orientationjs', 'newsletterjs');

                    if (count($items) > 0) {
                        foreach ($items as $item_key=>$item) {

                           if(!in_array($item->title, $skip_keys)){

                                if (isset($association['fieldName'][$item->title])) {

                                    $item->content = $objectArray[$item->title];
                                    //$item->content = ($objectArray[$item->title]) ? $objectArray[$item->title] : $item->content;

                                    $this->em->persist($item);
                                    array_push($ref, $item);
                                    unset($objectArray[$item->title]);
                                }
                           }
                           else { //we don't want to remove those elements we already have on DB
                                array_push($ref, $item);
                           }
                        }
                    }

                    foreach ($objectArray as $key => $value) {

                        if(!in_array($key, $skip_keys)){
                            $item = new $association['targetEntity']();
                            $this->log->info('creating ====== ' . get_class($item) . ' - ' . $key . ' - ' . $value);
                            $item->title = $key;
                            $item->content = $value;
                            $item->type = get_class($entityitem) . ucwords($association['fieldName']);
                            $this->em->persist($item);
                            $this->em->flush();
                            array_push($ref, $item);
                        }
                    }

                    $entityitem->$association['fieldName'] = $ref;

                    $this->em->persist($entityitem);
                    $this->em->flush();
                    $postdata[$association['fieldName']] = $ref;

                } else if (strpos($postdata[$association['fieldName']], ',') !== false) {
                    #Get find(array('id',explode(',',$postdata[$association['fieldName'])))
                    $ids = explode(',', $postdata[$association['fieldName']]);
                    $ref = $this->em->getRepository($association['targetEntity'])->findBy(array('id' => $ids));
                } else {
                    #Can't map data, so clear data.
                    $this->log->info("733 Can't map data, so clear data");
                    $ref = null;
                }

                $postdata[$association['fieldName']] = $ref;
            }
        }


        return $postdata;
    }

}
