<?php

namespace Wednesday\Controller;

use Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration,
    Doctrine\Common\Collections\ArrayCollection,
    \Zend_Controller_Front as Front,
    \Zend_Application_Resource_ResourceAbstract as ResourceAbstract,
    \Zend_Cache,
    \Zend_Acl,
    \Zend_Auth,
    \Zend_Registry,
    \Zend_Acl_Role,
    \Zend_Acl_Resource,
    \Zend_Session_Namespace,
    \Wednesday\Acl\Manager as WedAclAction,
    \Wednesday\Acl\WednesdayAcl as WedAcl,
    \Zend_Navigation,
    \Zend_Controller_Action as ZendActionController,
    \Wednesday\Resource\Containers,
    \Wednesday\Resource\Service as ResourceService,
    \Zend_Controller_Request_Abstract as RequestAbstract,
    \Zend_Auth as ZendAuth,
    \Wednesday_Auth_Adapter_Doctrine as DoctrineAdapterAuth;

/**
 * ActionController - The default error controller class
 *
 * @author mrhelly
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.7.4 RC1, jameshelly $
 */
class ActionController extends ZendActionController {

    /**
     *
     * @var boolean
     */
    protected $initialised;

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

    /**
     *
     * @var Zend_Locale
     */
    protected $locale;

    /**
     *
     * @var Zend_Session_SaveHandler_DbTable
     */
    protected $session;

    /**
     *
     * @var Zend_Translate
     */
    protected $translate;

    /**
     * Zend_Navigation object
     * @var Zend_Navigation
     */
    protected $navcontainer;

    /**
     *
     * @var array
     */
    protected $config;

    /**
     *
     * Access to Zend_Log.
     * @var Zend_Log
     */
    public $log;

