<?php
//namespace Wednesday\Application\Resource;

/**/
use \Zend_Controller_Front as Front,
    \Zend_Application_Resource_ResourceAbstract as ResourceAbstract,
    Doctrine\Common\EventManager,
    Doctrine\DBAL\DriverManager,
    Doctrine\DBAL\Logging\DebugStack,
    Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\Common\Annotations\AnnotationRegistry,
    Doctrine\ORM\Mapping\Driver\AnnotationDriver,
    Doctrine\ORM\Mapping\Driver\DriverChain,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration as D2ORMConf,
    Wednesday\Auth\Adapter\Doctrine;

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
class Wednesday_Application_Resource_Doctrine extends ResourceAbstract {

    /**
     * @var string
     */
    protected $curcfg;
    
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_entManager;

    /**
     * @var \Doctrine\Common\EventManager
     */
    protected $_evtManager;

    /**
     * @var \Doctrine\DBAL\DriverManager
     */
    protected $_drvManager;

    /**
     * @var Doctrine\DBAL\Connection
     */
    protected $_conn;

    /**
     * @var Zend_Log
     */
    protected $log;
    /**
     * @var D2ORMConf
     */
    protected $cfg;

    /**
     * Init Doctrine
     *
     * @param N/A
     * @return \Doctrine\ORM\EntityManager Instance.
     */
    public function init() {
        #Get logger
        if(null === $this->log) {
            $bootstrap = $this->getBootstrap();
            $this->log = $bootstrap->getResource('Log');
        }
        $this->log->debug(get_class($this).'::init');
        $this->cfg          = $this->getConfig();
        $this->_evtManager  = $this->getEventManager();
        $this->_entManager  = $this->getEntityManager();
        return $this;
    }
        
    /**
     * Init Doctrine
     *
     * @param N/A
     * @return \Doctrine\Common\EventManager Instance.
     */
    public function getConfig($name = 'default') {
        return new D2ORMConf();
    }
    
    /**
     * Init Doctrine
     *
     * @param N/A
     * @return \Doctrine\Common\EventManager Instance.
     */
    public function getEventManager($name = 'default', $listeners = true) {
        #Get logger
        if(null === $this->log) {
            $this->log = $this->getBootstrap()->getResource('Log');//->getResource('Log');//->getContainer()->get('logger');
        }
        $this->log->debug(get_class($this).'::get_evtManager');
        if ( (null === $this->_evtManager) ) {
            $this->_evtManager = $this->_buildEventManager($this->getOptions(),$listeners);
        } else if(($this->curcfg != $name)&&($name != '')) {
            return $this->_buildEventManager($this->getOptions(),$listeners);
        }
        $this->getBootstrap()->getContainer()->set('event.manager', $this->_evtManager);
        return $this->_evtManager;
    }

    /**
     * Init Doctrine
     *
     * @param N/A
     * @return \Doctrine\ORM\EntityManager Instance.
     */
    public function getEntityManager($name = 'default', $listeners = true) {
        #Get logger
        if(null === $this->log) {
            $this->log = $this->getBootstrap()->getResource('Log');//>getContainer()->get('logger');
        }
        $this->log->debug(get_class($this).'::getEntityManager');  
        if ((null === $this->_entManager) ) {
            $this->_entManager = $this->_buildEntityManager($name,$listeners);
        } else if(($this->curcfg != $name)&&($name != '')) {
            return $this->_buildEntityManager($name,$listeners);
        }
        $this->getBootstrap()->getContainer()->set('entity.manager', $this->_entManager);
        return $this->_entManager;
    }

    /**
     *
     * @param type $name
     * @return type
     */
    public function getConnection($name = 'default', $eventman = false) {
        #Get logger
        if(null === $this->log) {
            $this->log = $this->getBootstrap()->getResource('Log');//$this->getBootstrap()->getResource('Log');//->getContainer()->get('logger');
        }
        $options = $this->getOptions();
//        $name = (($name != $options['orm']['manager']['connection'])&&($name == 'default'))?$options['orm']['manager']['connection']:$name;
        $this->log->debug(get_class($this).'::getConnection('.$name.')');
        if($name != '') {
            return $this->_buildConnection($name,$eventman);
        }
        if ((null === $this->_conn) ) {
            $this->_conn = $this->_buildConnection($name,$eventman);
        }
        $this->getBootstrap()->getContainer()->set('doctrine.connection', $this->_conn);
        return $this->_conn;
    }

