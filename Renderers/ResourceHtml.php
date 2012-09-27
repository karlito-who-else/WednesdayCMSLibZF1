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
class ResourceHtml implements Renderer {

    private $_resource;
    private $_baseuri;
    private $_basepath;
    private $_options;
    private $_defaultspath;

	
    public function __construct($resource, $options = false) {
        $this->_resource = $resource;
        $this->_options = $options;
        #TODO Hookin CDNmanager ()
        $resources = ResourceService::getInstance();
        $dest = ($resource->cdn == 1)?'cdn':'local';
        $this->_baseuri = $resources->getBaseUri($dest);
        $this->_basepath = $resources->getBaseUri('local');
		$this->_defaultspath = '/themes/admin/img/custom/';
    }

    public function __toString() {
        return $this->render();
    }

    public function render() {
        $rendered = "";
        $data = 'data-id="'.$this->_resource->id.'" ';
        $class = (isset($this->_options['class'])===true)?'class="'.$this->_options['class'].'" ':"";
        $id = (isset($this->_options['id'])===true)?'id="'.$this->_options['id'].'" ':"";
        $dims = (isset($this->_options['width'])===true)?'width="'.$this->_options['width'].'" ':"";
        $dims .= (isset($this->_options['height'])===true)?'height="'.$this->_options['height'].'" ':"";
        $resourceuri = str_replace($this->_basepath, $this->_baseuri, $this->_resource->link);
        //switch($this->_resource->mimetype) {
		
        //for all non images files we would need to load default images depending on the file mimetype
        if (strpos($this->_resource->mimetype, 'image') === false)
        {		
            //generic: all audio mimetypes
            if (strpos($this->_resource->mimetype, 'audio') !== false)
            {
                $resourceuri = $this->_defaultspath . 'icon-audio.png';
            }
            //generic: all video mimetypes
            elseif (strpos($this->_resource->mimetype, 'video') !== false)
            {
                $resourceuri = $this->_defaultspath . 'icon-video.png';
            }
            else //specifical types
            {
                switch($this->_resource->mimetype) {
                    case 'application/pdf':
                        $resourceuri = $this->_defaultspath . 'icon-pdf.png';
                        break;
                    case 'application/msword':
                        $resourceuri = $this->_defaultspath . 'icon-word.png';
                        break;
                    case 'application/vnd.ms-powerpoint':
                        $resourceuri = $this->_defaultspath . 'icon-ppt.png';
                        break;
                    case 'application/vnd.ms-excel':
                        $resourceuri = $this->_defaultspath . 'icon-excel.png';
                        break;
                    default:
                        $resourceuri = $this->_defaultspath . 'icon-doc.png';
                    break;
                }
            }
        }
		
    	$rendered = '<img '.$id.$class.$data.$dims.'src="'.$resourceuri.'" alt="'.$this->_resource->title.'" title="'.$this->_resource->title.'" />';
        return $rendered;
    }
}
