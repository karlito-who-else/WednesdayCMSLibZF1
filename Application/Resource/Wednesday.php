<?php

//namespace Wednesday\Application\Resource;

use Doctrine\ORM\em,
    Wednesday\Auth\Adapter\Doctrine,
    Wednesday\Restable\RestListener,
    Gedmo\Tree\TreeListener,
    Gedmo\Loggable\LoggableListener,
    Gedmo\Translatable\TranslationListener,
    Doctrine\ORM\Configuration,
    \Zend_Acl,
    \Zend_Acl_Role,
    \Zend_Acl_Resource,
    \Wednesday\Acl\WednesdayAcl as WedAcl,
    \Zend_Navigation,
    \Zend_Application_Resource_ResourceAbstract as ResourceAbstract,
    \Zend_Cache,
    \Zend_Registry,
    \Zend_Controller_Front as Front,
    \Application\Entities\Pages as PageEntity,
    \Application\Entities\Pages as UserMenuEntity,
    \Zend_Session_Namespace;

/**
 * Description of Wednesday
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author mrhelly
 */
class Wednesday_Application_Resource_Wednesday extends ResourceAbstract {

    const BRANDS = 'Application\Entities\Brands';
    const ROLES = 'Application\Entities\AclRoles';
    const USERS = 'Application\Entities\Users';
    const PAGES = 'Application\Entities\Pages';
    const USERMENUS = 'Application\Entities\MenuItems';
    const ACLRULZ = 'Application\Entities\AclRules';
    const ARTICLES = 'Application\Entities\ArticleItems';
    const CONTACTS = 'Application\Entities\ContactItems';
    const DOWNLOADS = 'Application\Entities\DownloadItems';
    const JOBS = 'Application\Entities\JobItems';
    const NEWS = 'Application\Entities\NewsItems';
    const COLLECTIONS = 'Application\Entities\Collections';
    const NUM_ITEMS_OUTSIDE_PASTSEASONS = 2;
    const NUM_ITEMS_SHOW_PASTSEASONS = 4;

    /**
     *
     * @var \Doctrine\ORM\em
     */
    protected $em;

    /**
     *
     * @var \Zend_Acl
     */
    protected $acl;

    /**
     *
     * Zend_Auth object
     * @var Zend_Auth
     */
    protected $auth;

    /**
     *
     * @var array
     */
    protected $options;

    /**
     *
     * @var \Wednesday\Site
     */
    protected $site;

    /**
     *
     * @var \Wednesday\Template
     */
    protected $template;

    /**
     *
     * @var Zend_Config_Ini
     */
    protected $theme;

    /**
     *
     * @var Zend_Log
     */
    protected $log;

    /**
     *
     * @var Zend_Registry
     */
    protected $registry;

    /**
     * Init Wednesday Manager (Themes|Templates|Acl|Session|Cache|Entities).
     *
     * @param N/A
     * @return $this;
     */
    public function init() {
        #Get log
        $this->log = $this->getBootstrap()->getResource('Log');
        $this->getBootstrap()->getContainer()->set('wednesday.manager', $this);
        $this->session = new Zend_Session_Namespace('wedcms');
        $this->config = $this->getConfig();
        $this->registry = Zend_Registry::getInstance();
        $this->log->debug(get_class($this) . '::init');
        return $this;
    }

    public function buildNavigation(Zend_Controller_Request_Abstract $request, $menuType = 'Main') {
        $this->auth = Zend_Auth::getInstance();
//        $logedIn = $this->auth->hasIdentity();
        $this->em = $this->getEntityManager();
        $cnf = $this->getConfig();
        $repo = $this->em->getRepository(self::PAGES);
        $rootpage = $this->em->getRepository(self::PAGES)->findOneByUid($cnf['settings']['application']['siteroot']);
        $locale = "en_GB";
        if ($rootpage) {
            $rootpage->setTranslatableLocale($locale);
            $this->em->refresh($rootpage);
        } else {
            $container = new Zend_Navigation();
            return $container;
        }

        $tree = $repo->childrenHierarchy($rootpage);
        $currentpath = "";
        $container = new Zend_Navigation();
        $loggedIn = array('log-out', 'change-password', 'sitemap');

        foreach ($rootpage->children as $page) {
            if (($page->status == PageEntity::STATUS_PUBLISHED) && ($page->menupos == $menuType)) {
                if (!in_array($page->slug, $loggedIn) || in_array($page->slug, $loggedIn)) {
                    //&& $logedIn
                    $urlpath = substr($request->getPathInfo(), 1);
                    $active = (strncmp($page->slug, $urlpath, strlen($page->slug)) == 0) ? true : false;
                    $page_options = array(
                        'label' => $this->trim($page->title),
                        'title' => $this->trim($page->title),
                        'uri' => $currentpath . '/' . $page->slug,
                        'pages' => $this->addChildPages($page->children, $currentpath . '/' . $page->slug, $active, $urlpath, $menuType)
                    );
                    $resource = false;
                    foreach ($page->metadata as $metadata) {
                        $this->log->info($metadata->title);
                        if ($metadata->type == 'aclresource') {
                            $resource = $metadata->content;
                            $this->log->info($resource);
                        }
                    }
                    if ($resource != false) {
                        $page_options['privilege'] = 'Read';
                        $page_options['resource'] = $resource;
                    }
                    $container->addPage(new Zend_Navigation_Page_Uri($page_options));
                }
            }
        }
        return $container;
    }