    /**
     *
     * @return type
     */
    protected function _buildConnection($name = 'default', $eventman = false) {
        $options = $this->getOptions();
        $connectionOptions = $this->_buildConnectionOptions($name);
        #Setup configuration as seen from the sandbox application
        if ( ($eventman==false ) && (null === $this->_evtManager) ) {
            $eventman = $this->_evtManager = $this->getEventManager($name);
        } else if ( $eventman==false ) {
            $eventman = $this->getEventManager($name);   
        }
        return DriverManager::getConnection($connectionOptions, $this->cfg, $eventman);
    }

    /**
     * A method to build the connection options, for a Doctrine
     * EntityManager/Connection. Sure, we can find a more elegant solution to build
     * the connection options. A builder class could be applied. Sure you can with
     * some refactor :)
     * TODO: refactor to build some other, more elegant, solution to build the conn
     * ection object.
     * @param Array $params The options array defined in getOptions
     * @return \Doctrine\Common\EventManager Instance.
     */
    protected function _buildEventManager($name = 'default', $listeners = true) {
        $options = $this->getOptions();
        $eventManager = new EventManager();
//        $this->log->debug($options);
        #TODO Loop through config to find availible listeners.
        
            foreach ($options['extensions']['listener'] as $listenerName => $listenerOptions) {
                if((!$listeners)&&($listenerName=='translatable')) {
                    break 1;
                }
                $$listenerName = new $listenerOptions['driver']();
                if(isset($listenerOptions['methods'])===true) {
                  foreach($listenerOptions['methods'] as $method => $param) {
                      $$listenerName->$method($param);
                  }
                }
                $eventManager->addEventSubscriber($$listenerName);
                        
        }
        return $eventManager;
    }

