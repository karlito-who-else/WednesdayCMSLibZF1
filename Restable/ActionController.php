<?php
namespace Wednesday\Restable;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Annotations\AnnotationReader,
    \Zend_Rest_Controller as ZendRestController,
    \Zend_Session_Namespace,
    \Zend_Auth;

/**
 * RestActionController - The rest error controller class
 * Description of AdminAction
 *
 * @author mrhelly
 * @version    $Id: 1.7.4 RC1, jameshelly $
 */
class ActionController extends ZendRestController
{
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
    const ARTICLEITEMS     = "Application\Entities\ArticleItems";
    const HCARDS 			= "Application\Entities\Hcards";
    const ADDRESSES 		= "Application\Entities\Addresses";
    const HOMEPAGEITEMS 	= "Application\Entities\HomepageItems";
    const HOMEPAGE 			= "Application\Entities\Homepage";
    const BRANDS 			= "Application\Entities\Brands";
    const PRODUCTS 			= "Application\Entities\Products";
    const LOOKS 			= "Application\Entities\Looks";
    const GROUPEDLOOKS      = "Application\Entities\GroupedLooks";
    const GRIDS 			= "Application\Entities\Grids";
    const ACLUSERROLES 		= "Application\Entities\AclUserRoles";
    const COLLECTIONS 		= "Application\Entities\Collections";
    const WIDGETS           = "Application\Entities\Widgets";
    const WIDGETSORDERED    = "Application\Entities\WidgetsOrdered";
    const CATEGORIES 		= "Wednesday\Models\Categories";
    const TAGS 				= "Wednesday\Models\Tags";
    const SETTINGS 			= "Wednesday\Models\Settings";
    const METADATA 			= "Wednesday\Models\MetaData";
    const MEDIABROWSER 		= "Wednesday\Restable\Action\MediaBroswer";
    const AUTH 				= "Wednesday\Restable\Action\Auth";
    const SEARCH 			= "Wednesday\Restable\Action\Search";
    const MEDIA_VARIATIONS 	= "Wednesday\Restable\Action\Variations";
    const RESOURCES_VIDEOS  = "Wednesday\Restable\Action\Videos";
    const GALLERY_ACTION    = "Wednesday\Restable\Action\Galleries";
    const GEOLOCATION 		= "Application\Entities\GeoLocations";

    const CACHE_TREE_VARIABLE = 'browsetree';

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
     *
     * @var type
     */
    protected $messageQueue;

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
    	#Get Cache
        $this->cache = $bootstrap->getResource('Cachemanager')->getCache('file');
        #Get Locale
        $this->locale = $bootstrap->getResource('Locale');
        #Get Resources
        $this->session = new Zend_Session_Namespace('wedcms');
        $this->session->setExpirationSeconds(floor(60*60*6));
        if(isset($this->session->admin_locale)){
            $this->locale->setLocale($this->session->admin_locale);
        }
        #Get Message Queue
    	$this->messageQueue = $this->_helper->getHelper('FlashMessenger');
		$this->view->messages = $this->messageQueue->getMessages();