    protected function addChildPages($kids, $currentpath, $isactive, $urlpath, $menuType) {
        $pages = array();
        $this->em = $this->getEntityManager();
        if (isset($kids) === true) {
            foreach ($kids as $kid) {
                if (($kid->status == PageEntity::STATUS_PUBLISHED) && ($kid->menupos == $menuType)) {

                    $visible = ($kid->status == PageEntity::STATUS_PUBLISHED) ? true : false;
                    $visible = ($kid->menupos == $menuType) ? $visible : false;
                    $testpath = substr($urlpath, strlen($currentpath)); //substr(str_replace(substr($currentpath, 1), '', $urlpath,1), 1);
                    $active = (strncmp($kid->slug, $testpath, strlen($kid->slug)) == 0) ? true : false;

                    $page_options = array(
                        'label' => $this->trim($kid->title),
                        'title' => $this->trim($kid->title),
                        'active' => $active,
                        'visible' => $isactive,
                        'uri' => $currentpath . '/' . $kid->slug,
                        'pages' => $this->addChildPages($kid->children, $currentpath . '/' . $kid->slug, $isactive, $urlpath, $menuType)
                    );
                    $resource = false;
                    foreach ($kid->metadata as $metadata) {
                        $this->log->debug($metadata->title);
                        if ($metadata->type == 'aclresource') {
                            $resource = $metadata->content;
                        }
                    }
                    if ($resource != false) {
                        $page_options['privilege'] = 'Read';
                        $page_options['resource'] = $resource;
                    }
                    $this->log->debug($page_options);
                    $pages[] = new Zend_Navigation_Page_Uri($page_options);
                }
            }
        }
        return $pages;
    }