    /**
     * This action handles
     *    - Default Action Initialisation
     */
    public function init() {
        #Get bootstrap object.
        $bootstrap = $this->getInvokeArg('bootstrap');
        #Get Logger
        $this->log = $this->getLog();
        $this->log->debug(get_class($this) . '::ini()');
        $init = ($this->initialised) ? 'true' : 'false';
        $this->log->info(get_class($this) . '::init( ' . $init . ' )');
        //$this->log->debug(get_class($this).'::init');
        if ($this->initialised) {
            return;
        }
        #Get Resources
        $bootstrap = $this->getInvokeArg('bootstrap');
        $this->session = new Zend_Session_Namespace('wedcms'); //$bootstrap->getResource('Session');
        $this->session->setExpirationSeconds(floor(60 * 60 * 6));
        #Set locale defaults
        $this->locale = $bootstrap->getResource('Locale');
//        #Use the currently selected locale to show the proper flag.
//        if (!@UNIT_TESTING)
//            $this->view->admin_locale = $this->session->admin_locale;
//
//        $this->translate = $bootstrap->getResource('Translate');
////        $this->translate->setLocale($this->locale->__toString());
//        $this->registry = Zend_Registry::getInstance();
//        $this->locale->setLocale($this->registry->locale->__toString());
//        $this->translate->setLocale($this->registry->locale->__toString());
        
        #Get Config
        $this->config = $bootstrap->getContainer()->get('config');

        #Get Doctrine Entity Manager
        $this->em = $bootstrap->getContainer()->get('entity.manager');
        $this->view->placeholder('doctrine')->exchangeArray(array('em' => $this->em));

        #Get Wednesday Resource
        $this->wednesday = $bootstrap->getContainer()->get('wednesday.manager');

        #Get Template Manager
        $this->template = $bootstrap->getContainer()->get('template.manager');

        if ($this->view->initialised === true) {
            $this->log->debug(get_class($this) . '::init( Rerun )');
            return;
        }

        if ($this->config['settings']['application']['offline'] == true) {
            $this->_redirect('/offline');
            return;
        }

//        #Set Translate.
//        $transObj = (object) array('translate' => $this->translate);
//        $this->view->placeholder('translate')->exchangeArray($transObj);
        #set siteroot
        $this->view->siteroot = $this->config['settings']['application']['siteroot'];

        #Get Zend Auth.
//        $this->auth = Zend_Auth::getInstance();
//
        #Prepare to pass the available locales to the localeSwitcher view helper.
        $this->view->available_locales = $this->config['settings']['application']['locales'];

        #Character Encoding
        $encoding = 'UTF-8';

        #Render Sidebar
        $this->view->layout()->sidebar = "<h4>Sidebar</h4>";

        #Init Context Switching
        $this->_helper->contextSwitch()->setContext(
                'html', array(
            'suffix' => 'html',
            'headers' => array(
                'Content-Type' => 'text/html; Charset=' . $encoding,
            ),
                ), 'xml', array(
            'suffix' => 'xml',
            'headers' => array(
                'Content-Type' => 'text/xml; Charset=' . $encoding,
            ),
                ), 'json', array(
            'suffix' => 'json',
            'headers' => array(
                'Content-Type' => 'application/json; Charset=' . $encoding,
            ),
                )
        )->setAutoJsonSerialization(false)->initContext();

        #Default View Setup
        $this->view->doctype('<!DOCTYPE html>');
        $this->buildPageTitle();
        $this->buildPageKeywords();
        $this->buildPageDescription();
        $this->buildHeadMeta($encoding, $this->locale);
        $this->buildScripts();
        $this->buildGoogleAnalytics();
        $this->addFacebookOpenGraph();

        #Navigation
        if ($this->getRequest()->getControllerName() == 'error') {
            $this->view->layout()->navigation = new Zend_Navigation();
            $this->view->layout()->footerNavigation = new Zend_Navigation();
        } else {
            if ($this->config['settings']['application']['menu']['mode'] == "menuitems") {
                $topmenu_id = $this->config['settings']['application']['menu']['rootid'];
                $this->view->layout()->navigation = $this->wednesday->buildNavigationForMenus($this->getRequest(), $topmenu_id);
                $this->view->layout()->footerNavigation = $this->wednesday->buildNavigationForMenus($this->getRequest(), 'Footer');
            } else {
                $this->view->layout()->navigation = $this->wednesday->buildNavigation($this->getRequest(), 'Main');
                $this->view->layout()->footerNavigation = $this->wednesday->buildNavigation($this->getRequest(), 'Footer');
            }
        }

        //This seems incredibly HACKTASTIC! why is Zend_Navigation like this with menus that have no children?
        $uri = $this->getRequest()->getRequestUri();
        if ($uri != "/") {
            $activeNav = $this->view->layout()->navigation->findByUri($uri);
            if (isset($activeNav) === true) {
                $activeNav->active = true;
                $activeNav->setClass("active");
            }
        }

        #Default Placeholders.
        $this->view->placeholder('footer-vcard')->exchangeArray($this->config['settings']['application']['site']['vcard']);
        $this->view->placeholder('sitecompany')->set($this->config['settings']['application']['clientname']);
        $this->view->placeholder('copyright')->set(date("Y"));

        $resources = ResourceService::getInstance();
        //$dest = ($resource->cdn == 1)?'cdn':'local';
        //$this->_baseuri = $resources->getBaseUri('local');
        $this->_basepath = $resources->getBaseUri('local');

        $this->view->assetBasePath = $this->_basepath;

        #Initialised, don't run again.
        $this->view->initialised = $this->initialised = true;
        $init = ($this->initialised) ? 'true' : 'false';
        $this->log->info(get_class($this) . '::init(' . $init . ')');
    }

