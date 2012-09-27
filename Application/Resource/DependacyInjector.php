<?php
//namespace Wednesday\Application\Resource;

use Wednesday_Controller_Action_Helper_DependencyInjector as ActionHelperDependencyInjector,
    \Zend_Controller_Action_HelperBroker as ActionHelperBroker,
    \Zend_Application_Resource_ResourceAbstract as ResourceAbstract,
    \Zend_Controller_Front as Front,
    \Zend_Session_Namespace;

/**
 * Description of DependacyInjector
 *
 * @author mrhelly
 */
class Wednesday_Application_Resource_DependacyInjector extends ResourceAbstract {

    public function init() {
        $this->log = $this->getBootstrap()->getResource('Log');
        $this->log->info(get_class($this) . '::init');
        return $this->getDependencyInjector();
    }

    public function getDependencyInjector() {
        $dependencyInjector = new ActionHelperDependencyInjector();
        ActionHelperBroker::addHelper($dependencyInjector);
        $this->log->info(get_class($dependencyInjector) . '::getDependencyInjector');
        return $dependencyInjector;
    }

}