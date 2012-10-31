<?php
//namespace Wednesday\View\Helper;

use \Zend_Controller_Front as Front,
    \Doctrine\Common\Collections\ArrayCollection,
    \Zend_View_Helper_Abstract as ViewHelperAbstract;

/**
 * Description of Resource
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_View_Helper_EntityRenderer extends ViewHelperAbstract {

    protected $_renderType = 'Wednesday\Renderers\EntityHtml';
    protected $_renderer = null;
    protected $_ent = null;
    protected $_res = null;
    protected $_dua = null;
    protected $_options = array();

    /**
     *
     * @return type
     */
    public function entityRenderer() {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');

//        $this->log->debug(func_get_args());
        $params = $this->parseParams(func_get_args());
//        $this->log->debug($this->_options);

        $rendered = "";//"{$params}";
        if (isset($this->_options['render'])) {
            switch ($this->_options['render']) {
                case 'uri':
                    $this->_renderType = "Wednesday\Renderers\EntityText";
                    break;
                case 'html':
                    $this->_renderType = "Wednesday\Renderers\EntityHtml";
                    break;
                case 'tmpl':
                    $this->_renderType = "Wednesday\Renderers\EntityTmpl";
                    break;
                case 'list':
                    $this->_renderType = "Wednesday\Renderers\EntityAggregate";
                    break;
                case 'grid':
                    $this->_renderType = "Wednesday\Renderers\GridRenderer";
                    break;
            }
        }
        $this->log->debug($this->_renderType);
//        $this->log->debug($params);
//        echo "::".get_class($this->_ent)."<br />\n";
//        var_dump($this->_options);
//        die();
        //$this->log->debug($this->_options);
        $this->_renderer = new $this->_renderType($this->_ent, $this->_options);
        return $this->_renderer->render();
    }

    /**
     *
     * @param type $params
     * @return type
     */
    protected function parseParams($params) {
        $retval = "";

        foreach ($params as $key => $param) {
//            $cls = get_class($param);
//            $this->log->debug("{$key} => {$cls}");
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
                    $paramtype = "(array) ";// . print_r($param, true);
                    break;
                case 'Proxies\ApplicationEntitiesResourcesProxy':
                case 'Proxies\__CG__\Application\Entities\Resources':
                case 'Application\Entities\Resources':
                    $this->_res = $param;
                    $paramtype = $param->link;
                    break;
                case 'Doctrine\Common\Collections\ArrayCollection':
                case 'Doctrine\ORM\PersistentCollection':
                    $this->_options[$key] = $param;
                    break;
                case substr_compare($this->_checkType($param),'Proxies\ApplicationEntities',0,strlen('Proxies\ApplicationEntities')):
                case substr_compare($this->_checkType($param),'Proxies\__CG__\Application\Entities',0,strlen('Proxies\__CG__\Application\Entities')):
                case substr_compare($this->_checkType($param),'Application\Entities\\',0,strlen('Application\Entities')):
                    $paramtype = "(known) " . $this->_checkType($param);
//                    echo "-:".get_class($param)."<br />\n";
                    if(is_numeric($key)) {
//                        echo "-:ent<br />\n";
                        $this->_ent = $param;
                    } else {
                        if( (isset($this->_ent)===false) )   {
                            // || ( is_object($this->_ent) && is_object($param) && ($this->_ent->id != $param->id) )
//                            echo "::ent<br />\n";
                            $this->_ent = $param;
                        } else {
//                            echo "-:opt<br />\n";
                            $this->_options[$key] = $param;
                        }
                    }
                    break;
                default:
                    $paramtype = "(unknown) " . $this->_checkType($param);
                    break;
            }
            $retval .= ":: {$key} => {$paramtype} <br />";
            $this->log->debug(":: {$key} => {$paramtype}");
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
