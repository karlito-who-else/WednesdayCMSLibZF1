<?php

namespace Wednesday\Renderers;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \Wednesday\Resource\Containers as ResourceContainers,
    \Wednesday\Resource\Service as ResourceService;

/**
 * Description of ResourceUri
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class ResourceUri implements Renderer {

    private $_resource;
    private $_baseuri;
    private $_basepath;

    public function __construct($resource, $options = false) {
        $this->_resource = $resource;
        $this->_options = $options;
        #TODO Hookin CDNmanager ()
        $resources = ResourceService::getInstance();
        $dest = ($resource->cdn == 1)?'cdn':'local';
        $this->_baseuri = $resources->getBaseUri($dest);
        $this->_basepath = $resources->getBaseUri('local');
    }

    public function __toString() {
        return $this->render();
    }

    public function render() {
//        $resourceuri = $this->_baseuri.ltrim($this->_resource->path,'/').'/'.$this->_resource->name;

        if($this->_resource->type == 'video'){
            $resource_link = $this->_resource->link.'.poster.jpg';
        }else{
            $resource_link = $this->_resource->link;
        }

        $resourceuri = str_replace($this->_basepath, $this->_baseuri, $resource_link);//.ltrim($this->_resource->path,'/').'/'.$this->_resource->name;

        return $resourceuri;
    }

}
