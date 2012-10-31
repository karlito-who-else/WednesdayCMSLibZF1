<?php
namespace Wednesday\Backbone\Action;

use \Zend_Controller_Request_Abstract as RequestAbstract,
    \Zend_Controller_Front as Front,
    \Zend_Log as ZendLog,
    \Zend_Auth as ZendAuth,
    \Wednesday\Acl\WednesdayAcl as WedAcl,
    \Doctrine\ORM\EntityManager;
/**
 * Description of AbstractAction
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
abstract class AbstractAction {

    /**
     *
     * Access to Zend_Log.
     * @var ZendLog
     */
    public $log;

    /**
     *
     * Zend_Auth object
     * @var ZendAuth
     */
    private $auth;

    /**
     *
     * Auto Loaded acl object to filter program flow.
     * @var WedAcl
     */
    private $acl;

    /**
     * Doctrine\ORM\EntityManager object wrapping the entity environment
     * @var Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     *
     * config
     * @var string
     */
    private $config;

    /**
     *
     * requestHandler
     * @var RequestAbstract
     */
    private $requestHandler;

    /**
     *
     * Wednesday Manager (Session|Cache).
     * @var Wednesday_Application_Resource_Wednesday
     */
    protected $wednesday;

    /**
     *
     * @param RequestAbstract $requestHandler
     * @return type
     */
    public function __construct(RequestAbstract $requestHandler = null) {

        $bootstrap = Front::getInstance()->getParam('bootstrap');
        #Get Logger
        $this->log = $bootstrap->getResource('Log');

        #Get EntityManager
        $this->em = $bootstrap->getContainer()->get('entity.manager');

        #Get Zend Auth.
        $this->auth = ZendAuth::getInstance();

        #Get Acl Object
        $this->acl = WedAcl::getInstance();

        #Get Wednesday Object
        $this->wednesday = $bootstrap->getContainer()->get('wednesday.manager');

        #Get Config Object
        $this->config = $bootstrap->getContainer()->get('config');

        #Set Request Handler
        $this->requestHandler = $requestHandler;

        $this->log->debug(get_class($this) . 'Allow Admin (' . $this->config['settings']['application']['administration'] . ')');

        if($this->config['settings']['application']['administration']==false){
//            $this->_redirect('/error/404');
            $code = 403;//401;
            $message = 'Forbidden';//'Unauthorized';
            $method = 'Forbidden';//'Unauthorized';
            return (object) array( 'status' => false, 'code' => $code, 'message' => $message, 'method'=>$method);
        }
    }

    public function findOneById($id) {
        return $this;
    }

    public function findAll() {
        return $this;
    }

    public function getRequest() {
        return $this->requestHandler;
    }

}
