<?php
namespace Wednesday\Renderers;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \Wednesday\Resource\Containers,
    \Wednesday\Resource\Service as ResourceService;

/**
 * Description of EntityVideo
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author venelin
 */
class EntityVideo implements Renderer {

    private $_entity;
    private $_baseuri;

    public function __construct($item) {
        $this->_entity = $item;
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