        #Init REST Server
        $request = $this->getRequest();
        $this->_server = new Server($request);
        $aliases = array(
            'resources' 	=> self::RESOURCES,
            'variations' 	=> self::VARIATIONS,
            'pages' 		=> self::PAGES,
            'menuitems'     => self::USERMENUITEMS,
            'templates' 	=> self::TEMPLATES,
            'tvars' 		=> self::TVARS,
            'tvarconts' 	=> self::TVARCONTENTS,
            'users' 		=> self::USERS,
            'blog'          => self::BLOGITEMS,
            'articles'      => self::ARTICLEITEMS,
            'discounts' 	=> self::DISCOUNTS,
            'vcard' 		=> self::HCARDS,
            'categories' 	=> self::CATEGORIES,
            'tags'          => self::TAGS,
            'settings' 		=> self::SETTINGS,
            'addresses' 	=> self::ADDRESSES,
            'metadata' 		=> self::METADATA,
            'homepageitems' => self::HOMEPAGEITEMS,
            'homepage' 		=> self::HOMEPAGE,
            'brands'		=> self::BRANDS,
            'products'		=> self::PRODUCTS,
            'looks'			=> self::LOOKS,
            'groupedlooks'  => self::GROUPEDLOOKS,
            'grids'			=> self::GRIDS,
            'acluserroles'	=> self::ACLUSERROLES,
            'collections'   => self::COLLECTIONS,
            'widgets'       => self::WIDGETS,
            'widgetsordered'=> self::WIDGETSORDERED,
            'geolocations'       => self::GEOLOCATION

        );
        $actions = array(
            'auth'              => self::AUTH,
            'mediabrowser'      => self::MEDIABROWSER,
            'mediavariations'   => self::MEDIA_VARIATIONS,
            'search'            => self::SEARCH,
            'videos'            => self::RESOURCES_VIDEOS,
            'galleries'         => self::GALLERY_ACTION
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
    	if($this->_server->getActionType()=='action') {
            $this->method = 'method';
        }
        #Authentication
//        $this->_server->setPassword($password)->setUsername($username);
        #Security Options ::
        #Get Acl Object
        #TODO Set auth request inside server to allow you to use auth
        #Get Zend Auth.

        $this->getRequest()->setActionName($this->method);
        $this->view->response = "";
        $this->log->info(get_class($this) . '::init(' . $this->method . ')');
        #Allow xml to use view rendering.
        if ($this->getRequest()->getParam('format') == 'xml') {
            $this->_helper->viewRenderer->setNoRender(false);
        }

    }

    /**
     * Store bookstrap
     * @see Controller/Zend_Controller_Action::preDispatch()
     */
    public function preDispatch() {
        $this->view->clearVars();
        $this->view->response = "";
        $this->log->info(get_class($this).'::preDispatch('.$this->method.')');
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
        $this->log->info(get_class($this).'::postDispatch('.$this->method.')');
    }

    /**
     *
     * @param type $message
     * @param type $code
     */
    public function respondError($message='Internal Server Error', $code=500) {
        $this->view->clearVars();
        $this->getResponse()->setHttpResponseCode($code);
        $this->view->response = (object) array( 'status' => false, 'code' => $code, 'message' => $message, 'method'=>$this->method);
        $this->log->info(get_class($this).'::respondError('.$this->method.')');
    }

    /**
     *
     */
    public function methodAction() {
        if(isset($this->_repo)===false) {
            $this->respondError('no repo');
        } else {
            $api = $this->_server->getRequestParams();
            $service = $api['id'];
            $this->log->info(get_class($this).'::methodAction('.$service."::".$this->method.')');
            #Run methods
            if(is_numeric($service)) {
                $service = $api['action'];
            }
//            var_dump($api);
//            die($service);
            $this->view->response = $this->_repo->$service($this->_server->getRequestParams());
        }
        $this->log->info(get_class($this).'::methodAction('.$this->method.')');
     }