    protected function addChildPagesForCollectionsMenu($kids, $currentpath, $isactive, $urlpath, $menuType) {

        $pages = array();
        $this->em = $this->getEntityManager();
        $child_counter = 0;
        $num_added = 0;
        $past_seasons_inserted = false;
        $special_link = false;
        $class = 'visible';
        $target = '';
        $num_items_outside_pastseasons = self::NUM_ITEMS_OUTSIDE_PASTSEASONS;
        $min_items_to_show_pastseasons = self::NUM_ITEMS_SHOW_PASTSEASONS;

        if (isset($kids) === true) {
            foreach ($kids as $kid) {

                // check if the item is shoppable in order to skip it from the menu
                if ($kid->shoppable === true) {
                    if ($this->registry->isEcommerce === false) {
                        $num_items_outside_pastseasons++;
                        continue;
                    }
                }

                $num_children_parent = count($kid->parent->children);
                $parent_name = strtolower($kid->parent->name);

                $now = time();
                if (($kid->publishstart == null || $kid->publishend == null) || ($now < $kid->publishstart->getTimestamp() || $now > $kid->publishend->getTimestamp() )) {

                    //insert Past Seasons in last place
                    if ($child_counter >= ($num_children_parent - 1) && $parent_name == 'collections' && !$past_seasons_inserted && $num_children_parent >= $min_items_to_show_pastseasons) {

                        $page_options = array(
                            'label' => 'Past Seasons',
                            'title' => 'Past Seasons',
                            'id' => 'past-seasons',
                            'class' => 'visible',
                            'active' => false,
                            'visible' => $isactive,
                            'uri' => '#',
                            'pages' => array()
                        );

                        $past_seasons_inserted = true;
                        $pages[] = new Zend_Navigation_Page_Uri($page_options);
                    }

                    $child_counter++;
                    continue;
                }

                $active = $this->checkIfIsSubpath($kid->uri, $urlpath);

                $forcevisible = false;
                if ($active == false) {
                    $active = $this->checkIfChildrenContainsPath($kid, $urlpath);

                    if ($active) {
                        $forcevisible = true;
                    } else {

                        //special link means that contains some extra data that is not on the DB, such as extra parameters. We must identify them in order
                        //to show the menu properly
                        $special_link = true;
                        $kid_url = str_replace('/', '\/', trim($kid->uri, '/'));
                        $urlpth = trim($urlpath, '/');
                        $active = (preg_match('/' . $kid_url . '\//', $urlpth)) ? true : false;

                        //if the url's doesn't match at all but have the same parent as the current url, then we show it
                        $urlpath_entity = $this->getUrlPathEntity($urlpath);

                        if ($urlpath_entity) {
                            $uepid = $urlpath_entity->parent->id;
                        } else {
                            $uepid = 0;
                        }

                        $kpid = (int) $kid->parent->id;

                        if ($uepid == $kpid) {
                            $isactive = true;
                        }
                    }
                }

                if ($active) {
                    $isactive = true;
                }


                if ($isactive == true && !$forcevisible && !$special_link) {

                    $urlpathlevel = $this->getUrlPathMinLevel($urlpath);

                    //we only show the next level to the current path
                    if ($kid->lvl > $urlpathlevel + 1) {
                        $isactive = false;
                    }
                }

                //for 'collections' we'll need a special submenu 'Past Seasons' with a dropdown selector
                //so we'll need some of the collections children to be hidden
                if ($parent_name == 'collections' && $num_children_parent < $min_items_to_show_pastseasons) {
                    $class = 'visible';
                } else {

                    if ($num_added >= $num_items_outside_pastseasons) {
                        if ($parent_name == 'collections' && !$active && $forcevisible === false) {
                            $class = 'screen-offset';
                        } else {
                            $class = 'visible';
                        }
                    }
                }

                if ($kid->type != 'url' && !preg_match('/^\//', $kid->uri)) {
                    $kid->uri = '/' . $kid->uri;
                }

                if ($kid->type == 'url') {
                    $target = '_blank';
                }

                $page_options = array(
                    'label' => $this->trim($kid->name),
                    'title' => $this->trim($kid->name),
                    'active' => $active,
                    'visible' => $isactive,
                    'target' => $target,
                    'class' => $class,
                    'uri' => $kid->uri,
                    'pages' => $this->addChildPagesForCollectionsMenu($kid->children, $currentpath, $active, $urlpath, $menuType)
                );


                $pages[] = new Zend_Navigation_Page_Uri($page_options);

                //insert Past Seasons in last place
                if ($child_counter >= ($num_children_parent - 1) && $parent_name == 'collections' && !$past_seasons_inserted && $num_children_parent >= $min_items_to_show_pastseasons) {

                    $page_options = array(
                        'label' => 'Past Seasons',
                        'title' => 'Past Seasons',
                        'id' => 'past-seasons',
                        'class' => 'visible',
                        'active' => false,
                        'visible' => $isactive,
                        'uri' => '#',
                        'pages' => array()
                    );

                    $past_seasons_inserted = true;
                    $pages[] = new Zend_Navigation_Page_Uri($page_options);
                }
                $child_counter++;
                $num_added++;
            }
        }


        return $pages;
    }

    protected function addChildPagesForMenus($kids, $currentpath, $isactive, $urlpath, $menuType) {
        $target = null;
        $pages = array();
        $this->em = $this->getEntityManager();


        $child_counter = 0;
        $special_link = false;
        if (isset($kids) === true) {
            foreach ($kids as $kid) {

                // check if the item is shoppable in order to skip it
                if ($kid->shoppable === true) {
                    if ($this->registry->isEcommerce === false) {
                        continue;
                    }
                }

                $now = time();

                if (($kid->publishstart == null || $kid->publishend == null) || ($now < $kid->publishstart->getTimestamp() || $now > $kid->publishend->getTimestamp() )) {
                    $child_counter++;
                    continue;
                }

                $active = (strcmp(trim($kid->uri, '/'), trim($urlpath, '/')) == 0) ? true : false;

                if ($active == false) {
                    $active = $this->checkIfChildrenContainsPath($kid, $urlpath);

                    if ($active == false) {
                        $special_link = true;
                        $page_url = str_replace('/', '\/', trim($kid->uri, '/'));
                        $urlpth = trim($urlpath, '/');
                        $active = (preg_match('/' . $page_url . '\//', $urlpth)) ? true : false;
                    }
                } else {
                    $isactive == true;
                }


                if ($isactive == true && !$special_link) {

                    $urlpathlevel = $this->getUrlPathMinLevel($urlpath);

                    //we only show the next level to the current path
                    if ($kid->lvl > $urlpathlevel + 1) {
                        $isactive = false;
                    }
                }


                if ($kid->type == 'url') {
                    $target = '_blank';
                }


                $page_options = array(
                    'label' => $this->trim($kid->name),
                    'title' => $this->trim($kid->name),
                    'active' => $active,
                    'visible' => $isactive,
                    'target' => $target,
                    'uri' => $kid->uri,
                    'pages' => $this->addChildPagesForMenus($kid->children, $currentpath, $active, $urlpath, $menuType)
                );


                $pages[] = new Zend_Navigation_Page_Uri($page_options);
            }
        }


        return $pages;
    }

