<?php
namespace Wednesday\Resource;

use \Zend_Controller_Front as Front,
    \Zend_Cloud_StorageService_Factory as CloudStorageFactory,
    \Zend_Cloud_StorageService_Adapter;

/**
 * Description of Abstract
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
abstract class AbstractResource {
    
    /**
     * @var array config options.
     */
    protected static $ignore = array(".DS_Store",".","..");
        
    /**
     * @var array config options.
     */
    protected $options;
    
    /**
     * @var Zend_Cloud_StorageService_Adapter (FileSystem) instance.
     */
    protected $_local;
    
    /**
     * @var Zend_Cloud_StorageService_Adapter (CDN) instance.
     */
    protected $_cdn;
    
    /**
     * @var Doctrine\ORM\EntityManager The entity manager used by this query object.
     */
    protected $_em;
    
    /**
     *
     * Access to Zend_Log.
     * @var Zend_Log
     */
    public $log;
    
    /**
     * Initializes a new instance of a class derived from <tt>AbstractResource</tt>.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');
        
        $this->options = $options;
        $this->getStorageContainers();
        $this->log->debug(get_class($this)."::__construct");
    }
    
    /**
     * 
     */
    protected function getStorageContainers() {
        $this->_cdn = CloudStorageFactory::getAdapter($this->options['cdn']);
        $this->_local = CloudStorageFactory::getAdapter($this->options['local']);
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->em = $bootstrap->getContainer()->get('entity.manager');
    }
    
}