    /**
     * Store bookstrap
     * @see Controller/Zend_Controller_Action::preDispatch()
     */
    public function preDispatch() {
        if (isset($this->log) === false) {
            $this->log = $this->getLog();
        }
        $this->log->info($this->locale->__toString()." ".$this->registry->locale->__toString());

        $this->translate->setLocale($this->registry->locale->__toString());
        $this->view->navigation()->setTranslator($this->translate);
        //die('WTF?');
        $this->log->info(get_class($this) . "::preDispatch[]");
        if ($this->config['settings']['application']['auth']['required'] == true) {
            #init live plugins.
            $this->auth = Zend_Auth::getInstance();
            if ($this->auth->hasIdentity()) {
                $this->acl = WedAcl::getInstance();
                $this->log->info("ACL Role: " . $this->acl->getNavigationRole(true));
                $this->view->navigation()->setAcl($this->acl->getAcl())->setRole($this->acl->getNavigationRole(true));
                //$this->view->navigation()->setTranslator($this->view->placeholder('translate')->translate);
                $user = $this->acl->getUser();
                if (($user->logins <= 0) && ($this->getRequest()->getRequestUri() != '/auth/changepassword/')) {
                    $this->_redirect('/auth/changepassword/');
                }
            } else if (
                    ($this->getRequest()->getRequestUri() != '/auth/login/')
                    &&
                    ($this->getRequest()->getRequestUri() != '/admin/lost-password/')
            ) {
                # XXX SPILL HACK
                #TODO Redirect to a better page for frontend requests?
                $ns = new Zend_Session_Namespace('wednesday');
                if(isset($ns->authReturn)===false) {
                    $ns->authReturn = $this->view->url();
                }
                $this->_redirect('/auth/login/');
                return;
//                $this->log->info(get_class($this) . "::SPILL HACK for auth " . $_SERVER['REMOTE_USER']);
//
//                $bootstrap = Front::getInstance()->getParam('bootstrap');
//                $adapter = new DoctrineAdapterAuth(
//                                $bootstrap->getContainer()->get('entity.manager'),
//                                'Application\Entities\Users',
//                                'username',
//                                'password',
//                                "checkPassword"
//                );
//                $adapter->setIdentity($_SERVER['REMOTE_USER']);
//                $adapter->setCredential('none');
//                $result = $this->auth->authenticate($adapter);
//                $this->acl = WedAcl::getInstance();
//                $this->view->navigation()->setAcl($this->acl->getAcl())->setRole($this->acl->getNavigationRole());
            } else {
                $this->acl = WedAcl::getInstance();
                $this->view->navigation()->setAcl($this->acl->getAcl())->setRole($this->acl->getNavigationRole());
            }
        }
    }

    /**
     *
     * @return type
     */
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
    public function getUniqid() {
        return uniqid() . dechex(rand(65536, 1048574));
    }

    /**
     *
     * @param type $options
     */
    protected function buildPageTitle($options = null) {
        $denyTitles = array(
            'Blog',
            'LuceneSearch',
            'MediaLibrary',
            'PageRenderer',
            'WedManager',
            'Default'
        );
        #Setup styles & output variables.
        $request = $this->getRequest();

        $moduleTitle = ucwords($request->getModuleName());
        if ($moduleTitle == 'Default') {
            $moduleTitle = 'Home';
        }
        if (in_array($moduleTitle, $denyTitles)) {
            $moduleTitle = "";
        }
        switch ($moduleTitle) {
            case 'Default':
            case 'HomePage':
                $moduleTitle = "Home";
                break;
//            case 'Stores':
//                $moduleTitle = "Stores";
//                break;
            case 'SpecialProjects':
//                $moduleTitle = "Don't Steal The Jacket";
                $moduleTitle = "Special Projects";
                break;
//            case 'Mykita':
//                $moduleTitle = "Mykita &amp; Moncler";
//                break;
            case 'News':
//                $moduleTitle .= " ".ucwords($request->getModuleName())." ".ucwords($request->getControllerName())." ".ucwords($request->getActionName())."";
                $moduleTitle = ($options != null) ? "" : "News";
                break;
//            case 'ProductManager':
////                $moduleTitle = "PM:: ".$request->getModuleName()." ".$request->getControllerName()." ".$request->getActionName()."";
//                $moduleTitle = "";
//                switch($request->getActionName()) {
//                    case 'gallery':
//                        $moduleTitle = "Gallery";
//                        break;
//                    case 'collection':
//                        $moduleTitle = "";
//                        break;
//                    default:
//                        break;
//                }
//                break;
            case 'PageRenderer':
//                $moduleTitle = "PR:: ".$request->getModuleName()." ".$request->getControllerName()." ".$request->getActionName()."";
                $moduleTitle = "";
                break;
            default:
                $moduleTitle = " " . $request->getModuleName() . " " . $request->getControllerName() . " " . $request->getActionName() . "";
                $moduleTitle = "";
                break;
        }
        $title = ($options != null) ? $this->config['settings']['application']['clientname'] . ' : ' . $options['title'] : $this->config['settings']['application']['clientname'] . ' ';
        $this->view->headTitle()->setSeparator(' - ');
        $this->view->headTitle($title, 'SET')->headTitle($moduleTitle);
    }

    /**
     *
     * @param type $options
     */
    protected function buildPageKeywords($options = null) {
        $keywords = ($options != null) ? implode(',', $options) : $this->config['settings']['application']['site']['meta']['keywords'];
        $this->log->debug(get_class($this) . '::buildPageKeywords');
        $this->log->debug(print_r($options, true));
        $this->view->headMeta()->setName
                (
                'keywords',
                /* @@OSNAME@@, @@SEASON@@, @@LOOKNAME@@, */ $keywords
        );
    }