    public function buildNavigationForMenus(Zend_Controller_Request_Abstract $request, $menu_id = null) {

        $this->auth = Zend_Auth::getInstance();

        $this->em = $this->getEntityManager();

        $locale = $this->registry->locale;
        $localeTerritory = explode('_',$this->getBootstrap()->getResource('Locale'));
        if ($menu_id === null) {
            $rootentity = $this->em->getRepository(self::USERMENUS)->getMenuRootNode();
            $menu_id = $rootentity->id;
        }

        $rootpage = $this->em->getRepository(self::USERMENUS)->findOneById($menu_id);

        if ($rootpage) {
//            $this->em->refresh($rootpage);
        } else {
            $container = new Zend_Navigation();
            return $container;
        }


        $num_items_outside_pastseasons = 0;
        $container = new Zend_Navigation();
        $loggedIn = array('log-out', 'change-password', 'sitemap');
        $target = '';



        foreach ($rootpage->children as $page) {

            // check if the item is shoppable in order to skip it
            if ($page->shoppable === true) {
                if ($this->registry->isEcommerce === false) {
                    $num_items_outside_pastseasons++;
                    continue;
                }
            }


            if (!$this->checkIfExclused($page,$localeTerritory) && (!in_array($page->name, $loggedIn) || in_array($page->name, $loggedIn))) {

                $urlpath = substr($request->getPathInfo(), 1);

                $active = $this->checkIfIsSubpath($page->uri, $urlpath);

                if($page->featured){
                    $active=true;
                }
                if ($page->publishstart == null || $page->publishend == null) {
                    continue;
                }

                $now = time();
                if ($now < $page->publishstart->getTimestamp() || $now > $page->publishend->getTimestamp()) {
                    continue;
                }

                //maybe the page we are trying to load is active, but is a child of the current page.
                if ($active == false) {

                    $active = $this->checkIfChildrenContainsPath($page, $urlpath);

                    if ($active == false) {

                        $page_url = str_replace('/', '\/', trim($page->uri, '/'));
                        $urlpth = trim($urlpath, '/');
                        $active = (preg_match('/' . $page_url . '\//', $urlpth)) ? true : false;
                    }
                }


                if ($page->type == 'url') {
                    $target = '_blank';
                }


                if ($page->type != 'url' && !preg_match('/^\//', $page->uri) && !preg_match('/^http:\/\//', $page->uri)) {
                    $uri = '/' . $page->uri;
                } else {
                    $uri = $page->uri;
                }


                //if the menu is the collections menu, we'll need to generate a special kind of menu with a drop down
                //that contains the past seasons.
                if (strtolower($page->name) == 'collections') {

                    //we will show first the newest items, so we get the children ordered by publishstart desc in order to build the menu
                    $children_by_date = $this->em->getRepository(self::USERMENUS)->getOrderedChildren($page->id, 'publishstart', 'desc');
                    $page->children = $children_by_date;

                    $page_options = array(
                        'label' => $this->trim($page->name),
                        'title' => $this->trim($page->name),
                        'uri' => $uri,
                        'target' => $target,
                        'active' => $active,
                        'pages' => $this->addChildPagesForCollectionsMenu($page->children, $page->uri, $active, $urlpath, '')
                    );
                } else {
                    $page_options = array(
                        'label' => $this->trim($page->name),
                        'title' => $this->trim($page->name),
                        'uri' => $uri,
                        'target' => $target,
                        'active' => $active,
                        'pages' => $this->addChildPagesForMenus($page->children, $page->uri, $active, $urlpath, '')
                    );
                }

                $container->addPage(new Zend_Navigation_Page_Uri($page_options));
            }
        }




        return $container;
    }

    private function checkIfExclused($page,$localeTerritory){  
        foreach($page->exclusionList as $country){
            $territory =explode('_', $country->code);
            if(end($territory)==end($localeTerritory)){
                return true;
            }
        }
        return false;
    }

    private function getUrlPathMinLevel($urlpath) {

        $urlpath1 = trim($urlpath, '/');
        $urlpath2 = '/' . $urlpath1;
        $urlpath3 = $urlpath1 . '/';
        $urlpath4 = '/' . $urlpath1 . '/';

        $entities = $this->em->getRepository(self::USERMENUS)->findByUri(array($urlpath1, $urlpath2, $urlpath3, $urlpath4));

        $level = 99; //set level to a max value;
        foreach ($entities as $entity) {
            if ($entity->lvl < $level) {
                $level = $entity->lvl;
            }
        }

        return $level;
    }

