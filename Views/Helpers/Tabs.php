<?php

//namespace Wednesday\View\Helper;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract;

/**
 * Description of Resource
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_View_Helper_Tabs extends ViewHelperAbstract {

    protected $_renderType = 'Wednesday\Renderers\TabsRenderer';
    protected $_renderer = null;
    protected $_ent = null;
    protected $_res = null;
    protected $_dua = null;
    protected $_options = array();

    /**
     *
     * @return type
     */
    public function tabs() {

        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');
        $this->parseParams(func_get_args());
        $rendered = "";
        $this->log->info($this->_renderType . " " . $this->_options['tmpl']);
//        $this->log->err(get_class($this->_ent) . "::");
        $this->_renderer = new $this->_renderType($this->_ent, $this->_options);
        return $rendered . $this->_renderer->render();
    }

    /**
     *
     * @param type $params
     * @return type
     */
    protected function parseParams($params) {
//        $retval = "";
        #todo both indexes in array is an array, so find a better way to find out which index is the objects and which is the options
        //hard coded for now,
        $this->_ent = $params[0];
        $this->_options = $params[1];
//        return $retval;
    }

}