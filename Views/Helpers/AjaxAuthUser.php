<?php
//namespace Wednesday\View\Helper;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \Default_Form_Login;

/**
 * Description of Resource
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_View_Helper_AjaxAuthUser extends ViewHelperAbstract {

    /**
     *
     * @return type
     */
    public function ajaxAuthUser($identity) {
        $form = new Default_Form_Login();
        $loginForm = $form;
        if($identity != false) {
            $loginForm = <<<EOT
                <form action="/admin/auth/logout" method="post" class="pull-right">
                    <button class="btn" name="submit">Logout {$identity->username}</button>
                </form>
EOT;
        }
        return $loginForm;
    }

}
