<?php

//namespace Wednesday\View\Helper;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \Wednesday\Renderers\SiteMapRenderer;

/**
 * Description of Resource
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_View_Helper_SiteMap extends ViewHelperAbstract {

    protected $_renderType = 'Wednesday\Renderers\SiteMapRenderer';
    protected $_renderer = null;
    protected $_ent = null;
    protected $_res = null;
    protected $_dua = null;
    protected $_options = array();

    /**
     *
     * @return type
     */
    public function siteMap($page = false) {

        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');
        $this->log->err("Got:  " . get_class($page));
        $sitemapRenderer = new SiteMapRenderer($page);
        return $sitemapRenderer->renderChildren($page);
    }

}