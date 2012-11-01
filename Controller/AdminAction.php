<?php

namespace Wednesday\Controller;

use Doctrine\ORM\EntityManager,
    \Zend_Controller_Front as Front,
    \Zend_Cache,
    \Zend_Acl,
    \Zend_Acl_Role,
    \Zend_Acl_Resource,
    \Zend_Session_Namespace,
    \Zend_Auth,
    \Zend_Locale,
    \Wednesday\Acl\Manager as WedAclAction,
    \Wednesday\Acl\WednesdayAcl as WedAcl,
    \Zend_Config_Xml;

/**
 * Description of AdminAction
 *
 * @author mrhelly
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.7.4 RC1, jameshelly $
 */
class AdminAction extends ActionController {

    const USERS     = "Application\Entities\Users";
    const SETTINGS  = "Wednesday\Models\Settings";
    const SITESNS   = "sites";

    /**
     *
     * Zend_Auth object
     * @var Zend_Auth
     */
    protected $auth;

    /**
     *
     * Auto Loaded acl object to filter program flow.
     * @var Zend_Acl
     */
    protected $acl;

    /**
     * Doctrine\ORM\EntityManager object wrapping the entity environment
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     *
     * Access to Zend_Log.
     * @var Zend_Log
     */
    public $log;

    /**
     *
     * Wednesday Manager (Acl|Session|Cache).
     * @var Wednesday_Application_Resource_Wednesday
     */
    protected $wednesday;

    /**
     *
     * Wednesday Manager (Themes|Templates).
     * @var Wednesday_Application_Resource_Template
     */
    protected $template;

    public function init() {
        $bootstrap = $this->getInvokeArg('bootstrap');
        #Get Logger
        $this->log = $bootstrap->getResource('Log');
        $this->log->debug(get_class($this).'::init');

        #Get Resources
//        $this->session = $bootstrap->getResource('Session');
        $this->session = new Zend_Session_Namespace('wedcms');
        $this->session->setExpirationSeconds(floor(60*60*6));

        #Set locale defaults
        $this->locale = $bootstrap->getResource('Locale');
        $this->translate = $bootstrap->getResource('Translate');
        #Get Doctrine Entity Manager
        $this->config = $bootstrap->getContainer()->get('config');
        #Get Doctrine Entity Manager
        $this->em = $bootstrap->getContainer()->get('entity.manager');
        $this->view->placeholder('doctrine')->exchangeArray(array('em'=>$this->em));

        #Get Wednesday Manager
        $this->wednesday = $bootstrap->getContainer()->get('wednesday.manager');

        #Get Template Manager
        $this->template = $bootstrap->getContainer()->get('template.manager');

        #Set Translate.
        $transObj = (object) array('translate' => $this->translate);
        $this->view->placeholder('translate')->exchangeArray($transObj);

        #Get Zend Auth.
        $this->auth = Zend_Auth::getInstance();

        #Get Acl Object
//        $this->acl = WedAcl::getInstance();

        #Prepare to pass the available locales to the localeSwitcher view helper.
        $this->view->available_locales = $this->config['settings']['application']['locales'];
        $this->view->debug = $this->config['settings']['application']['debug']['js']['labjs'];

        //Manage this in action
//        #Use the currently selected locale to show the proper flag.
//        $this->view->admin_locale = $this->session->admin_locale;
//
//        if (!empty($this->view->admin_locale)) {
//            $this->locale = new Zend_Locale($this->session->admin_locale);
//            $this->view->placeholder('locale')->set($this->locale);
//            $this->getInvokeArg('bootstrap')->getContainer()->set('locale', $this->locale);
//        } else {
//            $this->view->admin_locale = $this->locale->__toString();
//        }
//        $this->translate->setLocale($this->locale->__toString());

        $this->view->identity = false;

        $this->log->debug(get_class($this) . 'Allow Admin (' . $this->config['settings']['application']['administration'] . ')');
        if($this->config['settings']['application']['administration']==false){
            $this->_redirect('/error/404');
            return;
        }

        #Set Navigation to view
        if(@$this->config['settings']['application']['menu']['mode']=="menuitems") {
            $this->view->placeholder('navigation')->main = $this->wednesday->buildAdminNavigation($this->getRequest(), 'Main');
            $this->view->placeholder('navigation')->footer = $this->wednesday->buildAdminNavigation($this->getRequest(),'Footer');
        } else {
            $this->view->placeholder('navigation')->main = $this->buildNavigation('sidemenu');
            $this->view->placeholder('navigation')->footer = $this->buildNavigation('footer');
//            $this->view->placeholder('navigation')->main = $this->wednesday->buildNavigation($this->getRequest(),'Main');
//            $this->view->placeholder('navigation')->footer = $this->wednesday->buildNavigation($this->getRequest(),'Footer');
        }
        #set Navigation to view
        $this->view->placeholder('navigation')->permissions = $this->buildNavigation('permissions');
        $this->view->placeholder('navigation')->taxonomies  = $this->buildNavigation('taxonomies');
        $this->view->placeholder('navigation')->metadata    = $this->buildNavigation('metadata');
        $this->view->placeholder('navigation')->assets      = $this->buildNavigation('assets');
        $this->view->placeholder('navigation')->content     = $this->buildNavigation('content');
        $this->view->placeholder('navigation')->advanced    = $this->buildNavigation('advanced');

        #Default Placeholders.
        $this->view->placeholder('footer-vcard')->exchangeArray($this->config['settings']['application']['site']['vcard']);
		$this->view->placeholder('sitecompany')->set($this->config['settings']['application']['site']['clientName']);
        $this->view->placeholder('copyright')->set('2012');
        $this->view->showsites = (count($this->config['settings']['application']['sites'])>1)?true:false;

        #Character Encoding
        $encoding = 'UTF-8';
        $this->buildHeadMeta($encoding, $this->locale->__toString());
        $this->getSiteRoot();
        $this->log->debug(get_class($this) . '::init( )');
    }

