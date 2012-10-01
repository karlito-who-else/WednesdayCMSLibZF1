<?php

namespace Wednesday\Resource;

use \Zend_Controller_Front as Front,
    \Zend_Registry,
    \Zend_Cache,
    \Zend_Cloud_StorageService_Factory as CloudStorageFactory,
    \Wednesday_Cloud_StorageService_Adapter_Cloudfiles as CloudfilesAdapter,
    \Compass_Service_Rackspace_Cloudfiles as RackspaceCloudfilesService,
    \Zend_Cloud_StorageService_Adapter,
    \Zend_Exception as Exception,
    \Application\Entities\MediaResources,
    \Application\Entities\MediaVariations,
    \Wednesday\Models\MetaData as MetaData;

/**
 * Description of Service
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
final class Service {
    const RESOURCES = "Application\Entities\MediaResources";
    const VARIATIONS = "Application\Entities\MediaVariations";
    const SYNCASSETS = "Application\Entities\ProcessedMedia";
    const MAXPROCESSED = 30;

    /**
     *
     * @var $_serviceClassName
     */
    private static $_serviceClassName = '\Wednesday\Resource\Service';

    /**
     *
     * @var $instance
     */
    private static $_instance;

    /**
     *
     * @var $generator
     */
    private static $_generator;    

    /**
     * array config options.
     * @var $options
     */
    protected $options;

    /**
     * array config options.
     * @var $options
     */
    protected $sizes;

    /**
     * cache object.
     * @var $cache
     */
    protected $cache;

    /**
     * Zend_Cloud_StorageService_Adapter (CDN) instance.
     * @var $_storage
     */
    protected $_storage;

    /**
     * Doctrine\ORM\EntityManager The entity manager used by this query object.
     * @var $_em
     */
    protected $_em;

    /**
     * Access to Zend_Log.
     * @var $log
     */
    public $log;

    /**
     * Initialize the default registry instance.
     *
     * @return void
     */
    protected static function init() {
        self::setInstance(new self::$_serviceClassName());
        self::$_instance->getStorage();
        self::$_instance->getGenerator();        
    }

    /**
     * Retrieves the default registry instance.
     *
     * @return Service
     */
    public static function getInstance() {
        if (self::$_instance === null) {
            self::init();
        }

        return self::$_instance;
    }

    /**
     * Set the default registry instance to a specified instance.
     *
     * @param Service $resource An object instance of type \Wednesday\Resource\ResourceContainers,
     *   or a subclass.
     * @return void
     * @throws Zend_Exception if registry is already initialized.
     */
    public static function setInstance(Service $resource) {
        if (self::$_instance !== null) {
            throw new Exception('Storage Service is already initialized');
        }

        self::$_instance = $resource;
    }

    /**
     *
     * @param type $options
     */
    public function setOptions($options) {
        $this->options = $options;
    }

    /**
     *
     * @return type
     */
    public function getOptions() {
        if (isset($this->options) === false) {
            $bootstrap = Front::getInstance()->getParam('bootstrap');
            $config = $bootstrap->getContainer()->get('config');
            $this->sizes = $config['settings']['application']['asset']['manager']['size'];
            $this->options = $config['settings']['application']['asset']['manager'];
        }
        return $this->options;
    }

    /**
     *
     * @return type
     */
    public function getStorage() {
        if (isset($this->_storage) === false) {
            $options = $this->getOptions();
            $this->_storage = new Containers($options);
            $bootstrap = Front::getInstance()->getParam('bootstrap');
            $this->_em = $bootstrap->getContainer()->get('entity.manager');
            $this->log = $bootstrap->getResource('Log');
        }
        return $this->_storage;
    }
    
    /**
     *
     * @return type
     */
    public function getGenerator() {
        if (self::$_generator === null) {
            self::$_generator = new Generate();
        }
        return self::$_generator;
    }    

    /**
     *
     * @param string $location
     * @return string
     */
    public function getBaseUri($location = 'local') {
        return $this->_storage->getBaseUrl($location);
    }

    /**
     *
     * @param Application\Entities\MediaResource $res
     * @param string $variation
     * @return string
     */
    public function getVariationLink($res, $variation = 'default') {
        $res->setVariation($variation);
        return $res->link;
    }

    /**
     *
     * @return boolean
     */
    public function syncAssets($location = 'local', $start = false, $refresh = false, $path = '/') {
        $this->cache = Front::getInstance()->getParam("bootstrap")->getResource('Cachemanager')->getCache('file');
        
        $ignore = $this->options['variations']['ignore'];
        switch ($location) {
//            case 'variation':
//                $files = $this->_storage->getFolderArray($path,'local');
//                foreach($files as $key => $file) {
//                    if($file != false) {
//                        if($file['type'] == 'image') {
//                            $this->log->debug($file['link']);
//                            #Check for existing variations.
//                            $resource = $this->_em->getRepository(self::RESOURCES)->findOneByLink($file['link']);
//                            if (isset($resource) === true) {
//                                $this->log->debug($resource->id);
//                                $metadatas = $this->storeVariations($file);
//                                $this->log->debug(count($metadatas));
//                                foreach ($metadatas as $metadata) {
//                                    if(!$resource->metadata->contains($metadata)) {
//                                        $resource->metadata->add($metadata);
//                                    }
//                                }
//                                $this->_em->persist($resource);
//                                $this->_em->flush();
//                                $this->_em->clear();
//                            }
//                        }
//                    }
//                }
//                break;
//            case 'alllocal':
//                $start = true;
//                $refresh = false;
//                $path = "/";
//            case 'local':
//                $cacheKey = 'localsync';
//                #New Sync Init
//                if($start) {
//                    $localfiles = $this->_storage->getFolderArray($path,'local');
//                    if($this->cache->test($cacheKey)) {
//                        //exists
//                        $this->log->debug($cacheKey);
//                        $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG,array($cacheKey));
//                    }
//                    //Always strip ignored files.
//                    if($refresh) { //Only check new files.
//                        $files = array();
//                        foreach($localfiles as $file) {
//                            $start = substr($file['name'], 0, strlen($ignore));
//                            if($ignore != $start) {
//                                //$localfiles
//                                $dbfile = $this->_em->getRepository(self::RESOURCES)->findOneByLink($file['link']);
//                                if(isset($dbfile)===false) {
//                                    $files[] = $file;
//                                }
//                            }
//                        }
//                    } else {
//                        $files = array();
//                        foreach($localfiles as $file) {
//                            $start = substr($file['name'], 0, strlen($ignore));
//                            if($ignore != $start) {
//                                $files[] = $file;
//                            }
//                        }
//                    }
////                    $this->log->debug($files);
//                    $this->cache->save($files);
//                    #Reset Table contents.
//                    $bootstrap = Front::getInstance()->getParam('bootstrap');
//                    $conn = $bootstrap->getResource('doctrine')->getConnection('default');
//                    $conn->executeQuery("TRUNCATE TABLE `processed_assets`");
//                    $processAssetClass = self::SYNCASSETS;
//                    foreach($files as $file) {
//                        $asset = new $processAssetClass();
//                        $asset->filename = $file['link'];
//                        $asset->created = new \DateTime('now');
//                        $asset->completed = false;
//                        $this->_em->persist($asset);
//                    }
//                    $this->_em->flush();
//                    $this->_em->clear();
//                }
//                #Do Sync
//                if ($this->cache->test($cacheKey)) {
//                    $files = $this->cache->load($cacheKey);
//                    $this->log->debug(count($files));
//                    $count = 0;
//                    foreach($files as $key => $file) {
//                        if($file != false) {
//                            $this->storeResource($file);
//                            #Store Completion
//                            $processed = $this->_em->getRepository(self::SYNCASSETS)->findOneByFilename($file['link']);
//                            if(isset($processed)===true) {
//                                $processed->completed = true;
//                                $this->_em->persist($processed);
//                                $this->_em->flush();
//                                $this->_em->clear();
//                                $files[$key] = false;
//                                $count++;
//                                if($count >= self::MAXPROCESSED) {
//                                    $this->log->debug('MAX PROCESSED');
//                                    break 1;
//                                }
//                            }
//                        }
//                    }
//                    #Save updated items to cache.
//                    $this->cache->save($files);
//                }
//                $this->log->debug(count($files));
//                break;
//            case 'cdn':
//
//                $files = array();
//                $this->log->debug($location);
//                break;
            case 'local':
                $cacheKey = 'localsync';
                $this->log->info($start);
                $this->log->info($refresh);
                $this->log->info($path);
                $this->log->info($location);
                $files = array();
                if($start) {
                    $localfiles = $this->_storage->getFolderArray($path,'local');
//                    $localfiles = $this->_storage->getFolderArray($path,'local');
                    if($this->cache->test($cacheKey)) {
                        //exists
                        $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG,array($cacheKey));
                    }
                    //Always strip ignored files.
                    if($refresh) { //Only check new files.
                        foreach($localfiles as $file) {
                            $start = substr($file['name'], 0, strlen($ignore));
                            if($ignore != $start) {
                                $dbfile = $this->_em->getRepository(self::RESOURCES)->findOneByLink($file['link']);
                                if(isset($dbfile)===false) {
                                    $files[] = $file;
                                }
                            }
                        }
                    } else { // Check ALL files.
                        foreach($localfiles as $file) {
                            $start = substr($file['name'], 0, strlen($ignore));
                            if($ignore != $start) {
                                $files[] = $file;
                            }
                        }
                    }
                    $this->cache->save($files);
//                } else {
                }
//                #Do Sync
                $count = 0;
                if ($this->cache->test($cacheKey)) {
                    $cachefiles = $this->cache->load($cacheKey);
                    $process = array_slice($cachefiles, 0, self::MAXPROCESSED);
                    $count = count($process);
                    self::$_generator->storeResources($process);
                    $this->cache->save($cachefiles);
                    $files = $process;
                }
                $this->log->info(count($files));                           
                break;
            default:
                $this->log->info($location);
                break;
        }

        return $files;
    }

    /**
     *
     */
    public function saveAsset($filepath) {
        $file = $this->_storage->getFileArray($filepath,'local');
        $this->log->debug($filepath);
        $this->log->err($file);
        return $this->storeResource($file);
    }

    /**
     *
     */
    protected function pushResource($file) {
        $return = false;
        $path = $file->path . '/' . $file->name; //ltrim(,'/');
        $metadata = $this->_storage->fetchMetadata($path, 'cdn');
        $this->log->debug($path);
//        $this->log->debug($metadata);
        if ($file->cdn) {
            $return = true;
        } else {
            if (!is_dir(WEB_PATH . $file->link)) {
                $date = new \DateTime('now');
                $fdate = $date->format(\DateTime::W3C);
                $data = file_get_contents(WEB_PATH . $file->link);
                $options = array(CloudfilesAdapter::METADATA => array(
                        'Last-Modified' => $fdate,
                        'Wednesday' => 'Rock'
                        ));
//        $options = array(
//            Zend_Cloud_StorageService_Adapter_S3::BUCKET_NAME => "myBucket",
//            Zend_Cloud_StorageService_Adapter_S3::METADATA    => array(
//            Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
//        ));
                $return = $this->_storage->storeItem($path, 'cdn', $data, $options);
                $file->cdn = true;
                $this->_em->persist($file);
                $this->_em->flush();
                unset($data);
            }
        }
        return $return;
    }

}