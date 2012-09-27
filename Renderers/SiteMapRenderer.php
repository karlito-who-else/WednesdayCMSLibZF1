<?php
namespace Wednesday\Renderers;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    Doctrine\Common\EventArgs,
    Wednesday\Exception\InvalidArgumentException,
    Wednesday\Exception\InvalidMappingException;

/**
 * Description of SiteMapRenderer
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
class SiteMapRenderer implements Renderer {
    //put your code here
    private $_page;
    private $_options;
    private $_baseuri;

    public function __construct($page) {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->em = $bootstrap->getContainer()->get('entity.manager');
        $this->repo = $this->em->getRepository('Application\Entities\Pages');
        $this->_page = $page;
//        $this->_options = $options;
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
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $rendered = "SiteMapRenderer: GO!";
        $bootstrap->view->partialLoop()->setObjectKey('entity');
        $this->log = $bootstrap->getResource('Log');
        $this->log->err(get_class($this)."::render()");
        return $rendered;
    }

    public function renderChildren($parent, $count=0, $url = '/',$string='') {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $bootstrap->view->partialLoop()->setObjectKey('entity');
        $this->log = $bootstrap->getResource('Log');
        $this->log->err(get_class($this)."::renderChildren()");        
        if ($count == 0) {
            $string .= '<ul class="list-unsigned sitemap">'."\n";
        } else {
            $string .='<ul>'."\n";
        }
        try {
            $this->log->err(get_class($parent)."?"); 
            foreach ($parent->children as $node) {
                if($node->status == 'published') {
                    if (count($node->children)>0) {
                        if(($node->template->type=='aggregate')&&($node->children->first()->template->type=='resource')) {
                            $string .= '<li><a href="'.$url.$node->slug.'">' . $node->title.'</a></li>'."\n"."\n";
                        } else {
                            $string .= '<li><a href="'.$this->repo->getPageUri($node).'">' . $node->title . '</a>';
                            $string .= $this->renderChildren($node, $count,$node->slug.'/');
                            $string .= '</li>'."\n"."\n";                            
                        }
                    } else {
                        $string .= '<li><a href="'.$url.$node->slug.'">' . $node->title.'</a></li>'."\n"."\n";
                    }
                }
            }
        }
        catch(Exception $e) {
            $this->log->err($e->getMessage());
        }

        $string .= "</ul>"."\n"."\n"."\n";
        return $string;
    }

}
