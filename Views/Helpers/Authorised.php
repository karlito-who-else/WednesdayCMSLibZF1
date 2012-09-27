<?php

use \Zend_Auth,
    \Zend_Controller_Front as Front,
    \Wednesday\Acl\WednesdayAcl as WedAcl,
    \Zend_View_Helper_Abstract as ViewHelperAbstract;

/**
 * Description of Authorised
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_View_Helper_Authorised extends ViewHelperAbstract {

    protected $_usr = null;
    protected $_acl = null;
    protected $log = null;

    /**
     *
     * @return type
     */
    public function authorised($resource,$permission) {
        $this->auth = Zend_Auth::getInstance();
        $denied = true;
        $bootstrap = Front::getInstance()->getParam("bootstrap");
		$this->log = $bootstrap->getResource('Log');
        $this->_acl = WedAcl::getInstance();
        $acl = $this->_acl->getAcl();
        $this->_user = $this->_acl->getUser();
        $roles = $this->_user->acluserroles;
//        $this->log->info($this->_user->username);
//        $this->log->info("count: ".count($roles));
        foreach($roles as $role) {
            if($acl->has($resource)){
                $allowed = $acl->isAllowed($role->name, $resource, $permission) ? "allowed" : "denied";
                $this->log->info("".$role->name." is ".$allowed." access to {$permission} {$resource}");
                if($acl->isAllowed($role->name, $resource, $permission)){
                    $denied = false;
                }
//            } else {
//                $denied = false;
            }
        }
        return !$denied;
    }

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
