<?php
//namespace Wednesday\View\Helper;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract;

/**
 * Description of Resource
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author jamesh
 * 
 */
class Wednesday_View_Helper_Carousel extends ViewHelperAbstract {

    protected $_renderType = 'Wednesday\Renderers\CarouselRenderer';
    protected $_renderer = null;
    protected $_ent = null;
    protected $_res = null;
    protected $_dua = null;
    protected $_options = array();

    /**
     *
     * @return type
     */
    public function carousel() {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');
        $params = $this->parseParams(func_get_args());
        $rendered = "";
        $this->log->debug($this->_renderType." ".$this->_options['tmpl']);
        $this->_renderer = new $this->_renderType($this->_ent, $this->_options);
        return $rendered.$this->_renderer->render();
    }

    /**
     *
     * @param type $params
     * @return type
     */
    protected function parseParams($params) {
        $retval = "";

        foreach ($params as $key => $param) {
            $paramtype = $this->_checkType($param);
            switch ($this->_checkType($param)) {
                case 'boolean':
                    if (!is_numeric($key)) {
                        $this->_options[$key] = $param;
                    }
                    $paramtype = "(boolean) " . $param;
                    break;
                case 'number':
                    if (!is_numeric($key)) {
                        $this->_options[$key] = $param;
                    }
                    $paramtype = "(number) " . $param;
                    break;
                case 'string':
                    if (!is_numeric($key)) {
                        $this->_options[$key] = $param;
                    }
                    $paramtype = "(string) " . $param;
                    break;
                case 'array':
                    if (!is_numeric($key)) {
                        $this->_options[$key] = $param;
                    } else {
                        $this->parseParams($param);
                    }
                    $paramtype = "(array) ";
                    // . print_r($param, true);
                    break;
                case 'Proxies\ApplicationEntitiesResourcesProxy':
                case 'Proxies\__CG__\Application\Entities\Resources':
                case 'Application\Entities\Resources':
                    $this->_res = $param;
                    $paramtype = $param->link;
                    break;
                case substr_compare($this->_checkType($param),'Proxies\__CG__\Application\Entities\\',0,strlen('Proxies\__CG__\Application\Entities')):
                case substr_compare($this->_checkType($param),'Application\Entities\\',0,strlen('Application\Entities')):
                    $paramtype = "(known) " . $this->_checkType($param);
                    $this->_ent = $param;
                    break;
                default:
                    $paramtype = "(unknown) " . get_class($param) . " " . $this->_checkType($param);
                    break;
            }
            $retval .= ":: {$key} => {$paramtype} <br />";
            $this->log->debug($retval);
        }
        return $retval;
    }

    /**
     *
     * @param type $variable
     * @return type
     */
    private function _checkType($variable) {
        /*
          is_resource()
          is_scalar()
         */
        if (is_null($variable) === true) {
            return false;
        } else if (is_bool($variable) === true) {
            return "boolean";
        } else if ((is_numeric($variable) === true) || (is_float($variable) === true) || (is_int($variable) === true)) {
            return "number";
        } else if (is_string($variable) === true) {
            return "string";
        } else if (is_array($variable) === true) {
            return "array";
        } else if (is_object($variable) === true) {
            return get_class($variable);
        }
        return false;
    }
}