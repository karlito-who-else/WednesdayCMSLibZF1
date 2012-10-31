<?php

namespace Wednesday\Restable\Action;

use \Zend_Controller_Request_Abstract as RequestAbstract,
    \Zend_Controller_Front as Front;

/**
 * Description of Post
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Post extends AbstractAction {

    /**
     * configure
     *
     * @return void
     */
    public function configure($reqVars) {
        $this->log->info($query);
        return $this;
    }

    /**
     * options
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function options($reqVars) {
        $this->log->info($reqVars);
        //return get_class_methods(get_class($this));
        return $this;
    }

    /**
     * head
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function head($reqVars) {
        $this->log->info($reqVars);
        return $this;
    }

    /**
     * post
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function get($reqVars) {
        $this->log->info($reqVars);
        return $this;
    }

    /**
     * post
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function post($reqVars) {
        $this->log->info($reqVars);
        return $this;
    }

    /**
     * put
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function put($reqVars) {
        $this->log->info($reqVars);
        return $this;
    }

    /**
     * delete
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function delete($reqVars) {
        $this->log->info($reqVars);
        return $this;
    }

    /**
     * dispatch
     *
     * @param type $query
     * @return Post $this
     */
    public function dispatch($query) {
        $this->log->info($query);
        return $this;
    }

    public function toJsonObject($format = "default") {
        $code = 200;
        $message = 'OK';
        $method = 'Post';
        return (object) array( 'code' => $code, 'message' => $message, 'method'=>$method);
    }
}