    private function checkIfIsSubpath($main_url, $subpath) {

        $main_url = trim($main_url, '/');
        $subpath = trim($subpath, '/');

        $active = false;
        if (strcmp($main_url, preg_replace('/\/([A-Z]|[a-z]|\d|\-|\?|\=)+\/?$/', '', $subpath)) == 0 ||
                strcmp($main_url, preg_replace('/\/([A-Z]|[a-z]|\d|\-|\?|\=)+\/([A-Z]|[a-z]|\d|\-|\?|\=)+\/?$/', '', $subpath)) == 0 ||
                strcmp($main_url, $subpath) == 0) {
            $active = true;
        }

        return $active;
    }

    private function checkIfChildrenContainsPath($page, $urlpath) {

        foreach ($page->children as $child) {

            $child_active = $this->checkIfIsSubpath($child->uri, $urlpath);
            if ($child_active) {

                return true;
            }

            $child_active = $this->checkIfChildrenContainsPath($child, $urlpath);

            if ($child_active == true) {
                return true;
            }
        }

        return false;
    }

    protected function getUrlPathEntity($urlpath) {

        if ($urlpath == '' || $urlpath == '/') {
            return false;
        }

        if (!preg_match('/^\//', $urlpath)) {
            $urlpath = '/' . $urlpath;
        }

        $urlpath1 = trim($urlpath, '/');
        $urlpath2 = '/' . $urlpath1;
        $urlpath3 = $urlpath1 . '/';
        $urlpath4 = '/' . $urlpath1 . '/';

        $urlpath_entity = $this->em->getRepository(self::USERMENUS)->findOneByUri(array($urlpath1, $urlpath2, $urlpath3, $urlpath4));

        if (!isset($urlpath_entity)) {
            $urlpath1 = trim(preg_replace('/(\/\w+)\/?$/', '', $urlpath), '/');
            $urlpath2 = '/' . $urlpath1;
            $urlpath3 = $urlpath1 . '/';
            $urlpath4 = '/' . $urlpath1 . '/';
            $urlpath_entity = $this->em->getRepository(self::USERMENUS)->findOneByUri(array($urlpath1, $urlpath2, $urlpath3, $urlpath4));
        }

        return $urlpath_entity;
    }

    public function buildAdminNavigation(Zend_Controller_Request_Abstract $request, $menuType = 'Main') {
        $this->config = $this->getConfig();
        $this->session = new Zend_Session_Namespace('wedcms');
        if (isset($this->session->siteroot) === false) {
            $this->session->siteroot = $this->config['settings']['application']['siteroot'];
        }
        $siteroot = $this->session->siteroot;
        $this->auth = Zend_Auth::getInstance();
//        $logedIn = $this->auth->hasIdentity();
        $activeID = $request->getParam('id');
        $this->em = $this->getEntityManager();
        $cnf = $this->getConfig();
        $repo = $this->em->getRepository(self::PAGES);
        $rootpage = $this->em->getRepository(self::PAGES)->findOneByUid($siteroot);
        $currentpath = "/admin/";
        $activeTask = $request->getParam('task');
        $activeController = $request->getParam('controller');
        $container = new Zend_Navigation();
        if (isset($rootpage) === false) {
            return $container;
        }
        #Disable pages navigation.
        return $container;

        $page = $rootpage;
        $page_options = array(
            'label' => $this->trim($page->title),
            'title' => $this->trim($page->title),
            'uri' => $currentpath . $page->id,
            'pages' => $this->createEditPage($page, $currentpath, $activeID, $activeTask, $activeController)
        );
        $url = $this->em->getRepository(self::PAGES)->getPageUri($page);
        $resourceName = str_ireplace("/", ":", str_replace('/' . $siteroot, '', $url));
        $found = strpos($resourceName, $siteroot);
//        $this->log->debug('Read ['.$resourceName.'] - '.$found);
        if ($found === true) {
            $resourceName = "mvc:front" . $resourceName;
        } else {
            $resourceName = "mvc:front:" . $siteroot . $resourceName;
        }
        $page_options['privilege'] = 'Update';
        $page_options['resource'] = $resourceName;
        $container->addPage(new Zend_Navigation_Page_Uri($page_options));
        foreach ($rootpage->children as $page) {
            if ($page->template->type != 'pseudo') {
                $urlpath = substr($request->getPathInfo(), 1);
                $active = (strncmp($page->slug, $urlpath, strlen($page->slug)) == 0) ? true : false;
                if (count($page->children) > 0) {
                    $page_options = array(
                        'label' => $this->trim($page->title),
                        'title' => $this->trim($page->title),
                        'uri' => $currentpath . $page->id,
                        'pages' => $this->addAdminChildPages($page->children, $currentpath, $activeID, $urlpath, $menuType)
                    );
                } else {
                    $page_options = array(
                        'label' => $this->trim($page->title),
                        'title' => $this->trim($page->title),
                        'uri' => $currentpath . $page->id,
                        'pages' => $this->createEditPage($page, $currentpath, $activeID, $activeTask, $activeController)
                    );
                }
                $url = $this->em->getRepository(self::PAGES)->getPageUri($page);
                $resourceName = str_ireplace("/", ":", str_replace('/' . $siteroot, '', $url));
                $found = strpos($resourceName, $siteroot);
                //$this->log->debug('Read ['.$resourceName.'] - '.$found);
                if ($found === true) {
                    $resourceName = "mvc:front" . $resourceName;
                } else {
                    $resourceName = "mvc:front"; //:" . $siteroot . $resourceName;
                }
//                $this->log->debug('Read ['.$resourceName.'] - '.$url);
                $page_options['privilege'] = 'Read';
                $page_options['resource'] = $resourceName;
                $container->addPage(new Zend_Navigation_Page_Uri($page_options));
            }
        }
        return $container;
    }

