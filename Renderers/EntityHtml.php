<?php
namespace Wednesday\Renderers;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \Wednesday\Resource\Containers,
    \Wednesday\Resource\Service as ResourceService;

/**
 * Description of ResourceUri
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class EntityHtml implements Renderer {

    private $_entity;
    private $_options;
    private $_baseuri;

    public function __construct($item, $options = false) {
        $this->_entity = $item;
        $this->_options = $options;
        #TODO Hookin CDNmanager ()
        $resources = ResourceService::getInstance();
        $this->_baseuri = $resources->getBaseUri('local');
    }

    public function __toString() {
        return $this->render();
    }

    public function render() {
        $rendered = "";
        if(method_exists($this->_entity,'toHtml')===true){
            $rendered = $this->_entity->toHtml();
        } else {
            $rendered = print_r($this->_entity->toArray(),true);
        }
        return $rendered;
    }

}