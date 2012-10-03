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
 * Description of Generate
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author jamesh
 */
class Generate {
    const RESOURCES     = "Application\Entities\MediaResources";
    const VARIATIONS    = "Application\Entities\MediaVariations";
    const METADATA      = "Wednesday\Models\MetaData";

    /**
     * array config options.
     * @var array $options
     */
    protected $config;

    /**
     * cache object.
     * @var \Zend_Cache $cache
     */
    protected $cache;

    /**
     * Doctrine\ORM\EntityManager The entity manager used by this query object.
     * @var \Doctrine\ORM\EntityManager $_em
     */
    protected $_em;

    /**
     * Access to .
     * @var \Zend_Log $log
     */
    public $log;

    /**
     *
     */
    public function __construct() {
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->_em = $bootstrap->getContainer()->get('entity.manager');
        $this->log = $bootstrap->getResource('Log');
        $this->config = $bootstrap->getContainer()->get('config');
        $this->log->debug(get_class($this)."::__construct");
    }

    /**
     * @method purgeResources
     * @param array $files
     * @return null
     */
    public function purgeResources($files) {
        $success = array();
        foreach($files as $key => $file) {
              $success[$key] = $this->purgeResource($file);
        }
        $this->log->debug($success);
    }

    /**
     * @method purgeResource
     * @param type $file
     * @return boolean
     */
    public function purgeResource($file) {
        $exists = $this->_em->getRepository(self::RESOURCES)->findOneByLink($file['link']);
        if (isset($exists) === true) {
            if(file_exists(WEB_PATH . $file['link'])===false) {
                //TODO Check Children?
                $this->log->debug("Removing Orphan Resource ".$file['link']." (".$exists->id.")");
                $this->_em->remove($exists);
                $this->_em->flush();
                $this->_em->clear();
                return true;
            }
            return false;
        }
    }

    /**
     * @method storeResources
     * @param type $files
     * @return null
     */
    public function storeResources($files) {
        $this->log->debug(get_class($this)."::storeResources");
        $success = array();
        foreach($files as $key => $file) {
             $success[$key] = $this->storeResource($file);
        }
        $this->log->debug($success);
    }

    /**
     * @method storeResource
     * @param array $file
     * @return array
     */
    public function storeResource($file) {
        $this->log->debug(get_class($this)."::storeResource");
        $parent = $this->_em->getRepository(self::RESOURCES)->findOneByLink('/assets' . $file['path']);
        $exists = $this->_em->getRepository(self::RESOURCES)->findOneByLink($file['link']);
        $this->log->debug($parent->id."::".'/assets' . $file['path']." - ".$exists->id."::".$file['link']);
        $resource = false;

        if (isset($exists) === true) {
            $this->log->debug("Update Resource ".$file['link']." (".$exists->id.")");
            if ($exists->link == $file['link']) {
                $resource = $exists;
            } else {
                //Something went petetong.
            }
        } else {
            $this->log->debug("Create Resource ".$file['link']);
            $resource = new MediaResources();
            $resource->name         = $file['name'];
            $resource->parent       = $parent;
            $resource->title        = $file['title'];
            $resource->longtitle    = $file['longtitle'];
            $resource->summary      = $file['summary'];
            $resource->description  = $file['description'];
            $resource->type         = $file['type'];
            $resource->mimetype     = $file['mime'];
            $resource->path         = $file['path'];
            $resource->link         = $file['link'];
            $resource->sortorder    = $file['position'];
            $resource->cdn          = $file['published'];
            //Store resource.
            $this->_em->persist($resource);
            $this->_em->flush();
        }
        if(isset($resource->parent->id)===false) {
            $resource->parent   = $parent;
        }
        $resource->type         = $file['type'];
        $resource->mimetype     = $file['mime'];
        $resource->path         = $file['path'];
        $resource->link         = $file['link'];
        $resource->sortorder    = $file['position'];
        $resource->cdn          = $file['published'];
        //Store resource.
        $this->_em->persist($resource);
        $this->_em->flush();
//        $this->_em->detach($resource);
        //Handle Variations
        $file['entity'] = $resource;
        $success = array();
        if($this->config['settings']['application']['asset']['manager']['variations']['generate'] == true) {
            $success = $this->createFileVariations($file);
            $this->log->debug($success);
        }
        unset($parent);
        unset($exists);
        return $success;
    }