    /**
     * Store bookstrap
     * @see Controller/Zend_Controller_Action::preDispatch()
     */
    public function preDispatch() {
        $this->log->debug(get_class($this)."::preDispatch[]");
        $this->log->debug(get_class($this).' '.$this->session->siteroot);
//        $this->log->debug(get_class($this) . "::SPILL HACK for auth " . $_SERVER['REMOTE_USER']);
//        $this->log->debug($_SERVER);
//        $this->log->debug($_COOKIE);
        #init live plugins.
        if ($this->auth->hasIdentity()) {
            $this->acl = WedAcl::getInstance();
            $this->log->debug("ACL Role: ".$this->acl->getNavigationRole());
            $this->view->navigation()->setAcl($this->acl->getAcl())->setRole($this->acl->getNavigationRole(true));
            $user = $this->acl->getUser();
            if(($user->logins <= 0)&&($this->getRequest()->getRequestUri() != '/admin/auth/changepassword/')) {
                $this->_redirect('/admin/auth/changepassword/');
            }
        } else if (
                ($this->getRequest()->getRequestUri() != '/admin/auth/login/')
                &&
                ($this->getRequest()->getRequestUri() != '/admin/auth/lostpassword/')
                &&
                ($this->getRequest()->getRequestUri() != '/admin/lost-password/')
        ) {
            #TODO Redirect to a better page for frontend requests?
            $ns = new Zend_Session_Namespace('wednesday');
            if(isset($ns->authReturn)===false) {
                $ns->authReturn = $this->view->url();
            }
            $this->_redirect('/admin/auth/login/');
            return;
        }
    }

    protected function getSiteRoot() {
        $sites = $this->em->getRepository(self::SETTINGS)->findOneByTitle(self::SITESNS);
        if(isset($this->session->siteroot)===false) {
            $this->session->siteroot = $this->config['settings']['application']['siteroot'];
        }
        $this->log->debug(get_class($this) . '::init('.$sites->content[$this->session->siteroot].')');
        $this->view->placeholder('site')->exchangeArray($this->config['settings']['application']['site']);
        $this->view->placeholder('siteroot')->set($this->session->siteroot);
        $this->view->placeholder('siteroot_name')->set($sites->content[$this->session->siteroot]);
    }

    public function getLog() {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }

    /**
     *
     * @return type
     */
    protected function getAuthenticatedUser() {
        $identity = $this->auth->getIdentity();
        if (strpos($identity, '@') === false) {
            $user = $this->em->getRepository(self::USERS)->findOneByUsername($identity);
        } else {
            $user = $this->em->getRepository(self::USERS)->findOneByEmail($identity);
        }
        return $user;
    }

    public function getUniqid() {
        return uniqid() . dechex(rand(65536, 1048574));
    }

    public function getUniqeId($id) {
        return uniqid() . md5($id.dechex(rand(65536, 1048574))) . dechex(rand(65536, 1048574));
    }

    protected function buildNavigation($section) {
        #HardCode WedManager module
        if($this->config['settings']['application']['admin']['menu']['mode'] == 'persite') {
            $sites = $this->em->getRepository(self::SETTINGS)->findOneByTitle(self::SITESNS);
            $moduleFolder = APPLICATION_PATH . "/configs/admin-nav-".strtolower($sites->content[$this->session->siteroot]).".ini";
        } else {
            //if($this->config['settings']['application']['admin']['menu']['mode'] == 'single')
            $moduleFolder = APPLICATION_PATH . "/configs/admin-navigation.ini";
        }

        $navigation = new \Zend_Config_Ini($moduleFolder, 'navigation');
        $container = new \Zend_Navigation($navigation->$section);
        return $container;
    }

}
