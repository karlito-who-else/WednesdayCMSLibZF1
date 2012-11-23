<?php

namespace Wednesday\Renderers;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \Wednesday\Resource\Containers as ResourceContainers,
    \Wednesday\Resource\Service as ResourceService;

/**
 * Description of tabContentRenderer
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author mrhelly
 */
class TabsRenderer implements Renderer {

    //put your code here
    private $_tabs;
    private $_options;
    private $_baseuri;

    public function __construct($tabs, $options) {
        $this->_tabs = $tabs;
        $this->_options = $options;
        #TODO Hookin CDNmanager ()
        $resources = ResourceService::getInstance();
        $this->_baseuri = $resources->getBaseUri('local');
    }

    public function __call($methodName, $args)
    {
        echo $methodName . ' called !';
    }

    public function __toString() {
        return $this->render();
    }

    public function render() {
        $rendered = "tabContentRenderer: GO!";
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $bootstrap->view->partialLoop()->setObjectKey('entity');
        $this->log = $bootstrap->getResource('Log');
        $items = "";
        $header = '';
        $content = '';

        $i=0;
        foreach ($this->_tabs as $tabContentItem) 
        {
            $i++;
            $header .= $bootstrap->view->partial($this->_options['tabHead'], array('num' => $i,'title' => $tabContentItem['title']));
            switch(get_class($tabContentItem['content']))
            {
                case 'Application\Entities\MediaGalleries' :
                {
                    $content .= $bootstrap->view->partial($this->_options['tabGallery'],array('num' => $i, 'item' => $tabContentItem['content'],'tabTitle'=>$tabContentItem['title']));
                    break;
                }
                case 'Application\Entities\MediaResources' :
                {
                    $content .= $bootstrap->view->partial($this->_options['tabResource'],array('num' => $i, 'item' => $tabContentItem['content'],'tabTitle'=>$tabContentItem['title']));
                    break;
                }
            }  
        }

        switch($i)
        {
            case 1:
                $count = ' tab-count-one';
                break;
            case 2:
                $count = ' tab-count-two';
                break;
            case 3:
                $count = ' tab-count-three';
                break;
            case 4:
                $count = ' tab-count-four';
                break;
            case 5:
                $count = ' five-pillars-tabs';
                break;
        }
        $rendered = $bootstrap->view->partial($this->_options['tabLayout'],array('tabHeader' => $header, 'tabContent' => $content,'tabCount'=>$count));
        return $rendered;
    }

}