    public function buildAdminNavigationForMenus(Zend_Controller_Request_Abstract $request, $menu_id = null) {

        $config = new Zend_Config_Xml(CONFIG_PATH . DIRECTORY_SEPARATOR . 'adminMenu.xml');
        $config = $config->toArray();

        $this->config = $this->getConfig();
        $this->session = new Zend_Session_Namespace('wedcms');
        if (isset($this->session->siteroot) === false) {
            $this->session->siteroot = $this->config['settings']['application']['siteroot'];
        }

        $this->auth = Zend_Auth::getInstance();

        $activeID = $request->getParam('id');
        $this->em = $this->getEntityManager();

        if ($menu_id === null) {
            $rootentity = $this->em->getRepository(self::USERMENUS)->getMenuRootNode();
            $menu_id = $rootentity->id;
        }


        $rootpage = $this->em->getRepository(self::USERMENUS)->findOneById($menu_id);
        $currentpath = "/admin/";
        $activeTask = $request->getParam('task');
        $activeController = $request->getParam('controller');
        $container = new Zend_Navigation();
        if (isset($rootpage) === false) {
            return $container;
        }
        #Disable pages navigation.


        $page = $rootpage;
        $page_options = array(
            'label' => $this->trim($page->name),
            'title' => $this->trim($page->name),
            'uri' => $currentpath . $page->id,
            'pages' => $this->createEditPageForMenus($page, $currentpath, $activeID, $activeTask, $activeController)
        );
        $url = $page->uri;


        $page_options['privilege'] = 'Update';

        $container->addPage(new Zend_Navigation_Page_Uri($page_options));
        foreach ($rootpage->children as $page) {

            $urlpath = substr($request->getPathInfo(), 1);
            $active = true;
            if (count($page->children) > 0) {


                $uri = (string) $page->id;
                if ($page->name == 'Collections') {
                    $xmlItem = $config[strtoupper($page->name)]['SEASON'];
                } else {
                    $xmlItem = $config[strtoupper($page->name)];
                }

                $page_options = array(
                    'label' => $this->trim($page->name),
                    'title' => $this->trim($page->name),
                    'uri' => '/admin/menuitems/update/' . $page->id,
                    'pages' => $this->addAdminChildPagesForMenus($page->children, $currentpath, $activeID, $urlpath, $xmlItem)
                );
            } else {
                $page_options = array(
                    'label' => $this->trim($page->name),
                    'title' => $this->trim($page->name),
                    'uri' => '/admin/menuitems/update/' . $page->id,
                    'pages' => $this->createEditPageForMenus($page, $currentpath, $activeID, $activeTask, $activeController)
                );
            }
            $url = $page->uri;

            $container->addPage(new Zend_Navigation_Page_Uri($page_options));
        }

        return $container;
    }

