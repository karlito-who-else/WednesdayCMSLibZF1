<?php

namespace Wednesday\Acl;

use \Zend_Controller_Request_Abstract as RequestAbstract,
    \Zend_Controller_Front as Front,
    \Zend_Auth as ZendAuth,
    \Zend_Acl as ZendAcl,
    \Zend_Acl_Role,
    \Zend_Acl_Resource,
    \Zend_Acl_Exception,
    \Zend_Session_Namespace,
    \Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping as ORM,
    Gedmo\Mapping\Annotation as Gedmo,
    Doctrine\Common\Collections\ArrayCollection,
    Wednesday\Restable\Entity as Restable,
    Wednesday\Restable\CoreItems as CoreRestItems,
    Wednesday\Restable\EntityAbstract as RestableItems;

/**
 * Description of AclAbstract
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
class WednesdayAcl {
    const BRANDS        = "Application\Entities\Brands";
    const ROLES         = "Application\Entities\AclRoles";
    const USERROLES     = "Application\Entities\AclUserRoles";
    const PERMISSIONS   = "Application\Entities\AclPermissions";
    const RESOURCES     = "Application\Entities\AclResources";
    const USERS         = "Application\Entities\Users";
    const ACLRULZ       = "Application\Entities\AclRules";
    const PAGES         = "Application\Entities\Pages";

    /**
     *
     * @var type
     */
    private static $aclcachekey = 'WedAcl';

    /**
     * Singleton instance
     *
     * @var Zend_Auth
     */
    protected static $_instance = null;

    /**
     * Persistent storage handler
     *
     * @var Zend_Cache_Core
     */
    protected $_storage = null;

    /**
     * Persistent storage handler
     *
     * @var Zend_Auth
     */
    protected $_auth = null;

    /**
     * Loaded user.
     *
     * @var Application\Entities\Users
     */
    protected $_user = null;

    /**
     *
     * @var boolean
     */
    public $denied = false;


    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    protected function __construct() {
       return $this;
    }

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone() {

    }

    /**
     * Returns an instance of Zend_Auth
     *
     * Singleton pattern implementation
     *
     * @return Zend_Auth Provides a fluent interface
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Returns the persistent storage handler
     *
     * Session storage is used by default unless a different storage adapter has been set.
     *
     * @return Zend_Cache_Core
     */
    public function getStorage() {
        if (null === $this->_storage) {
            $bootstrap = Front::getInstance()->getParam('bootstrap');
            $this->_storage = $bootstrap->getResource('Cachemanager')->getCache('file');
        }
        return $this->_storage;
    }

    /**
     * Sets the persistent storage handler
     *
     * @param  Zend_Cache_Core $storage
     * @return Zend_Auth Provides a fluent interface
     */
    public function setStorage(Zend_Cache_Core $storage) {
        $this->_storage = $storage;
        return $this;
    }

    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return boolean
     */
    public function hasAcl() {
        return $this->getStorage()->test(self::$aclcachekey);
    }

    /**
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    public function getAcl() {
        $storage = $this->getStorage();

        if (!$storage->test(self::$aclcachekey)) {
            $this->buildACL();
        }
        return $storage->load(self::$aclcachekey);
    }

    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return boolean
     */
    public function buildACL() {
        $em = $this->getEntityManager();

        #Build Resouces
        $aclresources = $em->getRepository(self::RESOURCES)->findAll();
        #Build Roles
        $acluserroles = $em->getRepository(self::USERROLES)->findAll();
        #Build Rules by applying allow/deny to Resources.
        $aclrules = $em->getRepository(self::ACLRULZ)->findAll();
        $aclroles = $em->getRepository(self::ROLES)->findAll();
        $users = $em->getRepository(self::USERS)->findAll();

        $acl = new ZendAcl();
        foreach ($aclroles as $role) {
            //Create the role object
            $aclroleObject = new Zend_Acl_Role($role->title);

            if ($role->parent) {
                $acl->addRole($aclroleObject, $role->parent->title);
            } else {
                $acl->addRole($aclroleObject);
            }
        }
        foreach ($acluserroles as $userrole) {
            //Create the role object
            $aclroleObject = new Zend_Acl_Role($userrole->name);
            //\Doctrine\Common\Util\Debug::dump($userrole);
            if ($userrole->aclroles) {
                $acl->addRole($aclroleObject, $userrole->aclroles->first()->title);
            } else {
                $acl->addRole($aclroleObject);
            }
        }
        foreach ($users as $user) {
            if(count($user->acluserroles) >= 1) {
                $rolesgroup = array();
                foreach ($user->acluserroles as $role) {
                    $rolesgroup[] = $role->name;
                }
                $aclroleObject = new Zend_Acl_Role($user->username);
                $acl->addRole($aclroleObject, $rolesgroup);
            }
        }
        //Build the resource tree
        foreach ($aclresources as $resource) {
            if ($resource->parent) {
                $acl->addResource(new Zend_Acl_Resource($resource->name), $resource->parent->name);
            } else {
                $acl->addResource(new Zend_Acl_Resource($resource->name));
            }
        }
        //By default we deny everything.
        foreach ($aclrules as $aclrule) {
            if ($aclrule->allow) {
                $acl->allow($aclrule->acluserrole->name, $aclrule->aclresource->name, $aclrule->aclpermission->title);
            } else {
                $acl->deny($aclrule->acluserrole->name, $aclrule->aclresource->name, $aclrule->aclpermission->title);
            }
        }
        $this->getStorage()->save($acl, self::$aclcachekey);
    }

    /**
     * Clears the identity from persistent storage
     *
     * @return void
     */
    public function clearACL() {
        $this->getStorage()->clean();
    }

    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return boolean
     */
    public function hasAuth() {
        return!(null === $this->_auth);
    }

    /**
     * Returns the current Zend_Auth instance.
     *
     * @return ZendAuth
     */
    public function getAuth() {
        if (null === $this->_auth) {
            #Get Zend Auth.
            $this->_auth = ZendAuth::getInstance();
        }
        return $this->_auth;
    }

    /**
     * Clears the identity from persistent storage
     *
     * @return void
     */
    public function clearAuth() {
        #Nothing to clear.
        if (null === $this->_auth) {
            return;
        }
        $this->_auth->clearIdentity();
        $this->_auth = null;
    }

    /**
     * Returns true if and only if a user object has been initialised
     *
     * @return boolean
     */
    public function hasUser() {
        return!(null === $this->_user);
    }

    /**
     * Returns the current Zend_Auth instance.
     *
     * @return ZendAuth
     */
    public function getUser() {
        $log = $this->getLogger();
        if (null === $this->_user) {
            #Get Zend Auth.
            if ($this->hasAuth()) {
                $log->debug('Has Auth');
            } else {
                $this->getAuth();
            }
            if ($this->_auth->hasIdentity()) {
                $em = Front::getInstance()->getParam('bootstrap')->getContainer()->get('entity.manager');
                $identity = $this->_auth->getIdentity();
                $log->debug('Has Ident :'.$identity);
                if (strpos($identity, '@') === false) {
                    $this->_user = $em->getRepository(self::USERS)->findOneByUsername($identity);
                } else {
                    $this->_user = $em->getRepository(self::USERS)->findOneByEmail($identity);
                }
            } else {
                return false;
            }
            if (isset($this->_user) === false) {
                $log->debug('Recieved user object is bad for some reason.');
                return false;
            }
        }
        return $this->_user;
    }

    /**
     * Returns the current user roles for the currently Authorized user.
     *
     * @return array
     */
    public function getUserRoles() {
        $log = $this->getLogger();
        if (!$this->hasUser()) {
            $log->debug('getUser');
            $user = $this->getUser();
            if ($user == false) {
                if($this->hasAuth()) {
                    $log->debug('getUser Failed. :'.$this->_auth->getIdentity());
                }
                $log->debug('getUser Failed.');
//                throw new Zend_Acl_Exception('getRole() There is no authenticated user to operate on.');
                return false;
            } else {
                $log->debug('getUser '.$this->_user->username.'.');
                return $this->_user->acluserroles;
            }
        }
    }

    public function getRequestPermission(RequestAbstract $request) {
        $permissionString = 'Read';
        switch ($request->getActionName()) {
            case 'create':
                $permissionString = 'Create';
                break;
            case 'update':
                $permissionString = 'Update';
                break;
            case 'delete':
                $permissionString = 'Delete';
                break;
            case 'read':
            default:
                $permissionString = 'Read';
                break;
        }
        return $permissionString;
    }

    public function getRequestResource(RequestAbstract $request) {
        $resourceString = 'mvc:'.strtolower($request->getModuleName()).':'.strtolower($request->getControllerName());
        $this->config = $this->getConfig();
        $siteroot = $this->config['settings']['application']['config']['namespace'];
        $log = $this->getLogger();
        $em = $this->getEntityManager();
        $log->info("ACL::".$request->getModuleName().":".$request->getControllerName().":".$request->getActionName());
        $route = Front::getInstance()->getRouter()->getCurrentRouteName();
        $log->info($route);
        switch (substr($route, 0, 5)) {
            case 'manag':
            case 'admin':
                $resourceString = 'mvc:admin';
                $found = false;
//                $log->info('Read ['.$request->getModuleName().'] - '.$request->getControllerName());
                switch ($request->getModuleName()) {
                    case 'WedManager':
                    case 'PageRenderer':
                    case 'LuceneSearch':
                    case 'MediaLibrary':
                    case 'CoreApi':
                        $resourceString = 'mvc:admin';
                        $log->info('ACL:: Module['.$request->getModuleName().'], Controller['.$request->getControllerName().']');
                        switch (strtolower($request->getControllerName())) {
                            case 'account':
                                $resourceString = 'mvc:admin:users';
                                break;
                            case 'pages':
//                                $pgid = $request->getParam('id');
//                                if($pgid > 0) {
//                                    $pgRepo = $em->getRepository(self::PAGES);
//                                    $page = $pgRepo->find($pgid);
//                                    if(isset($page->id)) {
//                                        $url = $pgRepo->getPageUri($page);
//                                        $resourceString = str_ireplace("/", ":", $url);
//                                        $found = strpos($resourceString, $siteroot);
//                                        $log->info('Read ['.$resourceString.'] - '.$found);
//                                        if($found===true) {
//                                            $resourceString = "mvc:front" . $resourceString;
//                                        } else {
//                                            $resourceString = "mvc:front:" . $siteroot . $resourceString;
//                                        }
//                                    }
//                                }
                                $resourceString = 'mvc:admin:page';
                                break;
                            case 'template':
                                $resourceString = 'mvc:admin:template';
                                break;
                            case 'admin':
                                $resourceString = 'mvc:admin:'.strtolower($request->getModuleName());
                                break;
                            case 'manager':
                                $task = strtolower($request->getParam('task'));
                                if($task != "task") {
                                    $resourceString = 'mvc:admin:'.$task;
                                } else {
                                    $resourceString = 'mvc:admin';
                                }
                                break;
                            case 'api':
//                                $resourceString = 'mvc:admin:api';
//                                break;
                            case 'index':
                                $resourceString = 'mvc:admin';
                                break;
                            default:
                                $resourceString = 'mvc:admin:'.strtolower($request->getControllerName());
                                break;
                        }
                        break;
                    case 'Blog':
                        $resourceString = 'mvc:admin:blog';
                        switch (strtolower($request->getParam('task'))) {
                            case 'news':
                            case 'newsitems':
                                $group = ':'.'news';
                                break;
                            #Moncler
                            case 'heritageitems':
                                $group = ':'.'heritage';
                                break;
                            case 'specialprojects':
                            case 'specialprojectsitems':
                                $group = ':'.'specialproject';
                                break;
                            #LLX
                            case 'pressitems':
                                $group = ':'.'press';
                                break;
                            case 'articleitems':
                                $group = ':'.'article';
                                break;
                            case 'contactitems':
                                $group = ':'.'contact';
                                break;
                            case 'downloaditems':
                                $group = ':'.'download';
                                break;
                            case 'jobitems':
                                $group = ':'.'job';
                                break;
                            default:
                                $group = '';
                                break;
                        }
                        $resourceString .= $group;
                        break;

                }
                $log->info('Read ['.$resourceString.'] - '.$found);
                break;
            case 'apili':
                $resourceString = 'mvc:admin';
                break;
            default:
                $resourceString = "mvc:front:".$siteroot;
                $acl = $this->getACL();
                if(!$acl->has($resourceString)) {
                    $resourceString = "mvc:front";
                }
                $log->info('Read ['.$resourceString.'] - ');
                break;
        }
        return $resourceString;
    }

    public function getNavigationRole($admin = false) {
        $log = $this->getLogger();
        if (!$this->hasUser()) {
            $user = $this->getUser();
            $roles = $this->_user->acluserroles;
        } else {
            $roles = $this->_user->acluserroles;
        }
        $log->debug('getUser '.$this->_user->username.'.');
        $log->debug("Num Roles: ".count($roles));
        if($admin) {
            $rolesgroup = array();
            foreach ($roles as $role) {
                $rolesgroup[] = $role->name;
            }
            $aclroleObject = new Zend_Acl_Role($this->_user->username);
            $acl = $this->getACL();
            if(!$acl->hasRole($this->_user->username)) {
                $acl->addRole($aclroleObject, $rolesgroup);
                $this->getStorage()->save($acl, self::$aclcachekey);
            }
            return $this->_user->username;
        } else {
            foreach ($roles as $role) {
                if($role->id <= 4) {
                    return $role->name;
                }
            }
            return 'UserRole_Guest';
        }
    }

    private function getLogger() {
        return Front::getInstance()->getParam('bootstrap')->getResource('Log');
    }

    private function getEntityManager() {
        return Front::getInstance()->getParam('bootstrap')->getContainer()->get('entity.manager');
    }

    private function getConfig() {
        return Front::getInstance()->getParam('bootstrap')->getContainer()->get('config');
    }
}