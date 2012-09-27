<?php

use Zend\Code\Reflection as Z2ReflectionClass,
    \Zend_Controller_Front as Front,
    \Zend_Reflection_Class as ReflectionClass,
    \Zend_Registry as Registry;

/**
 * Description of Boilerplate_Controller_Helper_DependencyInjector
 *
 * @author mrhelly
 */
class Wednesday_Controller_Action_Helper_DependencyInjector extends Zend_Controller_Action_Helper_Abstract {

    /**
     * Takes care of injecting controller dependencies
     * into the controller at runtime.
     */
    public function preDispatch() {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');
        $this->log->info(get_class($this).'::preDispatch');

        $actionController = $this->getActionController();

        $r = new ReflectionClass($actionController);
        $properties = $r->getProperties();

//        $this->log->info($properties);

        foreach ($properties as $property) {
//            $this->log->info($property->getDeclaringClass()->getName());
//            $this->log->info(get_class($actionController));
//            if ($property->getDeclaringClass()->getName() == get_class($actionController)) {

                $this->log->info($property->getDeclaringClass()->getName());

                if ($property->getDocComment() && $property->getDocComment()->hasTag('InjectService')) {

                    $this->log->info($property->getDocComment());

                    //$tag = $property->getDocComment()->getTag('InjectService');
                    $tag = $property->getDocComment()->getTag('var');

                    if ($tag->getDescription()) {
                        $this->log->info($tag->getDescription());

                        $sc = Registry::get('sc');

                        $service = $sc->get(
                                $tag->getDescription()
                        );

                        $property->setAccessible(true);
                        $property->setValue($actionController, $service);
                    }
                    else
                        throw new Exception("No service key given");
                }
//            }
        }
    }

}
