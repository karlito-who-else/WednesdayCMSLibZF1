<?php

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \Default_Form_Login;

/**
 * Description of Resource
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_View_Helper_UserIsLoggedIn extends ViewHelperAbstract {

    /**
     *
     * @return type
     */
    public function userIsLoggedIn() {

        $this->auth = Zend_Auth::getInstance();

        return $this->auth->hasIdentity();
    }
}