    protected function addAdminChildPages($kids, $currentpath, $activeID, $urlpath, $menuType) {
        $pages = array();
        $sess = $this->session;
        $siteroot = $sess->siteroot;
        $this->em = $this->getEntityManager();
        if (isset($kids) === true) {
            foreach ($kids as $kid) {
                $this->log->debug('Admin: ' . $kid->title);
                $privilege = 'Read';
                $visible = ($kid->status == PageEntity::STATUS_PUBLISHED) ? true : false;
                $visible = ($kid->menupos == $menuType) ? $visible : false;
//                $visible = true;
                $testpath = substr($urlpath, strlen($currentpath));
                $active = ($kid->id == $activeID) ? true : false;
                $lgactive = ($active) ? 'true' : 'false';
                $lgvisible = ($visible) ? 'true' : 'false';
                $this->log->debug($kid->status . " [" . $kid->menupos . "==" . $menuType . "]" . $lgvisible . " - " . $kid->template->type);
                switch ($kid->template->type) {
                    case 'aggregate':
                        $aggregatelist = array();
                        foreach ($kid->template->templatevariables as $varible) {
                            #TODO change grabbing options if structure changes
                            if ($varible->type == 'aggregate' && !in_array($varible->type, $aggregatelist)) {
                                array_push($aggregatelist, $varible->options);
                            }
                        }
                        $url = 'items/' . strtolower(substr($aggregatelist[0], 21) . '/read/' . $kid->id); //should only be one but stores them for future imrpovments
                        if (count($aggregatelist) <= 0) {
                            $url = 'pages/update/' . $kid->id;
                        }
                        break;
                    case 'pseudo':
                    case 'partial':
                    default :
                        $url = 'pages/update/' . $kid->id;
                        break;
                }
//                $this->log->debug($currentpath . " : " . (strncmp($kid->slug, $testpath, strlen($kid->slug)) == 0) . ":hmm[" . $testpath . "|" . $kid->slug . "] active:" . $lgactive);
                $page_options = array(
                    'label' => $this->trim($kid->title),
                    'title' => $this->trim($kid->title),
                    'active' => $active,
                    'visible' => $visible,
                    'uri' => $currentpath . $url,
                    'pages' => $this->addAdminChildPages($kid->children, $currentpath /* . '/' . $kid->slug */, $activeID, $urlpath, $menuType)
                );
                $uri = $this->em->getRepository(self::PAGES)->getPageUri($kid);
                $resourceName = str_ireplace("/", ":", str_replace('/' . $siteroot, '', $uri));
                $found = strpos($resourceName, $siteroot);
                if ($found === true) {
                    $resourceName = "mvc:front" . $resourceName;
                } else {
                    $resourceName = "mvc:front"; //:" . $siteroot . $resourceName;
                }
                $this->log->debug($privilege . ' [' . $resourceName . '] - ' . $uri . " {" . $currentpath . $url . "}" . $lgactive . "-" . $lgvisible);
                $page_options['privilege'] = $privilege;
                $page_options['resource'] = $resourceName;
                $pages[] = new Zend_Navigation_Page_Uri($page_options);
            }
        }



        return $pages;
    }

    protected function addAdminChildPagesForMenus($kids, $currentpath, $activeID, $urlpath, $xmlConfig) {

//        die(var_dump($xmlConfig['SEASON']));


        $enityArrayLocation = 'COLLECTION';
        $pages = array();
        $sess = $this->session;
        $siteroot = $sess->siteroot;
        $this->em = $this->getEntityManager();
        if (isset($kids) === true) {
            foreach ($kids as $kid) {

                $xmlElement = $this->getElementChild($kid->name, $xmlConfig);
//                $this->log->debug('++++++++++++++++++++++++++++++++++++++++++++++');
//                $this->log->debug($xmlElement);
                $url = '/admin/menuitems/update/' . $kid->id;
                if (($xmlElement)) {
                    $collection = $this->em->getRepository(self::COLLECTIONS)->findOneBy(array('slug' => $xmlElement['SLUG'], 'year' => $xmlElement['YEAR']));
                    if ($collection)
                        $url = '/admin/manage/collections/update/' . $collection->id;
                }
                $privilege = 'Read';
                $visible = true;
                $testpath = substr($urlpath, strlen($currentpath));
                $active = ($kid->id == $activeID) ? true : false;
                $lgactive = ($active) ? 'true' : 'false';
                $lgvisible = ($visible) ? 'true' : 'false';

                $page_options = array(
                    'label' => $this->trim($kid->name),
                    'title' => $this->trim($kid->name),
                    'active' => $active,
                    'visible' => $visible,
                    'uri' => $url,
                    'pages' => $this->addAdminChildPagesForMenus($kid->children, $currentpath, $activeID, $urlpath, $xmlElement)
                );
                $uri = $kid->uri;

                $pages[] = new Zend_Navigation_Page_Uri($page_options);
            }
        }



        return $pages;
    }

    protected function getElementChild($needle, $hayStack, $type = false) {
        if (is_array($hayStack)) {
            foreach ($hayStack as $child) {
                if (($child['TITLE'] == $needle) || ($hayStack['TITLE'] == $needle)) {
                    return $child;
                    return true;
                } else if (is_array($child)) {
                    $result = $this->getElementChild($needle, $child);
                    if ($result)
                        return $result;
                }
            }
        }
    }

