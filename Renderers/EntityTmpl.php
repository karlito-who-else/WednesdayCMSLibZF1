<?php
namespace Wednesday\Renderers;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \Wednesday\Resource\Containers,
    \Wednesday\Resource\Service as ResourceService;

/**
 * Description of ResourceUri
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class EntityTmpl implements Renderer {

    private $_entity;
    private $_options;
    private $_baseuri;
    private $_partial;

    public function __construct($item, $partial = array('template'=>"partials/default.item.phtml")) {
        $this->_entity = $item;
        $this->_options = $partial;
        $this->_partial = $partial['template'];
        #TODO Hookin CDNmanager ()
        $resources = ResourceService::getInstance();
        $this->_baseuri = $resources->getBaseUri('local');        
    }

    public function __toString() {
        return $this->render();
    }

    public function render() {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $bootstrap->view->partialLoop()->setObjectKey('entity');
        $this->log = $bootstrap->getResource('Log');
        $rendered = "";
        $rendered = $bootstrap->view->partial($this->_partial, array('entity' => $this->_entity, 'baseurl' => $this->_baseuri, 'pageuri' => @$this->_options['pageuri']));
        return $rendered;
    }

}