    /**
     * @method createFileVariations
     * @param array $file
     * @return array
     */
    public function createFileVariations($file) {
        $this->log->debug(get_class($this)."::createVariations(".$file['link'].")");
        $success = array();
        $variations = $this->getVariationsForAsset($file);
        $filemeta = array();
//        $logmeta = array();
        if(isset($file['entity'])===true) {
            //Check Metadata for Resource Entity.
            foreach($file['entity']->metadata as $metadata) {
                $filemeta[$metadata->title] = $metadata;
                if(isset($variations[$metadata->title])===false) {
                    $this->log->debug("Variation ".$metadata->title." not found in list for generation, but already exists");
                }
//                $logmeta[$metadata->title] = array('id' => $metadata->content, 'type' => $metadata->type);
            }
//            $this->log->debug($logmeta);
        }
        foreach($variations as $variation => $varOptions) {
            if(isset($filemeta[$variation])===true) {
                $this->log->debug($variation." Already Exists!");
            } else {
                $this->log->debug($variation." Doesn't Exist!");
            }
            $success[$variation] = $this->createVariation($file, $variation, $varOptions->overwrite, $varOptions->scale, $varOptions->width, $varOptions->height);
            if($success[$variation] != false) {
                //Create metadata...
                if(isset($filemeta[$variation])) {//Metadata Exists and is linked, just update the references...
                    $metadata = $filemeta[$variation];
                    $varent = $this->_em->getRepository(self::VARIATIONS)->findOneBy(array('link'=>$success[$variation]));
                } else {
                    $metadata = new MetaData();
                    $varent = new MediaVariations();
                }
                $localpath = str_replace('//','/',WEB_PATH.$success[$variation]);
                $this->log->debug("Stat: ".$localpath);
                if(file_exists($localpath)) {
                    $info = pathinfo($localpath);
                    $varent->title = $variation;
                    $varent->longtitle = $info['basename'];
                    $varent->description = "";
                    $varent->type = filetype($localpath);
                    $varent->mimetype = RackspaceCloudfilesService::getMimeType(WEB_PATH . $success[$variation]);
                    $varent->link = $success[$variation];
                    $varent->path = $success[$variation];
                    $varent->stored = null;
                    $this->_em->persist($varent);
                    $this->_em->flush();
                    $metadata->title = $variation;
                    $metadata->type = self::VARIATIONS;
                    $metadata->content = $varent->id;
                    $this->_em->persist($metadata);
                    $this->_em->flush();
                    if(isset($filemeta[$variation])===false) {
                        $file['entity']->metadata->add($metadata);
                        $this->_em->persist($file['entity']);
                        $this->_em->flush();
                    }
                }
            }
        }
        $this->log->debug($success);
        return $success;
    }

    /**
     * @method createVariations
     *          create variation files for assets on disk, regardless Resource Entity.
     * @param array $files
     * @return null
     */
    public function createVariations($files) {
        $this->log->debug(get_class($this)."::createVariations");
        $success = array();
        foreach($files as $key => $file) {
            $variations = $this->getVariationsForAsset($file);
            $success[$key] = array();
            foreach($variations as $variation => $varOptions) {
                $success[$key][$variation] = $this->createVariation($file, $variation, $varOptions->overwrite, $varOptions->scale, $varOptions->width, $varOptions->height);
            }
        }
        $this->log->warn($success);
    }

    /**
     * @method createVariation
     * @param array $file
     * @param string $sizename
     * @param boolean $overwrite
     * @param boolean $scale
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     * @return boolean
     */
    public function createVariation($file, $sizename, $overwrite = true, $scale = false, $width=0, $height=0, $x=0, $y=0) {
        $this->log->debug(get_class($this)."::createVariation");
        $generated = false;
//        $this->log->debug($file['type']);
        switch($file['type']) {
            case 'image':
                $generated = $this->createImageVariation($file, $sizename, $overwrite, $scale, $width, $height, $x, $y);
                break;
            case 'video':
                $generated = $this->createVideoVariation($file, $sizename, $overwrite, $scale, $width, $height, $x, $y);
                break;
            default:
                //Throw Error? - Can't generate.
                $generated = array('type' => $file['type']);
                break;
        }
        return $generated;
    }