    /**
     *
     */
    public function indexAction() {
//        $params = $this->_server->getRequestParams();
        $this->log->info(get_class($this)."indexAction('".$this->method."')");
        if ($this->_repo) {
            $mode = $this->_server->getRenderMode();
            #Handle index actions with get params.
            if($mode=='tree') {
//                if(($ents = $this->cache->load(self::CACHE_TREE_VARIABLE)) === false) {
                    $entities = $this->_server->getFilteredEntities(false, true);
//                }
            } else {
                $entities = $this->_server->getFilteredEntities(false);
            }
//            if (empty($entities) && $this->cache->load(self::CACHE_TREE_VARIABLE) === false) {
            if (empty($entities)) {
                $this->respondError('Not Found', 404);
                return;
            }
            $ents = array();
            $this->log->info(get_class($this).'::indexAction("'.$mode.'")');
            $entityName = $this->_server->getActiveNamespace();
            //if the entity is collections, we return them grouped by season/year
            if($entityName == self::COLLECTIONS) {
                foreach ($entities as $entity) {
                    $entity->imgsrc = $entity->gallery->featured->link;
                    $ents[$entity->season . $entity->year][] = $entity->toJsonObject(true, true);
                }
                $this->view->response = "";
                $this->view->response->data = $ents;
                $this->view->response->status = true;
                $this->view->response->code = 200;
                $this->getResponse()->setHttpResponseCode($this->view->response->code);
            } else {

                if($mode=='tree') {
//                    if (($this->cache->load(self::CACHE_TREE_VARIABLE)) === false) {
                        $ents = $this->toJsTreeObject($entities, $this->_server->getTreeFilter());
//                        $this->cache->save($ents, self::CACHE_TREE_VARIABLE);
//                    }
                    $this->view->response->data = (empty($ents)===false)?$ents:array('false');
                    $this->view->response->status = true;
                    $this->view->response->code = 301;
                } else {
                    foreach ($entities as $entity) {
                        $ents[] = $entity->toJsonObject(true,true);
                    }
                    $this->view->response = "";
                    $this->view->response->data = $ents;
                    $this->view->response->status = true;
                    $this->view->response->code = 301;
                }
             }
        } else {
            $this->respondError('no repo');
        }
        $this->log->info(get_class($this).'::indexAction('.$this->method.')');
    }

    public function getAction() {
//        $this->respondError('no');
//        return;
        if ($this->_repo) {
//            $filtered = $this->_server->getRequestParams();
//            $this->log->info(print_r($filtered,true));
            $this->view->response = "";
            $mode = $this->_server->getRenderMode();
            $this->log->info(get_class($this).'::getAction("'.$mode.'")');
            if($mode=='dir') {
                $this->view->response->data = $this->_server->getFilteredEntityWithChildrenInfo(true);
            } else if($mode=='tree') {
//                unset($this->view->response);
                $this->view->response->data = $this->toJsTreeObject(array($this->_server->getFilteredEntity(false, true)),$this->_server->getTreeFilter());
            } else {
                $this->view->response->data = $this->_server->getFilteredEntity();
                $this->view->response->status = true;
                $this->view->response->code = 301;
            }
        } else {
            $this->respondError();
        }
        if(isset($this->view->response->data)) {
            $this->view->response->status = true;
            $this->view->response->code = 301;                    
        } else {
            $this->respondError('Not Found', 404);
        }
        $this->log->debug(get_class($this).'::getAction('.$this->method.')');
    }

    public function postAction() {
        if($this->_repo) {
            $entNamespace = $this->_server->getActiveNamespace();
            $entity = new $entNamespace();
//            if (method_exists($entity,'setTranslatableLocale')) {
//                $entity->setTranslatableLocale($this->locale->__toString());
//                $this->em->refresh($entity);
//            }
            #Zend Hack to get PUT data
            $rawdata = file_get_contents("php://input");
            $postData = "";
            if(substr($rawdata, 0, 1)=="{") {
                $this->log->info('json');
                $postData = json_decode($rawdata,true);
            } else {
                $this->log->info('rawurl');
                parse_str(rawurldecode($rawdata), $postData);
            }
            $this->log->info($postData);
			$dataobject = array();
            $parent = false;
			foreach ($postData as $key => $val) {
				if ($key == 'parent') {
					$val = (int)$val;
					$parent = $this->em->getRepository($entNamespace)->findOneById($val);
                    $this->log->info($parent->id." = ".$val);
					$dataobject[$key] = $parent;
					continue;
				}
				$dataobject[$key] = $val;
			}
            $dataobject = $this->mapAssocciations($dataobject,$entity);
            if($parent != false) {
                $dataobject['parent'] = $parent;
            }
//            unset ($dataobject['products']);
//            $dataobject['products'] = array($dataobject['products']);
			$entity->createAction($dataobject);

            if (!isset($entity)) {
                $this->respondError('Not Found', 404);
                return;
            }
            try{
                $this->em->persist($entity);
                $this->em->flush();
            } catch (\Exception $e){
//                $this->log->info($e->getTrace());
                $this->respondError($e->getMessage(), 500);
                return;
            }
            $this->view->response = "";
            $this->view->response->data = $entity->toJsonObject(true,true);
            $this->view->response->status = true;
            $this->view->response->code = 301;
        } else {
            $this->respondError();
        }
        $this->log->info(get_class($this).'::postAction('.$this->method.')');
    }