    /**
     * A method to build the connection options, for a Doctrine
     * EntityManager/Connection. Sure, we can find a more elegant solution to build
     * the connection options. A builder class could be applied. Sure you can with
     * some refactor :)
     * TODO: refactor to build some other, more elegant, solution to build the conn
     * ection object.
     * @param Array $params The options array defined in getOptions
     * @return \Doctrine\ORM\EntityManager Instance.
     */
    protected function _buildEntityManager($name = 'default', $listeners = true) {
        if(null === $this->log) {
            $this->log = $this->getBootstrap()->getResource('Log');
        }
        $this->log->debug(get_class($this).'::buildEntityManager');
        #Options
        $options = $this->getOptions();
        $eventman = $this->getEventManager($name,$listeners);
        $connection = $this->getConnection($name,$eventman);
        $config = $this->getConfig();//$this->cfg;
        
        #Now configure doctrine cache
        if (defined('COMMAND_LINE')===true) {
            $cacheClass = 'Doctrine\Common\Cache\ArrayCache';
        } else if ('development' == APPLICATION_ENV) {
            $cacheClass = isset($options['cacheClass']) ? $options['cacheClass'] : 'Doctrine\Common\Cache\ArrayCache';
        } else {
            $cacheClass = isset($options['cacheClass']) ? $options['cacheClass'] : 'Doctrine\Common\Cache\ArrayCache';
        }
        #Cache Options.
        $cache = new $cacheClass();
        $config->setMetadataCacheImpl($cache);
        #Caches for Development..
        if (defined('COMMAND_LINE')===true) {
            $cachedClass = 'Doctrine\Common\Cache\ArrayCache';
            $cached = new $cachedClass();
            $config->setQueryCacheImpl($cached);
            $config->setResultCacheImpl($cached);
        } else {//else if ('development' == APPLICATION_ENV) {}
            $config->setQueryCacheImpl($cache);
            $config->setResultCacheImpl($cache);
        }
        #Get data stored for each module
        $autoPaths = $this->getBootstrap()->getContainer()->get('autoload.paths');
        #Set paths for all module entities, which should all have the same namespace...
        $entityPaths = $autoPaths->entities;
        #Proxy Configuration
        $config->setProxyDir($autoPaths->proxyPath);
        $config->setProxyNamespace('Proxies');
        $config->setAutoGenerateProxyClasses(false);
        $this->log->debug(get_class($this).'::buildEntityManager('.$cacheClass.')');
        #Set Logging
        $logger = new DebugStack;
        $config->setSqlLogger($logger);
//        $this->log->debug(get_class($this).'::buildEntityManager('.$cacheClass.')');
        #Driver Configuration
        AnnotationRegistry::registerFile("Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php");
        #TODO Load namespaces from ini?
        $metadataDrivers = $options['orm']['manager']['metadataDrivers'][0];
//        $this->log->debug('metadataDrivers');
//        $this->log->debug($metadataDrivers);
        AnnotationRegistry::registerAutoloadNamespace("Gedmo", CORE_PATH . "/private/library");
        AnnotationRegistry::registerAutoloadNamespace("Wednesday", CORE_PATH . "/private/library");
        AnnotationRegistry::registerAutoloadNamespace("Doctrine\ORM\Mapping\\", CORE_PATH . "/private/library");
        $reader = new AnnotationReader();
        #Aliases interesting...
        //$reader->setAnnotationNamespaceAlias('Gedmo\Mapping\Annotation\\', 'gedmo');
//        $this->log->debug(get_class($this).'::buildEntityManager('.$cacheClass.')');
        #Add paths from ini.
        foreach ($metadataDrivers['mappingDirs'] as $folder){
            array_push($autoPaths->entities, $folder);
        }
        $entityPaths = $autoPaths->entities;
        #Add paths for Listeners.
        foreach ($options['extensions']['listener'] as $listenerName => $listenerOptions) {
            if(isset($listenerOptions['path'])){
                array_push($entityPaths, $listenerOptions['path']);
            }
        }
        $chainDriverImpl = new DriverChain();
        $defaultDriverImpl = AnnotationDriver::create($autoPaths->entities, $reader);
        $defaultDriverImpl->getAllClassNames();
        $translatableDriverImpl = $config->newDefaultAnnotationDriver($entityPaths);
        foreach($metadataDrivers['mappingNamespace'] as $mapping) {
            $chainDriverImpl->addDriver($defaultDriverImpl, $mapping.'\\');
        }
        #TODO Add namespaces from ini.
        foreach ($options['extensions']['listener'] as $listenerName => $listenerOptions) {
            if(isset($listenerOptions['namespace'])){
                $chainDriverImpl->addDriver($translatableDriverImpl, $listenerOptions['namespace']);
            }
        }
        $config->setMetadataDriverImpl($chainDriverImpl);
//        $this->log->debug(get_class($this).'::buildEntityManager('.$cacheClass.')');
        #setup entity manager
        return EntityManager::create(
            $connection,
            $config,
            $eventman
        );
    }

    /**
     * A method to build the connection options, for a Doctrine
     * EntityManager/Connection. Sure, we can find a more elegant solution to build
     * the connection options. A builder class could be applied. Sure you can with
     * some refactor :)
     * TODO: refactor to build some other, more elegant, solution to build the conn
     * ection object.
     * @param Array $options The options array defined on the application.ini file
     * @return Array
     */
    protected function _buildConnectionOptions($name = 'default') {
        $this->log->debug(get_class($this).'::_buildConnectionOptions('.$name.')');
        $options = $this->getOptions();
        
        if($name=='') {
            $name = 'default';
        }
        if($name == 'default') {
            if(isset($options['orm']['manager']['connection'])===true){
                $name = $options['orm']['manager']['connection'];
            }
        }
        $connectionSpec = array(
            'pdo_sqlite' => array('path', 'memory', 'user', 'password'),
            'pdo_mysql'  => array('user', 'password', 'host', 'port', 'dbname', 'unix_socket', 'charset', 'persistent'),
            'pdo_pgsql'  => array('user', 'password', 'host', 'port', 'dbname', 'persistent'),
            'pdo_oci'    => array('user', 'password', 'host', 'port', 'dbname', 'charset', 'persistent')
        );
		$dbalopts = $options['dbal'][$name];
        $connection = array('driver' => $dbalopts['driver'] );
		#Simple array map.
        foreach ($connectionSpec[$dbalopts['driver']] as $driverOption) {
            if (isset($dbalopts[$driverOption]) && !is_null($driverOption)) {
                $connection[$driverOption] = $dbalopts[$driverOption];
            }
        }
        return $connection;
    }

/* EoC */
}