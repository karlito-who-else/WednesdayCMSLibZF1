<?php
namespace Wednesday\Resource;

use \Zend_Controller_Front as Front,
    \Zend_Cloud_StorageService_Factory as CloudStorageFactory,
    \Compass_Service_Rackspace_Cloudfiles as RackspaceCloudfilesService,
    \Zend_Cloud_StorageService_Adapter;

/**
 * Description of Abstract
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
final class Containers extends AbstractResource {

    /**
     * @var array config options.
     */
    protected static $ignore = array(".DS_Store",".gitignore",".","..");

    /**
     * @var array config options.
     */
    protected static $restrict = array("v.");

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
     *
     * @param type $path
     * @return type
     */
    public function listItems($path, $location) {
        $path = str_replace('//','/',$path);
        $loc = ($location=='local')?'_local':'_cdn';
        return $this->$loc->listItems($path);
    }

    /**
     *
     * @param type $path
     * @return type
     */
    public function storeItem($path, $location, $data, $options) {
        $path = str_replace('//','/',$path);
        $loc = ($location=='local')?'_local':'_cdn';
        return $this->$loc->storeItem($path, $data, $options);
    }

    /**
     *
     * @param type $path
     * @return type
     */
    public function fetchItem($path, $location) {
        $path = str_replace('//','/',$path);
        $loc = ($location=='local')?'_local':'_cdn';
        return $this->$loc->fetchItem($path);
    }

    /**
     *
     * @param type $path
     * @return type
     */
    public function deleteItem($path, $location) {
        $path = str_replace('//','/',$path);
        $loc = ($location=='local')?'_local':'_cdn';
        return $this->$loc->deleteItem($path);
    }

    /**
     *
     * @param type $path
     * @return type
     */
    public function copyItem($path, $location, $destpath) {
        $path = str_replace('//','/',$path);
        $loc = ($location=='local')?'_local':'_cdn';
        return $this->$loc->copyItem($path, $destpath);
    }

    /**
     *
     * @param type $path
     * @return type
     */
    public function moveItem($path, $location, $destpath) {
        $path = str_replace('//','/',$path);
        $loc = ($location=='local')?'_local':'_cdn';
        return $this->$loc->moveItem($path, $destpath);
    }

    /**
     *
     * @param type $path
     * @return type
     */
    public function renameItem($path, $location, $newname) {
        $path = str_replace('//','/',$path);
        $loc = ($location=='local')?'_local':'_cdn';
        return $this->$loc->storeItem($path, $newname);
    }

    /**
     *
     * @param type $path
     * @return type
     */
    public function fetchMetadata($path, $location) {
        $path = str_replace('//','/',$path);
        $loc = ($location=='local')?'_local':'_cdn';
        return $this->$loc->fetchMetadata($path);
    }

    /**
     *
     * @param type $path
     * @return type
     */
    public function storeMetadata($path, $location, $metadata) {
        $path = str_replace('//','/',$path);
        $loc = ($location=='local')?'_local':'_cdn';
        return $this->$loc->storeMetadata($path, $metadata);
    }

    /**
     *
     * @param type $path
     * @return type
     */
    public function deleteMetadata($path, $location) {
        $path = str_replace('//','/',$path);
        $loc = ($location=='local')?'_local':'_cdn';
        return $this->$loc->deleteMetadata($path);
    }

    /**
     *
     * @param type $path
     * @return type
     */
    public function getClient($location) {
        $loc = ($location=='cdn')?'_cdn':'_local';
        return $this->$loc->getClient();
    }

    /**
     *
     * @param type $path
     * @return type
     */
    public function getBaseUrl($location) {
        if($location=='cdn'){
            return $this->options['cdn']['hostname'];
        } else {
            return $this->options['local']['hostname'].'/';
        }
    }

    /**
     *
     * @param type $path
     * @return type
     */
    public function getFileArray($path, $location) {
        $details = $this->getFileDetails($path);
        $objname = $details['info']['filename'];
        $link = $this->options[$location]['hostname'].$path;
        $trupath = str_replace($details['info']['basename'],'',$path);
        $this->log->debug($details);
        if($details['type'] == 'file'){
            $mimes = explode('/', $details['mimetype']);
            $details['type'] = $mimes[0];
        }
        $file['name']          = $objname;
        $file['title']         = $details['info']['filename'];
        $file['longtitle']     = $details['info']['filename'];
        $file['description']   = $details['info']['filename'];
        $file['type']          = $details['type'];
        $file['mime']          = $details['mimetype'];
        $file['path']          = rtrim($trupath,"/");
        $file['link']          = $link;
        $file['position']      = 1;
        $file['published']     = 0;
        return $file;
    }

    /**
     *
     * @param type $path
     * @return type
     */
    public function getFolderArray($path, $location, $i = 0) {
        $files = array();
        $ignore = "v.";
        $path = str_replace('//','/',$path);
        $loc = ($location=='local')?'_local':'_cdn';
        $this->log->debug($loc."- path:".$path);
        $objects = $this->$loc->listItems($path);
        if(is_array($objects)) {
            foreach ($objects as $objname) {
                #Validate names.
                $start = substr($objname, 0, strlen($ignore));
                if((!in_array($objname, self::$ignore))&&($ignore != $start)) {
                    #Validate names, check for restricted files.
                    $link = ($location=='cdn')?$this->options[$location]['hostname'].'/'.$this->options[$location]['bucket_name'].$path."/".$objname:$this->options[$location]['hostname'].$path."/".$objname;
                    $link = ($location=='cdn')?str_replace('//','/',$link):str_replace('//','/',$link);
                    $details = $this->getFileDetails($path."/".$objname);
                    if($details['type'] == 'file'){
                        $mimes = explode('/', $details['mimetype']);
                        $details['type'] = $mimes[0];
                    }
                    $files[$i]['name']          = $objname;
                    $files[$i]['title']         = $details['info']['filename'];
                    $files[$i]['longtitle']     = $details['info']['filename'];
                    $files[$i]['description']   = $details['info']['filename'];
                    $files[$i]['type']          = $details['type'];
                    $files[$i]['mime']          = $details['mimetype'];
                    $files[$i]['path']          = rtrim($path,"/");
                    $files[$i]['link']          = $link;
                    $files[$i]['position']      = $i;
                    $files[$i]['published']     = 0;
                    if($location=='local') {
                        $localpath = realpath($this->$loc->getClient()).$path."/".$objname;
                        if(is_dir($localpath)) {
                            $kids = $this->getFolderArray($path.'/'.$objname, $location, $i);
                            foreach ($kids as $key => $kid) {
                                $i++;
                                $files[$i] = $kid;
                            }
                            unset($kids);
                        }
                    } else {
                        $pos = strrpos($objname,'/');
                        $lastpos = strlen($objname)-1;
                        if(($pos!==false)&&(($pos==$lastpos))){
                            $folders = explode('/', $objname);
                            $foldername = $folders[count($folders)-2];
                        }
                    }
                }
                $i++;
            }
            #TODO S3 returns all objects without respect to path. we therefore need to explode the pathes, merge em and build a nicely nested array.
        }
        return $files;
    }

    protected function getFileDetails($path, $loc = 'local') {
        $details = array();
        $localpath = str_replace('//','/',WEB_PATH.'/assets/'.$path);
        if($loc=='local') {
            $details['info'] = pathinfo($localpath);
            $details['stats'] = stat($localpath);
            $details['type'] = filetype($localpath);
            $details['mimetype'] = RackspaceCloudfilesService::getMimeType($localpath);
        }
        return $details;
    }
}