    public function putAction() {
        if($this->_repo) {
            $filtered = $this->_server->getRequestParams();
            $id = (int) $filtered['id'];
            //$entity = $this->_repo->findOneById($id);
            $entity = $this->em->getRepository($this->_server->getActiveNamespace())->findOneById($id);
            if (method_exists($entity,'setTranslatableLocale')) {
                $entity->setTranslatableLocale($this->locale->__toString());
                $this->em->refresh($entity);
            }
            #Zend Hack to get PUT data
            $rawdata = file_get_contents("php://input");
            $postData = "";
//            $this->log->info(substr($rawdata, 0, 1));
            if(substr($rawdata, 0, 1)=="{") {
                $this->log->info('json');
                $postData = json_decode($rawdata,true);
            } else {
                $this->log->info('rawurl');
                parse_str(rawurldecode($rawdata), $postData);
            }
            $this->log->info($postData);
            $filtered = $this->mapAssocciations($postData,$entity);
            //TODO Handle tree stuff.
            $entity->put($filtered);    
            if (!isset($entity)) {
                $this->respondError('Not Found', 404);
                return;
            }
            $this->log->info(' '.$entity->id.' ');
            try {
                $this->em->persist($entity);
                $repo = $this->em->getRepository($this->_server->getActiveNamespace());
//                if (method_exists($repo,'persistAsNextSiblingOf')) {
                    $this->log->info('method_exists:persistAsNextSiblingOf');
                    if(isset($postData['id_next'])&&($postData['id_next']>0)) {
                        $next = $repo->findOneById($postData['id_next']);
                        $repo->persistAsNextSiblingOf($next,$entity);
                    } else if(isset($postData['id_previous'])&&($postData['id_previous']>0)) {
                        $prev= $repo->findOneById($postData['id_previous']);
                        $repo->persistAsNextSiblingOf($entity,$prev);
                    }
//                }
                $this->em->flush();
            } catch (\Exception $e){
                $this->respondError($e->getMessage(), 500);
                return;
            }
            $this->log->info(' '.$entity->id.' ');
            $this->view->response = "";
            $this->view->response->data = $entity->toJsonObject(true,true);
            $this->view->response->status = true;
            $this->view->response->code = 301;
        } else {
            $this->respondError();
        }
        $this->log->info(get_class($this).'::putAction('.$this->method.')');
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
//            			$entity = $this->_repo->findOneById($array_id);
                        $entity = $this->em->getRepository($this->_server->getActiveNamespace())->findOneById($array_id);
						$entity_title = $entity->title;

						if ($entity_title == null) {
							$entity_title = 'Not set';
						}
           				if (empty($entity)) {
                			$this->messageQueue->addMessage("The item you are trying to delete doesn't exist.");
            			}
            			try{
                			$this->em->remove($entity);
                			$this->em->flush();
                			$this->messageQueue->addMessage("You have successfully deleted the item '$entity_title'.");

            			}
            			catch (\Exception $e){
                			$this->messageQueue->addMessage("An error ocurred while trying to delete the item '$entity_title'. Please check that the item is not related with other existing items.");
            			}
            		}
            	}

            	$this->view->response = "";
				$this->view->response->status = true;
	            $this->view->response->code = 301;
            	$this->view->messages = $this->messageQueue->getMessages();
            	return;
            }

            //single deletion