    /**
     *
     * @param type $options
     */
    protected function buildPageDescription($options = null) {
        $description = ($options != null) ? @$options['description'] : $this->config['settings']['application']['site']['meta']['description'];
        $this->log->debug(get_class($this) . '::buildPageDescription');
        $this->log->debug(print_r($options, true));
        $this->view->headMeta()->setName
                (
                'description',
                /* @@SEASONCOLL@@ */ trim(strip_tags($description))
        );
    }

    /**
     *
     * @param type $encoding
     * @param type $locale
     */
    protected function buildHeadMeta($encoding, $locale) {
        //$themePath = $this->view->placeholder('themepath');
        $this->view->headMeta()
                ->setCharset($encoding)
                ->setHttpEquiv
                        (
                        'Content-Type', 'text/html; charset=' . strtoupper($encoding)
                )
                ->setHttpEquiv
                        (
                        'X-UA-Compatible', 'IE=edge,chrome=1'
        );

        $this->view
                ->headLink
                        (
                        array
                            (
                            'rel' => 'home',
                            'href' => $this->view->baseUrl('/')
                        )
                )
                ->headLink
                        (
                        array
                            (
                            'rel' => 'shortcut icon',
                            'href' => $this->view->theme->themePath . 'img/branding/favicon.ico'
                        )
                )
                ->headLink
                        (
                        array
                            (
                            'rel' => 'apple-touch-icon',
                            'href' => $this->view->theme->themePath . 'img/branding/apple-touch-icon.png'
                        )
                )
                ->headLink
                        (
                        array
                            (
                            'rel' => 'profile',
                            'href' => 'http://microformats.org/profile/hatom'
                        )
                )
                ->headLink
                        (
                        array
                            (
                            'rel' => 'profile',
                            'href' => 'https://microformats.org/profile/hcard'
                        )
        );
        $this->view->headLink()
                ->appendAlternate('http://' . $this->config['settings']['application']['domainName'] . '/feed/rss/news', 'application/rss+xml', 'Moncler.com News RSS Feed')
                ->appendAlternate('http://' . $this->config['settings']['application']['domainName'] . '/feed/atom/news', 'application/atom+xml', 'Moncler.com News Atom Feed');
    }

    /**
     *
     */
    protected function buildScripts() {
        $themePath = $this->view->placeholder('themepath');
        $inlineScript = <<<EOT
            window.jsonPath       = "{$themePath}json/vars-en.json";
            window.projekktorPath = "{$themePath}swf/jarisplayer.swf";
EOT;
        $this->view->inlineScript()->appendScript($inlineScript, 'text/javascript');
    }

    protected function buildGoogleAnalytics() {
        //Prepopulate Google Analytics code
        $googleaccount = $this->config['settings']['application']['googleanalytics'];
        $this->view->googleAnalytics($googleaccount);
    }

    /**
     *
     * @param type $override
     */
    protected function addFacebookOpenGraph($override = null) {

        $this->view->inlineScript()->appendFile('https://connect.facebook.net/en_US/all.js#xfbml=1');

        $default = array(
            'title' => $this->config['settings']['application']['facebook']['og']['title'],
            'description' => $this->config['settings']['application']['facebook']['og']['description'],
            'type' => $this->config['settings']['application']['facebook']['og']['type'],
            'url' => $this->view->serverUrl($this->view->url()),
            'image' => $this->view->serverUrl('/branding/CMSlogo.png'),
            'site_name' => $this->config['settings']['application']['facebook']['og']['site_name'],
            'admins' => $this->config['settings']['application']['facebook']['og']['admins']
        );

        if (isset($override)) {
            $default = array_merge($default, $override);
        }

        $this->view->headMeta()
                ->setProperty
                        (
                        'og:site_name', $default['site_name']
                )
                ->setProperty
                        (
                        'og:admins', $default['admins']
                )
                ->setProperty
                        (
                        'og:title', $default['title']
                )
                ->setProperty
                        (
                        'og:description', trim(strip_tags($default['description']))
                )
                ->setProperty
                        (
                        'og:type', $default['type']
                )
                ->setProperty
                        (
                        'og:url', $default['url']
                )
                ->setProperty
                        (
                        'og:image', $default['image']
        );
    }

    private function getConfig() {
        $front = Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        return $bootstrap->getContainer()->get('config');
    }

}
