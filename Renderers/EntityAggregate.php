<?php
namespace Wednesday\Renderers;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \Wednesday\Resource\Containers,
    \Wednesday\Resource\Service as ResourceService;

class EntityAggregate implements Renderer {

    private $_entities;
    private $_baseuri;
    private $_options;

    /**
     *
     * @param type $items
     * @param type $options
     * Array
     *  options['item_tpl'] = ''application->getThemeTemplateUri( (ThemeInstance) $theme, (string) tvar->partial);
     *  options['item_tpl']['tvars']['tvar->title|tvar->id'] = tvar->value;
     *  options['tmpl'] = ''
     *  options['tmpl']['tvars']['tvar->title|tvar->id'] = tvar->value;
     *  options['']
     *  options['']
     *  options['']
     *  options['']
     *  options['']
     */
    public function __construct($items, $options) {
        $this->_entities = $items;
        $this->_options =$options;
        #TODO Hookin CDNmanager ()
        $resources = ResourceService::getInstance();
        $this->_baseuri = $resources->getBaseUri('local');
    }

    public function __toString() {
        return $this->render();
    }

    public function render() {
        $rendered = "CarouselRenderer: GO!";
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $bootstrap->view->partialLoop()->setObjectKey('entity');
        $this->log = $bootstrap->getResource('Log');
        $items = "";
        #TODO Render TVAR aggregate TYPE
        foreach ($this->_entities->resources as $carouselItem) {
            $renderer = new EntityTmpl($carouselItem, $this->_options['item_tpl']);
            $items .= $renderer->render();
        }
        $rendered = $bootstrap->view->partial($this->_options['tmpl'],array('title' => $this->_entities->title, 'baseurl' => $this->_baseuri, 'rendered' => $items));

        return $rendered;
    }

}