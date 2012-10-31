<?php
namespace Wednesday\Renderers;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    Doctrine\Common\EventArgs,
    Wednesday\Exception\InvalidArgumentException,
    Wednesday\Exception\InvalidMappingException;

/**
 * Description of TabbedContentRenderer
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author mrhelly
 */
class TabbedContentRenderer implements Renderer {
    //put your code here
    private $_gallery;
    private $_options;
    private $_baseuri;

    public function __construct($gallery, $options) {
        $this->_gallery = $gallery;
        $this->_options = $options;
        #TODO Hookin CDNmanager ()
        $this->_baseuri = "/assets";
    }

    public function __call($methodName, $args)
    {
        echo $methodName . ' called !';
    }

    public function __toString() {
        return $this->render();
    }

    public function render() {
        $rendered = "TabbedContentRenderer: GO!";
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $bootstrap->view->partialLoop()->setObjectKey('entity');
        $this->log = $bootstrap->getResource('Log');
        $items = "";
        foreach ($this->_gallery->resources as $carouselItem) {
            $renderer = new EntityTmpl($carouselItem, $this->_options['item_tpl']);
            $items .= $renderer->render();
        }
        $rendered = $bootstrap->view->partial($this->_options['tmpl'],array('title' => $this->_gallery->title, 'rendered' => $items));
        
        return $rendered;
    }

}
