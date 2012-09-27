<?php
namespace Wednesday\Renderers;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \ZendX_JQuery_View_Helper_JQuery as JQueryHelper,
    \Wednesday\Resource\Containers as ResourceContainers,
    \Wednesday\Resource\Service as ResourceService;

/**
 * Description of ResourceVideo
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author venelin
 */
class ResourceVideo implements Renderer {

    private $_projekktor = 'Wednesday\Renderers\ResourceVideoProjekktor';
    private $_videoJS = 'Wednesday\Renderers\ResourceVideoJS';
    private $_resource;
    private $_baseuri;
    private $_basepath;
    private $_options;
    private $_defaultspath;


    public function __construct($resource, $options = false) {
        $this->_resource = $resource;
        $this->_options = $options;
    }

    public function __toString() {
        return $this->render();
    }

    public function render() {
        $rendererType;
        switch($this->_options['videoPlayer'])
        {
            case "videoJS":
                $rendererType = $this->_videoJS;
                break;
            case "projekktor":
            default :
                $rendererType = $this->_projekktor;
                break;
        }
        
        $_renderer = new $rendererType($this->_resource,$this->_options);
        $html = $_renderer->render();
        return $html;
        
    }
}
