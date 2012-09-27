<?php


namespace Wednesday\Restable\Action;

use \Zend_Controller_Request_Abstract as RequestAbstract,
    \Zend_Controller_Front as Front,
    \Zend_Auth as ZendAuth,
    \Wednesday_Auth_Adapter_Doctrine as DoctrineAdapterAuth;

/**
 * Description of Auth
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Auth extends AbstractAction {

    public function __construct(RequestAbstract $request = null) {
        parent::__construct($request);
        //$this->acl = $acl;
        //$this->log = $log;
        //$this->wednesday = $wednesday;
        //$this->em = $em;
//        $request = $this->getRequest();
    }

    public function toJsonObject() {
        $code = 200;//403;//401;
        $message = 'OK';//Forbidden';//'Unauthorized';
        $method = 'OK';//Forbidden';//'Unauthorized';
        return (object) array( 'auth' => $this->auth, 'code' => $code, 'message' => $message, 'method'=>$method);
//        return $this;
    }

    public function login($params) {
        $adapter = new DoctrineAdapterAuth(
            $this->em,
            'Application\Entities\Users',
            'username',
            'password',
            "checkPassword"
        );
        $request = $this->getRequest();
        if(isset($this->auth)===false) {
            $this->auth = ZendAuth::getInstance();
        }
        if(isset($this->em)===false) {
            $bootstrap = Front::getInstance()->getParam('bootstrap');
            $this->em = $bootstrap->getContainer()->get('entity.manager');
        }
        $code = 200;
        $message = 'OK';
        $method = 'no user logged in.';
        if($this->auth->hasIdentity()) {
            $code = 200;
            $message = 'OK';
            $method = $this->auth->getIdentity() . ' logged in.';
            return (object) array( 'status' => false, 'code' => $code, 'message' => $message, 'response'=>$method);
        } else if($request->isPost()||$request->isPut()) {
            $values = $request->getParams();
            $this->log->debug($values['username']." - ".$values['email']." - ".$values['password']);
            $adapter->setIdentity($values['username']);
            $adapter->setCredential($values['password']);
            $result = $this->auth->authenticate($adapter);
            if (!$result->isValid()) {
                $code = 403;
                $message = 'Forbidden';
                $method = 'Forbidden';
                $this->auth->clearIdentity();
                return (object) array( 'status' => false, 'code' => $code, 'message' => $message, 'method'=>$method);
            } else {
                $code = 200;
                $message = 'OK';
                $method = 'OK';
                return (object) array( 'status' => false, 'code' => $code, 'message' => $message, 'method'=>$method);
            }
            return (object) array( 'status' => false, 'code' => $code, 'message' => $message, 'method'=>$method);
        }
        return (object) array( 'status' => false, 'code' => $code, 'message' => $message, 'method'=>$method);
    }

    public function logout() {
        if(isset($this->auth)===false) {
            $this->auth = ZendAuth::getInstance();
        }
        $code = 200;
        $message = 'OK';
        $method = 'no user logged in.';
        $this->auth->clearIdentity();
        return (object) array( 'status' => false, 'code' => $code, 'message' => $message);
    }

}