//            $entity = $this->_repo->findOneById($id);
            $entity = $this->em->getRepository($this->_server->getActiveNamespace())->findOneById($id);
            $entity_title = $entity->title;
			if ($entity_title == null) {
				$entity_title = 'Not set';
			}

            if (empty($entity)) {
                $this->messageQueue->addMessage("The item you are trying to delete doesn't exist.");
                $this->respondError('Not Found', 404);
                return;
            }
            try{
                $this->em->remove($entity);
                $this->em->flush();
            }
            catch (\Exception $e){
                $this->messageQueue->addMessage("An error ocurred while trying to delete the item '$entity_title'. Please check that the item is not related with other existing items.");
                $this->respondError($e->getMessage(), 500);
                return;
            }

            $session = new Zend_Session_Namespace('labelux-cms');

            $this->messageQueue->addMessage("You have successfully deleted the item '$entity_title'.");
            $this->view->response = "";
            $this->view->response->data = $entity;
            //the js will use redirect_page in case we have deleted a blog entity to redirect to the previous page (list page)
            $this->view->response->redirect_page = $session->redirect;
            $this->view->response->status = true;
            $this->view->response->code = 301;
            $this->view->messages = $this->messageQueue->getMessages();

        } else {
            $this->messageQueue->addMessage("An error ocurred while trying to delete the item.");
            $this->respondError();
        }
        $this->log->info(get_class($this).'::deleteAction('.$this->method.')');
    }

    public function headAction() {
    	$params = $this->_getAllParams();
        $this->getResponse()->setBody(null);
        $this->view->clearVars();
        $this->getResponse()->setHeader('Last-Modified', date(DATE_RFC822));
        if(isset($this->_repo)===false) {
            $this->respondError('no repo');
        }
        $this->log->info(get_class($this).'::headAction('.$this->method.')');
    }

    public function optionAction() {
        $this->getResponse()->setBody(null);
        #TODO Hook ACL into this method.
        $this->getResponse()->setHeader('Allow', 'OPTIONS, HEAD, INDEX, GET, POST, PUT, DELETE');
        $this->view->clearVars();
        if(isset($this->_repo)===false) {
            $this->respondError('no repo');
        }
        $this->log->info(get_class($this).'::optionAction('.$this->method.')');
     }

    /**
     * @method parseRestRequest
     * @param array $params
     * @return array
     */
    protected function mapAssocciations($postdata,$entity) {
        $entityName = $this->_server->getActiveNamespace();
        $meta = $this->em->getClassMetadata($entityName);
//        $this->log->err($postdata);
//        $this->log->info($meta->associationMappings);
        foreach($meta->associationMappings as $association){
            $ref = null;
            if(isset($postdata[$association['fieldName']])===true) {
                $this->log->info(":: ".$association['fieldName']." => ".$association['targetEntity']);
                $this->log->err($association);
                //DoctrineClassMetaInfo::ONE_TO_ONE
                //DoctrineClassMetaInfo::ONE_TO_MANY
                //DoctrineClassMetaInfo::MANY_TO_ONE
                //DoctrineClassMetaInfo::MANY_TO_MANY 
                //$association['type']
                if($association['fieldName']=='metadata') {
                    #metadata requires kid gloves.
                    $this->log->err($postdata[$association['fieldName']]);
                    if(is_array($postdata[$association['fieldName']])) {
                        $ref=array();
                        foreach($postdata[$association['fieldName']] as $metaName => $data) {
                            #Numeric $metaName is just an ID list, don't update stuff.
                            if(!is_numeric($metaName)) {
                                $metadata = $entity->getMetadata($metaName);
                                if(!$metadata) {
                                    $mdns = self::METADATA;
                                    $metadata = new $mdns();
                                    $metadata->title = $metaName;
                                    $metadata->type = 'Application\Entities\MediaResourcesMetadata';
                                }
                                if(is_array($data)) {
                                    foreach ($data as $key => $value) {
                                        if($value == true) {
                                            $content = $key;
                                        }
                                    }
                                } else {
                                    $content = $data;
                                }
                                $metadata->content = $content;
                                $this->em->persist($metadata);
                                array_push($ref, $metadata);
                            }
                        }
                        //Now get metadata from the ent.
                        foreach($entity->metadata as $metaorig) {
                            $exists = false;
                            foreach($ref as $metatest) {
                                if($metatest->id == $metaorig->id) {
                                    $exists = true;
                                }
                            }
                            if(!$exists){ 
                                array_push($ref, $metaorig);
                            }
                        }
                    } else {
                        $ref = null;
                    }
                } else {
                    $this->log->info($association['fieldName'].":".$postdata[$association['fieldName']]);
                    if(is_numeric($postdata[$association['fieldName']])) {
                        $this->log->info("is_numeric");
                        #Get findOneById
                        $ref = $this->em->getRepository($association['targetEntity'])->findOneById($postdata[$association['fieldName']]);
                        if($association['type']== DoctrineClassMetaInfo::ONE_TO_MANY || $association['type']== DoctrineClassMetaInfo::MANY_TO_MANY){
                            $ref = array($ref);
                        }
                    } else if((!is_array($postdata[$association['fieldName']]))&&(strpos($postdata[$association['fieldName']], ',')!==false)) {
                        $this->log->info("is_csv");
                        $this->log->info($postdata[$association['fieldName']]);
                        #Get find(array('id',explode(',',$postdata[$association['fieldName'])))
                        $ids = explode(',',$postdata[$association['fieldName']]);
                        $this->log->info($ids);
                        $resourceCollection = $this->em->getRepository($association['targetEntity'])->findBy(array('id'=> $ids));
                        $ref = $resourceCollection;
//                        $sortorder =0;
//                        $ref=array();
//                        foreach ($resourceCollection as $resource) {
//                            $sortorder++;
//                            $orderedMedia = new $association['targetEntity']();
//                            $this->log->info('putting image - '.$resource->title);
//                            $orderedMedia->resource = $resource;
//                            $orderedMedia->sortorder = $sortorder;
//                            $this->em->persist($orderedMedia);
//                            array_push($ref, $orderedMedia);
//                        }
                    } else if(is_array($postdata[$association['fieldName']])) {
                        //Assume further code can handle Array, otherwise we will have to do some hard coded hacking.
                        $this->log->info("is_array");
                        $this->log->info($postdata[$association['fieldName']]);
                        $ref = $postdata[$association['fieldName']];
                    } else {
                        $this->log->info("isnt_array,isnt_csv,isnt_numeric");
                        $ref = null;
                    }
                }
                $postdata[$association['fieldName']] = $ref;
            }
        }
        $filtered = $postdata;
        $this->log->debug($filtered['parent']);
        if(!is_numeric($filtered['parent'])) {
            unset($filtered['parent']);
        }
        $this->log->debug($filtered['parent']);
        return $filtered;
    }

    protected function toXml($entities) {
        return print_r($entities,true);
    }

    protected function toJsTreeObject($entities, $restrictions = false) {
        $jsonReturn = array();
        $this->log->info(get_class($this)."::toJsTreeObject('".count($entities)."')");
        foreach($entities as $enta) {
        	if(is_array($enta)) {
        		$ent = (object) $enta;
        	} else {
	        	$ent = $enta;
        	}
//            $this->log->info($ent->title);
            $show = true;
            if($restrictions != false) {
                #Restrict things.
                foreach($restrictions as $restriction) {
                    $prop = $restriction['property'];
/*                     $this->log->info("".$prop.": ".$ent->$prop." = ".$restriction['filter']); */
                    if($ent->$prop != $restriction['filter']){
                        $show = false;
                    }
                }
            }
            if($show) {
                $children_ids = array();
                foreach($ent->children as $child) {
                    $children_ids[] = $child->id;
                }
                $jsonReturn[] = (object) array(
                    "data" => $ent->title,
                    "attr" => (object) array(
                        "id"=>"node-".$ent->id,
                        "data-type" => $ent->mimetype,
                        "data-child-ids" => implode(',',$children_ids)
                    ),
                    "children" => $this->toJsTreeObject($ent->children, $restrictions)
                );
            }
        }
        return $jsonReturn;
    }
}