    /**
     *
     * @param array $file
     * @return array
     */
    protected function getVariationsForAsset($file) {
        $variations = array();
        switch($file['type']) {
            case 'image':
                $this->log->debug(get_class($this)."::getVariationsForAsset");
                foreach ($this->config['settings']['application']['asset']['manager']['size'] as $sizename => $sizemap) {
                    $variations[$sizename] = (object) array(
                        'scale'=> $sizemap['scale'],
                        'overwrite' => true,
                        'width' => $sizemap['width'],
                        'height' => $sizemap['height']
                    );
                }
                break;
            case 'video':
                foreach ($this->config['settings']['application']['asset']['manager']['video']['size'] as $sizename => $sizemap) {
                    $variations[$sizename] = (object) array(
                        'scale'=> $sizemap['scale'],
                        'overwrite' => false,
                        'width' => $sizemap['width'],
                        'height' => $sizemap['height']
                    );
                }
                break;
            default:
                //Throw Error? - Can't generate.
                break;
        }
//        $this->log->debug($variations);
        return $variations;
    }

    /**
     * @method createImageVariation
     * @param array $file
     * @param string $sizename
     * @param boolean $overwrite
     * @param boolean $scale
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     * @return boolean
     */
    protected function createImageVariation($file, $sizename, $overwrite = true, $scale = false, $width=0, $height=0, $x=0, $y=0) {
        $this->log->debug(get_class($this)."::createImageVariation");
        $objname = $file['name'];
        $ignore = $this->config['settings']['application']['asset']['manager']['variations']['ignore'];
        $version = $ignore . $sizename . '.' . $objname;
        $filename = str_replace($objname, $version, $file['link']);
        if (file_exists(WEB_PATH . $filename) && !$overwrite) {
//            $hmm = ($overwrite)?"true":"false";
//            $this->log->info($hmm);
            $this->log->info("Won't Generate Variation Exists!( ".WEB_PATH . $filename.")");
            return $filename; //Exists.
        } else {
            if((extension_loaded('imagick'))&&($this->config['settings']['application']['asset']['manager']['variations']['generate'] == true)) {
                $this->log->debug("Generate Variation! (".WEB_PATH . $filename.")");
                $this->generateImageFile($filename, $file, $width, $height, $x, $y);
            } else {
                $this->log->debug("Can't Generate Variations!(".WEB_PATH . $filename.")");
//                $hmm = (extension_loaded('imagick'))?"true":"false";
//                $hmmm = ($this->config['settings']['application']['asset']['manager']['variations']['generate'])?"true":"false";
//                $this->log->debug($hmm."&&".$hmmm);
                if (file_exists(WEB_PATH . $filename)===false) {
                    return false;
                }
            }
            return $filename;
        }
    }

    public function cropImageFile($variation, $master, $width=0, $height=0, $x=0, $y=0) {
        if(extension_loaded('imagick')) {
            $image = new \Imagick(WEB_PATH . $master['link']);
            $image->cropImage($width, $height, $x, $y);
            $variationpath = WEB_PATH . $variation;
            $image->writeImage($variationpath);
            chmod($variationpath, 0777);
            $image->destroy();
            unset($image);
        }
    }

    protected function generateImageFile($variation, $master, $width=0, $height=0, $x=0, $y=0) {
        if(extension_loaded('imagick')) {
            $image = new \Imagick(WEB_PATH . $master['link']);
            $geo = $image->getImageGeometry();
            $curntar = $geo['width'] / $geo['height'];
            $targtar = $width / $height;
            if($curntar != $targtar) {
                $targwidth = $geo['width'];
                $targheight = $geo['height'];
                if($targtar > 1) {
                    //Landscape
                    if($curntar > 1) {
                        $targwidth = $geo['width'];
                        $targheight = (int)($targtar * $geo['height']);
                        $x = $y = 0;
                    } else if($curntar < 1) {
                        $targwidth = (int)($targtar * $geo['width']);
                        $targheight = $geo['height'];
                        $x = floor(($geo['width'] - $targwidth)  / 2);
                        $y = 0;
                    } else if($curntar == 1) {
                        $targwidth = (int)($targtar * $geo['width']);
                        $targheight = $geo['height'];
                    }
                } else if($targtar == 1) {
                    //Handle Square
                    $targwidth = $geo['width'];
                    $targheight = $geo['width'];
                } else {
                    //Handle Portrait
                    $targwidth = $geo['width'];
                    $targheight = (int)($targtar * $geo['height']);
                }
            }
            //TODO: Sort this out so it correct sizes things.
            $image->adaptiveResizeImage($width, $height, true);
            $geo2 = $image->getImageGeometry();
//            $this->log->info($geo['width']."x".$geo['height']);
//            $this->log->info($width."x".$height);
//            $this->log->info($geo2['width']."x".$geo2['height']);
//               $image->cropImage($width, $height, $x, $y);
            $variationpath = WEB_PATH . $variation;
            $image->writeImage($variationpath);
            chmod($variationpath, 0777);
            $image->destroy();
            unset($image);
        }
    }

