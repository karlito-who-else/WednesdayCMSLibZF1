<?php

//namespace Wednesday\View\Helper;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \Zend_View_Helper_Partial as ViewHelperPartial,
    \Zend_Controller_Action_HelperBroker as ActionHelperBroker,
    \Zend_Paginator as Paginator;

/**
 * Description of Resource
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_View_Helper_Template extends ViewHelperAbstract {
    const ARTICLES = 'Application\Entities\ArticleItems';
    const CONTACTS = 'Application\Entities\ContactItems';
    const DOWNLOADS = 'Application\Entities\DownloadItems';
    const JOBS = 'Application\Entities\JobItems';
    const NEWS = 'Application\Entities\NewsItems';
    const PRESS = 'Application\Entities\PressItems';
    const PAGES = "Application\Entities\Pages";
    const BRANDS = "Application\Entities\Brands";
    const CATEGORIES = "Wednesday\Models\Categories";
    const TEMPLATES = 'Application\Entities\Templates';
    const HCARDS = 'Application\Entities\Hcards';
    const PAGE = 'Application\Entities\Pages';
    const ENTITY_NAMESPACE  = "Application\Entities\\";
    const WEDMODEL_NAMESPACE  = "Wednesday\Models\\";
    const ThemeViewHelper = 'ThemedViews';
    const ITEMS_PER_PAGE = 5;

    /**
     *
     * @var Application\Entities\Pages
     */
    protected $page;

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

    public function tvarRenderer($route, $tvarcont, $requestargs = false) {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');
        $this->log->debug(get_class($this) . " tvarRenderer()");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $pagerepo = $em->getRepository(self::PAGE);
        //$this->log->debug($route);
        $rendered = "";
        switch ($tvarcont->contentvariable->type) {
            case 'entity': 
                //$this->log->debug("template: " . $this->template->type);
                if (($this->template->type == 'resource') && (!is_numeric($tvarcont->value))) {
                    $slug = $route[floor(count($route) - 1)];
                    $ent = $em->getRepository($tvarcont->value)->findOneBySlug($slug);
                    $this->log->debug("find(5): " . $tvarcont->contentvariable->options . " - " . $this->template->name);
                    $this->view->pageuri = $pagerepo->getPageUri($tvarcont->page->parent);
                    $this->log->debug($this->view->pageuri);
                    switch ($route[0]) {
                        case 'press-releases':
                            $next=$em->getRepository($tvarcont->value)->getNeightbourItem($ent->publishstart->format('Y-m-d'),'desc');
                            $pri=$em->getRepository($tvarcont->value)->getNeightbourItem($ent->publishstart->format('Y-m-d'),'desc','after');
                            $this->view->previousItem = $pri->slug;
                            $this->view->nextItem= $next->slug;
                            break;
                    }
                } else {
                    $ent = $em->getRepository($tvarcont->contentvariable->options)->find($tvarcont->value);
                    if($ent instanceof Application\Entities\Widgets) {
                        $ent = $this->mapWidgets($em,$ent);
                    }
                }
                $rendered = $ent;
                break;
            case 'aggregate':
                //$this->log->debug($this->template->type . "::" . $tvarcont->contentvariable->options . ":" . $tvarcont->value);
                if ($this->template->type == 'aggregate') {
                    #TODO Get type of find.
                    $this->log->debug($tvarcont->contentvariable->title . " -{ tvar value: '" . $tvarcont->value . "' }-");
                    $entNameSpace = (is_array($tvarcont->contentvariable->options))?$tvarcont->contentvariable->options['ns']:$tvarcont->contentvariable->options;
                    $pagination = (is_array($tvarcont->contentvariable->options))?($tvarcont->contentvariable->options['pagination']=="true")?true:false:false;
                    $filters = $this->getEntityFilters($entNameSpace);
                    $criteria = $this->getEntityCriteria($entNameSpace, $tvarcont->value, $requestargs);
                    $this->log->debug($entNameSpace . ", pagination: " . $pagination . "");
                    switch ($entNameSpace) {
                        case self::NEWS:
                            $ent = $em->getRepository($entNameSpace)->getCategorisedItems(
                                $_GET['category'],
                                array(
                                        'Year'=>$_REQUEST['year'],
                                        'Month'=>$_REQUEST['month']
                                    ),
                                    'publishstart',
                                    'desc'
                                );
                            break;
                        case self::CONTACTS:
                        case self::JOBS:
                            $this->log->info($_GET);
//                            $this->log->info($_REQUEST);
//                            $this->log->info($filters);
//                            $this->log->info($criteria);
                            $this->log->err("ITEMS::".$entNameSpace);
                            $categories = array();
                            //This doesn't exist right?
                            $categories = explode(",",$tvarcont->page->listCategories(true));
                            $this->log->info($categories);
                            $filters->Categories = $categories;
                            $ent = $em->getRepository($entNameSpace)->getFilteredItems($filters);
                            break;
                        case self::PRESS:
                        case self::ARTICLES:
                        case self::DOWNLOADS:
                            $this->log->info($_GET);
                            $this->log->info($_REQUEST);
                            $this->log->err("ITEMS::".$entNameSpace);
                            $categories = array();
                            //This doesn't exist right?
                            $categories = explode(",",$tvarcont->page->listCategories(true));
                            $this->log->info($categories);
                            $ent = $em->getRepository($entNameSpace)->getCategorisedItems($categories,array('Year'=>$_REQUEST['year'],'Month'=>$_REQUEST['month']),'publishstart','desc');
                            break;
                        case self::HCARDS:
                        case self::PAGES:
                            $ent = $em->getRepository($entNameSpace)->getInOrder(explode(',', $tvarcont->value));
                            break;
                        case self::CATEGORIES:
                            $ent = $em->getRepository($entNameSpace)->findBy($criteria, array($filters->orderBy => $filters->orderDir));
                            break;
                        default:
                            break;
                    }
                }
                $this->log->debug(count($ent));
                //TODO how to handle pagination?
                if($pagination) {
                    $rendered = $this->paginator($ent);
                } else {
                    $rendered = $ent;
                }
//                $this->log->debug(get_class($rendered));
                break;
            case 'list':
                //$ent = $em->getRepository($tvarcont->contentvariable->options)->findBy(array('id' => explode(",", $tvarcont->value)));
                //$rendered = $ent;
                $rendered = $tvarcont->value;
                break;
            case 'static':
                //$this->log->debug($tvarcont->contentvariable->options);
                $rendered = $tvarcont->value;
                break;
            case 'form':
            default:
                $rendered = $tvarcont->value;
                break;
        }
        return $rendered;
    }


    protected function mapWidgets($em, Application\Entities\Widgets $widget) {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $locale = $bootstrap->getResource('Locale');
        $this->log = $bootstrap->getResource('Log');
//        $metafactory = $em->getMetadataFactory();
        $widgetContents = array();//0=>array()
        $count = 1;
        foreach ($widget->items as $item) {
            if((strpos($item->objectClass, self::ENTITY_NAMESPACE)===false)&&(strpos($item->objectClass, self::WEDMODEL_NAMESPACE)===false)) {
                $this->log->debug("[{$item->type}]Class: ".$item->objectClass);
                $mappedItem = new $item->objectClass();                
            } else {
                $this->log->debug("[{$item->type}]Entity: ".$item->objectClass);
                $mappedItem = $em->getRepository($item->objectClass)->find($item->content);
            }
            if (method_exists($mappedItem,'setTranslatableLocale')) {
                $mappedItem->setTranslatableLocale($locale);
                $em->refresh($mappedItem);
            }
            $content = array();
            $content['title'] = $item->title;
            $content['type'] = $item->type;
            $content['content']= $mappedItem;
//            array_push($widgetContents, $content);
            $widgetContents[$count++] = $content;
            unset($content);
        }
        return $widgetContents;
    }

    protected function paginator($items, $pageNumber = 1) {
        if (isset($_GET['page']))
            $pageNumber = $_GET['page']; //$this->getRequest()->getParam('page');
        $paginator = Paginator::factory($items);
        $paginator->setCurrentPageNumber($pageNumber);
        $paginator->setItemCountPerPage(self::ITEMS_PER_PAGE);

        $this->view->currentItemCount = $paginator->getCurrentItemCount();
        $this->view->totalItemCount = $paginator->getTotalItemCount();
        $this->view->articles = $paginator;
    }

    public function getPage($pageuid=null) {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');
        $em = $bootstrap->getContainer()->get('entity.manager');
        if (isset($pageuid) === true && ($this->page->uid == $pageuid)) {
            return $this->page;
        } else if (isset($pageuid) === true) {
            $this->page = $em->getRepository(self::PAGE)->findOneByUid($pageuid);
        } else {
            return false;
        }
        $this->log->debug($this->page->uid);
        return $this->page;
    }

    public function setPage($pageuid) {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $em = $bootstrap->getContainer()->get('entity.manager');
        $this->page = $em->getRepository(self::PAGE)->findOneByUid($pageuid);
        return $this;
    }

    public function getTemplate($tmplid=null) {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');
        $em = $bootstrap->getContainer()->get('entity.manager');
        if (isset($tmplid) === true && ($this->template->id == $tmplid)) {
            return $this->template;
        } else if (isset($pageuid) === true) {
            $this->template = $em->getRepository(self::TEMPLATES)->findOneByUid($tmplid);
        } else {
            return false;
        }
        $this->log->debug($this->template->id);
        return $this->template;
    }

    public function setTemplate($tmplid) {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');
        $em = $bootstrap->getContainer()->get('entity.manager');
        $this->template = $em->getRepository(self::TEMPLATES)->findOneById($tmplid);
        return $this;
    }

    public function getTheme() {
        $helper = self::ThemeViewHelper;
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');
        $layoutplugin = ActionHelperBroker::getHelper($helper);
        $this->log->debug($layoutplugin);
        return $layoutplugin;
    }

    public function setTheme($theme) {
        $this->theme = $theme;
        return $this;
    }

    /**
     *
     * @return type
     */
    public function template() {
        return $this;
    }

    /**
     *
     * @return type
     */
    public function setOptions($options) {
        return $this;
    }

    /**
     *
     * @param type $pageuid
     * @return type
     */
    protected function getEntityFilters($class) {
        $this->log->debug(get_class($this) . " getEntityFilters()");
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $em = $bootstrap->getContainer()->get('entity.manager');
        $filters = (object) array('orderBy' => 'id', 'orderDir' => 'DESC');
        switch ($class) {
            case self::JOBS:
                $jobtypes = $em->getRepository("\Application\Entities\JobTypes")->findAll();
                $departments = $em->getRepository("\Application\Entities\ContactFields")->findAll();
                $locations = $em->getRepository("\Application\Entities\ContactLocations")->findAll();
                $this->view->itemFilters = (object) array(
                    0 => (object) array( 
                            'id' => 1001,
                            'title' => 'Department',
                            'children' => $departments
                        ),
                    1 => (object) array( 
                            'id' => 1002,
                            'title' => 'Type',
                            'children' => $jobtypes
                        ),
                    2 => (object) array( 
                            'id' => 1003,
                            'title' => 'Location',
                            'children' => $locations
                        ),
                );
                $filtered = array();
                $links = array(
                    1001 => 'department',
                    1002 => 'jobtype',
                    1003 => 'location'
                );
                foreach($_GET['category'] as $catid => $val) {
                    if(is_numeric($val)&&($val != -1)) {
                        $filtered[$links[$catid]] = $val;
                    }
                }
                $filters = (object) array(
                    'Categories' => '0',
                    'Filter' => $filtered,
                    'Year'=>$_REQUEST['year'],
                    'Month'=>$_REQUEST['month'],
                    'orderBy' => 'publishstart', 
                    'orderDir' => 'DESC'
                    );
                break;
            case self::CONTACTS:
                $fields = $em->getRepository("\Application\Entities\ContactFields")->findAll();
                $locations = $em->getRepository("\Application\Entities\ContactLocations")->findAll();
                $brands = $em->getRepository("\Application\Entities\Brands")->findAll();
                $this->view->itemFilters = (object) array(
                    0 => (object) array( 
                            'id' => 1001,
                            'title' => 'Company',
                            'children' => $brands
                        ),
                    1 => (object) array( 
                            'id' => 1002,
                            'title' => 'Fields',
                            'children' => $fields
                        ),
                    2 => (object) array( 
                            'id' => 1003,
                            'title' => 'Location',
                            'children' => $locations
                        )
                );
                $filtered = array();
                $links = array(
                    1001 => 'brand',
                    1002 => 'field',
                    1003 => 'location'
                );
                foreach($_GET['category'] as $catid => $val) {
                    if(is_numeric($val)&&($val != -1)) {
                        $filtered[$links[$catid]] = $val;
                    }
                }
                $filters = (object) array(
                    'Categories' => '0',
                    'Filter' => $filtered,
                    'Year'=>$_REQUEST['year'],
                    'Month'=>$_REQUEST['month'],
                    'orderBy' => 'publishstart', 
                    'orderDir' => 'DESC'
                    );
                break;
            case self::DOWNLOADS:
                $filters = (object) array('orderBy' => 'publishstart', 'orderDir' => 'DESC');
                break;
            case self::ARTICLES:
                $filters = (object) array('orderBy' => 'publishstart', 'orderDir' => 'DESC');
                break;
            case self::NEWS:
            case self::PRESS:
                $filters = (object) array('orderBy' => 'publishstart', 'orderDir' => 'DESC');
                break;
            case self::PAGES:
            case self::CATEGORIES:
                $filters = (object) array('orderBy' => 'lvl', 'orderDir' => 'DESC');
                break;
            case self::BRANDS:
            default:
                $filters = (object) array('orderBy' => 'id', 'orderDir' => 'DESC');
                break;
        }
        return $filters;
    }

    /**
     *
     * @param type $pageuid
     * @return type
     */
    protected function getEntityCriteria($class, $tvarvalue, $args) {
        if ($tvarvalue != -1) {
            return array('id' => explode(",", $tvarvalue));
        }
        $criteria = array();
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');
        $this->log->debug($args);

        switch ($class) {
            case self::ARTICLES:
            case self::CONTACTS:
            case self::DOWNLOADS:
            case self::JOBS:
            case self::NEWS:
            case self::PRESS:
            case self::PAGES:
            case self::CATEGORIES:
            case self::BRANDS:
            default:
                $criteria = array();
                break;
        }
        $this->log->debug('Criteria:' . $class);
        $this->log->debug($criteria);
        return $criteria;
    }
}