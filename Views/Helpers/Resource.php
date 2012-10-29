<?php

//namespace Wednesday\View\Helper;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    Application\Entities\Resources,
    Wednesday\Renderers\ResourceUri,
    Wednesday\Renderers\ResourceHtml,
    Wednesday\Exception\RuntimeException,
    Wednesday\Exception\InvalidMappingException,
    Wednesday\Exception\UnexpectedValueException,
    Wednesday\Exception\InvalidArgumentException;

/**
 * Description of Resource
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_View_Helper_Resource extends ViewHelperAbstract {

    protected $_renderType = 'Wednesday\Renderers\ResourceUri';
    protected $_renderer = null;
    protected $_res = null;
    protected $_dua = null;
    protected $_options = array();

    /**
     *
     * @return type
     */
    public function resource() {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->config = $bootstrap->getContainer()->get('config');
        $this->log = $bootstrap->getResource('Log');

        #Load WURFL data.
        $userAgent = $bootstrap->getResource('useragent');

        $this->dua = $userAgent->getDevice();
        $is_mobile = $this->dua->getFeature('is_mobile');
        $is_desktop = $this->dua->getFeature('is_desktop');
        $device = $this->dua->getFeature('device');

//        $is_bot		= $this->dua->getFeature('is_bot');
//        $maxWidth     = $this->dua->getMaxImageWidth();
//        $maxHeight	= $this->dua->getMaxImageHeight();
        #Force Size to be set based on the WURFL data.
        $size = ($is_mobile) ? 'mobile' : 'desktop';
        $size = (($is_mobile) && ($device == 'ipad')) ? 'ipad' : $size;
        $this->_options['size'] = $size;

        $params = $this->parseParams(func_get_args());

        if (!isset($this->_res)) {
            return 'Unable to find valid Resource entity';
//            throw new InvalidArgumentException("Unable to find valid Resource entity");
        }

        if (isset($this->_options['size'])) {

            if($is_mobile) {
                /**
                 * Determinate the mobile band
                 *
                 * Loop through the available mobile bands and try to determinate
                 * the proper one, depends on the device max image size
                 */
                foreach($this->config['settings']['application']['asset']['manager']['mobileband'] as $mb){
                    /**
                     * If a proper mobile variation band was found, build its name and append it to
                     * the standard requested variation:
                     * homepagelarge -> homepagelarge.mobile-0-320
                     *
                     * Assign the variation to the selected resource model, which will check to the
                     * variation image exists or the default variation should be used.
                     *
                     * Also, exit the loop as there is no need to continue
                     */
                    if($this->dua->getMaxImageWidth() >= $mb['width_from'] && $this->dua->getMaxImageWidth() <= $mb['width_to']){

                        $this->_res->setVariation($this->_options['size'].'-mobile-'.$mb['width_from'].'-'.$mb['width_to']);

                        break;
                    }
                }
            }

            /**
             * There are two cases, in which the following foreach loop will be executed:
             * 1. If a mobile device is used, but no proper mobile variarion was found - fallback to desktop
             * 2. Desktop computer or iPad was used.
             *
             * Grab the standard image variation, e.g. v.homepagelarge.img123.jpg
             */
            $this->_res->setVariation($this->_options['size']);
//            foreach ($this->_res->metadata as $meta) {
//                if ($meta->title == $this->_options['size']) {
//                    $em = $bootstrap->getContainer()->get('entity.manager');
//                    $variation = $em->getRepository($meta->type)->findOneById($meta->content);
//                    $em->detach($this->_res);
//                    $this->_res->link = $variation->link;
//                    break;
//                }
//            }
        }

        if (isset($this->_options['renderer'])) {
            switch ($this->_options['renderer']) {
                case 'uri':
                    $this->_renderType = "Wednesday\Renderers\ResourceUri";
                    break;
                case 'html':
                    $this->_renderType = "Wednesday\Renderers\ResourceHtml";
                    break;
                case 'video':
                    $this->_renderType = "Wednesday\Renderers\ResourceVideo";
                    break;
            }
        }
        $this->_renderer = new $this->_renderType($this->_res,$this->_options);
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

            $paramtype = $this->_checkType($param);
//            $this->log->info($key.' '.$this->_checkType($param));
            switch ($this->_checkType($param)) {
                case 'boolean':
                    if (!is_numeric($key)) {
                        $this->_options[$key] = $param;
                    }
                    $paramtype = "(boolean) " . $param;
                    break;
                case 'number':
//                    $this->log->info($param.' '.$this->_checkType($param));
                    if (!is_numeric($key)) {
                        $this->_options[$key] = $param;
                    } else if ($key == 'id') {
                        $bootstrap = Front::getInstance()->getParam('bootstrap');
                        $em = $bootstrap->getContainer()->get('entity.manager');
                        $this->_res =  $em->getRepository('Application\Entities\MediaResources')->findOneById($param);
                    }
                    $paramtype = "(number) " . $param;
                    break;
                case 'string':
                    if (!is_numeric($key)) {
                        $this->_options[$key] = $param;
                    }
                    $paramtype = "(string) " . $param;
                    break;
                case 'Proxies\ApplicationEntitiesResourcesProxy':
                case 'Proxies\__CG__\Application\Entities\Resources':
                case 'Application\Entities\Resources':
                case 'Proxies\__CG__\Application\Entities\MediaResources':
                case 'Application\Entities\MediaResources':
//                    $this->log->info('Set Resource : '.$param->id);
                    $this->_res = $param;
                    $paramtype = $param->link;
                    break;
                case 'array':
                    if (!is_numeric($key)) {
                        $this->_options[$key] = $param;
                    } else {
                        $this->parseParams($param);
                    }
                    $paramtype = "(array) " . print_r($param, true);
                    break;
                default:
                    $paramtype = "(unknown) " . $this->_checkType($param);
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

    /**
     *
     * @param type $Mimetype
      protected function getRenderer($Mimetype = 'image/jpg') {

      }
     */
}