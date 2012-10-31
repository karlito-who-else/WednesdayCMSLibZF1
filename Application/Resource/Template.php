<?php
//namespace Wednesday\Application\Resource;

use \Wednesday\Renderers\Template,
    \Zend_Application_Resource_ResourceAbstract as ResourceAbstract;

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
//require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * Description of Template
application resource
 *
 * Example configuration:
 * <pre>
 *   resources.template.theme.manager = false        ; default
 *   resources.template.version = 1.7.1               ; <null>
 *   resources.template.configpath = "/foo/bar"
 *   resources.template.javascript.enable = true
 *   resources.template.javascript.library = ZendX_Application_Resource_Jquery;
 *   resources.template.javascript.library = Zend_Application_Resource_Dojo;
 *   resources.template.ui_enable = true;
 *   resources.template.uiversion = 0.7.7;
 *   resources.template.ui_version = 0.7.7;
 *   resources.template.uilocalpath = "/bar/foo";
 *   resources.template.ui_localpath = "/bar/foo";
 *   resources.template.cdn_ssl = false
 *   resources.template.render_mode = 255 ; default
 *   resources.template.rendermode = 255 ; default
 *
 *   resources.Jquery.javascriptfile = "/some/file.js"
 *   resources.Jquery.javascriptfiles.0 = "/some/file.js"
 *   resources.Jquery.stylesheet = "/some/file.css"
 *   resources.Jquery.stylesheets.0 = "/some/file.css"
 * </pre>
 *
 * Resource for settings JQuery options
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   ZendX
 * @package    ZendX_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author mrhelly
 */
class Wednesday_Application_Resource_Template extends ResourceAbstract {

    /**
     * @var Wednesday_View_Helper_Template
     */
    protected $_template;

    /**
     * Init Doctrine
     *
     * @param N/A
     * @return \Doctrine\ORM\EntityManager Instance.
     */
    public function init() {
        #Get logger//LoggableListener
        $this->log = $this->getBootstrap()->getResource('Log');//->getResource('Log');
        $this->log->debug(get_class($this).'::init');
        if (null !== ($this->_template = $this->getTemplate())) {
            return $this->getTemplate();
        }
    }

    public function getTemplate(){
        if (null === $this->_template) {
            $this->getBootstrap()->bootstrap('view');
            $view = $this->getBootstrap()->view;
            Template::enableView($view);
            $view->Template()->setOptions($this->getOptions());
            $this->_template = $view->Template();
        }
        $this->getBootstrap()->getContainer()->set('template.manager', $this->_template);
        return $this->_template;
    }
}
