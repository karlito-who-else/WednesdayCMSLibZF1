<?php
namespace Wednesday\Restable;

use \Zend_Controller_Request_Abstract as RequestAbstract,
    \Zend_Controller_Front as Front;

/**
 * Description of RequestParser
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class RequestParser {

    /**
     *
     * Zend_Controller_Request_Abstract
     * @var string
     */
    private $request;

    /**
     *
     * @var string
     */
    private $method;

    /**
     *
     * @var array
     */
    private $params;

    public function __construct(RequestAbstract $request) {
        #Request
        $this->request = $request;
        #Get bootstrap object.
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        #Get Logger
        $this->log = $bootstrap->getResource('Log');
    }

    public function parseRequest($request = false) {
        $requestUri = str_replace('/api', '', $this->request->getRequestUri());
        $requestObjA = $this->parseRestRequest();
        $requestObjB = $this->parseRawRequest($requestUri);//$_SERVER['PATH_INFO']
        $this->params = array_merge($requestObjA, $requestObjB);
        $this->method = $this->checkRequestMethod();
        if(($this->method == 'get')&&($this->params['action'] == 'list')){
            $this->method = 'index';
        }
        if($this->params['id'] == 'tree'){
            $this->method = 'index';
            $this->params['action'] = 'tree';
        } else if($this->params['id'] == 'all') {
            $this->method = 'index';        
        } else if($this->params['id'] == '') {
            $this->params['id'] = $this->method;
        }
        $this->log->debug('Parse Request: ');
        $this->log->debug($this->method);
        $this->log->debug($this->params);
    }

    public function getParams() {
        if(isset($this->params)===false) {
            $this->parseRequest();
        }
        return $this->params;
    }

    public function getMethod() {
        if(isset($this->method)===false){
            $this->parseRequest();
        }
        return $this->method;
    }

    protected function checkRequestMethod($parms = false) {
        $method = 'index';
        $method = ($this->request->isGet())?'get':$method;
        $method = ($this->request->isPost())?'post':$method;
        $method = ($this->request->isPut())?'put':$method;
        $method = ($this->request->isDelete())?'delete':$method;
        $method = ($this->request->isHead())?'head':$method;
        $method = ($this->request->isOptions())?'option':$method;
        return $method;
    }

    /**
     * @method parseRestRequest
     * @param array $params
     * @return array
     */
    protected function parseRestRequest() {
        $params = $this->request->getParams();
        $defaultParams = array('module', 'controller', 'action');
        $filtered = array();
        foreach ($params as $paramName => $paramValue) {
            if (!in_array($paramName, $defaultParams)) {
                $this->log->debug("{$paramName} => {$paramValue}");
                $filtered[$paramName] = $paramValue;
            }
        }
        #Now check 'entity'
        if (strrpos($filtered['apireq'], "/") !== false) {
            $expl = explode('/', $filtered['apireq']);
            $filtered['entity'] = $expl[0];
            $filtered['id'] = $expl[1];
            if(isset($expl[2])===true) {
                $filtered['action'] = $expl[2];
            }
        }
        $filtered['get'] = $_GET;
        $filtered['post'] = $_POST;
        return $filtered;
    }

    protected function parseRawRequest($path) {
        $path = ltrim($path, '/');
        $e = explode('/', $path);
        $count = count($e);
        $end = end($e);
        $e2 = explode('.', $end);
        $e[count($e) - 1] = $e2[0];
        $format = isset($e2[1]) ? $e2[1] : 'xml';
        $entity = $e[0];
        $id = isset($e[1]) ? $e[1] : null;
        $action = isset($e[2]) ? $e[2] : null;
        $method = isset($_REQUEST['_method']) ? $_REQUEST['_method'] : $_SERVER['REQUEST_METHOD'];
        $method = strtoupper($method);
        if ($count === 1) {
            if ($method === 'POST' || $method === 'PUT') {
                $action = 'insert';
            } else if ($method === 'GET') {
                $action = 'list';
            }
        } else if ($count === 2) {
            if ($method === 'POST' || $method === 'PUT') {
                $action = 'update';
            } else if ($method === 'GET') {
                $action = 'get';
            } else if ($method === 'DELETE') {
                $action = 'delete';
            }
        } else if ($count === 3) {
            $action = $action;
        }

        $data = array_merge(array(
            'entity' => $entity,
            'id' => $id,
            'action' => $action
        ), $_REQUEST);

        return $data;
    }

}