    protected function createEditPageForMenus($page, $currentpath, $isactive, $activeTask, $activeController) {
        $sess = $this->session;
        $siteroot = $sess->siteroot;
        $kids = array('Edit');

        foreach ($kids as $child) {
            $this->log->debug('Edit:' . $child);
            switch ($child) {
                case 'Aggregate':
                    $privilege = "Update";
                    $active = ( $page->id == $isactive && $activeTask == strtolower(substr($aggregate, 21))) ? true : false;
                    $url = 'menuitems/' . strtolower(substr($aggregate, 21) . '/read/' . $page->id); //should only be one but stores them for future imrpovments
                    break;
                case 'Edit':
                default :
                    $privilege = "Delete";
                    $active = ( $page->id == $isactive && $activeController == 'pages') ? true : false;
                    $url = 'menuitems/update/' . $page->id;
                    break;
            }
            $page_options = array(
                'label' => $this->trim($child),
                'title' => $this->trim($child),
                'active' => $active,
                'visible' => true,
                'uri' => $currentpath . $url,
            );
            $url = $page->uri;
            $resourceName = str_ireplace("/", ":", str_replace('/' . $siteroot, '', $url));
            $found = strpos($resourceName, $siteroot);
            if ($found === true) {
                $resourceName = "mvc:front" . $resourceName;
            } else {
                $resourceName = "mvc:front"; //:" . $siteroot . $resourceName;
            }
            $lgactive = ($active) ? 'true' : 'false';
            $lgvisible = 'true'; //($visible) ? 'true' : 'false';
            $this->log->debug($privilege . ' [' . $resourceName . '] - ' . $url . ' [' . $lgactive . ':' . $lgvisible . ']');
            $page_options['privilege'] = $privilege;
            $page_options['resource'] = $resourceName;
            $pages[] = new Zend_Navigation_Page_Uri($page_options);
        }
        return $pages;
    }

    protected function createEditPage($page, $currentpath, $isactive, $activeTask, $activeController) {
        $sess = $this->session;
        $siteroot = $sess->siteroot;
        $kids = array('Edit');
        if ($page->template->type == 'aggregate')
            array_push($kids, 'Aggregate');
        foreach ($kids as $child) {
            $this->log->debug('Edit:' . $child);
            switch ($child) {
                case 'Aggregate':
                    foreach ($page->template->templatevariables as $varible) {
                        #TODO change grabbing options if structure changes
                        if ($varible->type == 'aggregate') {
                            $aggregate = $varible->options;
                        }
                    }
                    $privilege = "Update";
                    $active = ( $page->id == $isactive && $activeTask == strtolower(substr($aggregate, 21))) ? true : false;
                    $url = 'items/' . strtolower(substr($aggregate, 21) . '/read/' . $page->id); //should only be one but stores them for future imrpovments
                    break;
                case 'Edit':
                default :
                    $privilege = "Delete";
                    $active = ( $page->id == $isactive && $activeController == 'pages') ? true : false;
                    $url = 'pages/update/' . $page->id;
                    break;
            }
            $page_options = array(
                'label' => $this->trim($child),
                'title' => $this->trim($child),
                'active' => $active,
                'visible' => true,
                'uri' => $currentpath . $url,
            );
            $url = $this->em->getRepository(self::PAGES)->getPageUri($page);
            $resourceName = str_ireplace("/", ":", str_replace('/' . $siteroot, '', $url));
            $found = strpos($resourceName, $siteroot);
            if ($found === true) {
                $resourceName = "mvc:front" . $resourceName;
            } else {
                $resourceName = "mvc:front"; //:" . $siteroot . $resourceName;
            }
            $lgactive = ($active) ? 'true' : 'false';
            $lgvisible = 'true'; //($visible) ? 'true' : 'false';
            $this->log->debug($privilege . ' [' . $resourceName . '] - ' . $url . ' [' . $lgactive . ':' . $lgvisible . ']');
            $page_options['privilege'] = $privilege;
            $page_options['resource'] = $resourceName;
            $pages[] = new Zend_Navigation_Page_Uri($page_options);
        }
        return $pages;
    }

    public function getEntityManager() {
        if (isset($this->em) === false) {
            return $this->getBootstrap()->getContainer()->get('entity.manager');
        }
        return $this->em;
    }

    private function getConfig() {
        return $this->getBootstrap()->getContainer()->get('config');
    }

    protected function mb_trim($string) {
        $string = preg_replace("/(^\s+)|(\s+$)/us", "", $string);
        return $string;
    }

    protected function html_trim($string) {
        $pattern = '(? \t\n\r\x0B\x00\x{A0}\x{AD}\x{2000}-\x{200F}\x{201F}\x{202F}\x{3000}\x{FEFF}]|&nbsp;|<br\s*\/?>)+';
        return preg_replace('/^' . $pattern . '|' . $pattern . '$/u', '', $string);
    }

    protected function full_trim($string) {
        return trim(html_entity_decode($string), " \t\n\r\0\x0B\xA0");
    }
    
    protected function trim($string) {
        return $this->full_trim($string);
    }

}