    /**
     * @method createVideoVariation
     * @param array $file
     * @param string $sizename
     * @param boolean $overwrite
     * @param boolean $scale
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     * @return boolean
     */
    protected function createVideoVariation($file, $sizename, $overwrite = true, $scale = false, $width=0, $height=0, $x=0, $y=0) {
        $this->log->info(get_class($this)."::createVideoVariation");

        $objname = $file['name'];
        $ignore = $this->config['settings']['application']['asset']['manager']['variations']['ignore'];
        $version = $ignore . $objname;//$sizename . '.' .
        $ext = ($sizename!='mobile')?$sizename:'3gp';
        $fileparams = pathinfo(WEB_PATH . $file['link']);
        $filename = str_replace($fileparams['extension'], $ext, str_replace($objname, $version, $file['link']));
        if (file_exists(WEB_PATH . $filename) && !$overwrite) {
            return $filename; //Exists.
        } else {
//            if($sizename == 'poster') {
//                $version = $ignore . $sizename . '.' . $objname;
//                $ext = 'jpg';
//                $filename = $filename = str_replace($fileparams['extension'], $ext, str_replace($objname, $version, $file['link']));
//            }
            if($this->config['settings']['application']['asset']['manager']['variations']['generate'] == true) {
                $this->generateVideoFile($filename, $file['type'], $file, $width, $height, $x, $y);
            } else {
                $this->log->debug("Can't Generate Variations!");
            }
            return $filename;
        }
    }

    protected function generateVideoFile($variation, $filetype, $master) {
            //increase the max exec time
            
            if(extension_loaded('ffmpeg')) {
                $this->log->info("ffmpeg Loaded");
            }
            $ret = "";
            ini_set('max_execution_time', 0);
            $this->log->info(date('Y-m-d H:i:s')." | Start processing: ".$master['link']." = ".$variation);
            switch ($filetype) {
                case '3gp':
                case 'mobile':
                    /**
                    * For this file format, there are predefined valid sizes (-s parameter):
                    * 128x96, 176x144, 352x288, 704x576, and 1408x1152
                    */
                    $ret = exec("ffmpeg -y -i " . WEB_PATH . $master['link'] . " -r 20 -s 352x288 -b:v 400k -acodec libfaac -ac 1 -ar 8000 -ab 24k " . WEB_PATH . $variation);
                    $mimetype = 'video/3gpp';   //double p in 3gpp MIME type!
                    break;
                case 'ogv':
                    $ret = exec("ffmpeg2theora " . WEB_PATH . $master['link']);
                    $mimetype = 'video/ogg';
                    break;
                case 'webm':
                    $ret = exec("ffmpeg -y -i " . WEB_PATH . $master['link'] . " -b 1500k -vcodec libvpx -acodec libvorbis -ab 160000 -f webm -g 30 " . WEB_PATH . $variation);
                    $mimetype = 'video/webm';
                    break;

                case 'm4v':
                case 'mp4':
                    $ret = exec("ffmpeg -y -i " . WEB_PATH . $master['link'] . " -vcodec mpeg4 -f mp4 -qmax 8 " . WEB_PATH . $variation);
                    $mimetype = 'video/mp4';
                    break;
                default:
                    break;
            }
            chmod(WEB_PATH . $variation, 0777);
            $this->log->warn($ret);
            $this->log->info(date('Y-m-d H:i:s')." | End processing: ".$master['link']." = ".$variation."(".$mimetype.")");
    }
}
