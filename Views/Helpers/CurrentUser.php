<?php

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \Zend_Auth as ZendAuth,
    \Wednesday\Acl\WednesdayAcl as WedAcl;

/**
 * Description of Resource
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_View_Helper_CurrentUser extends ViewHelperAbstract {
    const USERS = 'Application\Entities\Users';

    /**
     *
     * @return type
     */
       public function currentUser() {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $log = $bootstrap->getResource('Log');
        $wedacl = WedAcl::getInstance();
        $user = $wedacl->getUser();
        return $user;
    }
}