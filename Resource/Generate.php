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
        $this->log->info($success);
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
                $this->log->err("Removing Orphan Resource ".$file['link']." (".$exists->id.")");
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
        $this->log->info(get_class($this)."::storeResources");
        $success = array();
        foreach($files as $key => $file) {
             $success[$key] = $this->storeResource($file);
        }
        $this->log->info($success);
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
            $this->log->info("Create Resource ".$file['link']);
//            $resource = new MediaResources();
//            $resource->name         = $file['name'];
//            $resource->title        = $file['title'];
//            $resource->longtitle    = $file['longtitle'];
//            $resource->summary      = $file['summary'];
//            $resource->description  = $file['description'];
//            $resource->type         = $file['type'];
//            $resource->mimetype     = $file['mime'];
//            $resource->path         = $file['path'];
//            $resource->link         = $file['link'];
//            $resource->sortorder    = $file['position'];
//            $resource->cdn          = $file['published'];
//            //Store resource.
//            $this->_em->persist($resource);
//            $this->_em->flush();
        }
        
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
        $this->log->info(get_class($this)."::createVariations(".$file['link'].")");
        $success = array();
        $variations = $this->getVariationsForAsset($file);
        $filemeta = array();
//        $logmeta = array();
        if(isset($file['entity'])===true) {
            //Check Metadata for Resource Entity.
            foreach($file['entity']->metadata as $metadata) {
                $filemeta[$metadata->title] = $metadata;
                if(isset($variations[$metadata->title])===false) {
                    $this->log->err("Variation ".$metadata->title." not found in list for generation, but already exists");
                }
//                $logmeta[$metadata->title] = array('id' => $metadata->content, 'type' => $metadata->type);
            }
//            $this->log->err($logmeta);
            //$metadata = $this->_em->getRepository(self::METADATA)->findOneBy(array('content'=>$variation->id,'type'=>self::VARIATIONS));
        }
        foreach($variations as $variation => $varOptions) {
            if(isset($filemeta[$variation])===true) {
                $this->log->info($variation." Already Exists!");
            } else {
                $this->log->info($variation." Doesn't Exist!");
            }
            $success[$variation] = $this->createVariation($file, $variation, $varOptions->scale, $varOptions->overwrite, $varOptions->width, $varOptions->height);
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
                $success[$key][$variation] = $this->createVariation($file, $variation, $varOptions->scale, $varOptions->overwrite, $varOptions->width, $varOptions->height);
            }
        }
        $this->log->debug($success);
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
    public function createVariation($file, $sizename, $overwrite = false, $scale = false, $width=0, $height=0, $x=0, $y=0) {
        $this->log->debug(get_class($this)."::createVariation");
        $generated = false;
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
        return $generated;//Generated.
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
                        'overwrite' => false,
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
//        $this->log->info($variations);
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
    protected function createImageVariation($file, $sizename, $overwrite = false, $scale = false, $width=0, $height=0, $x=0, $y=0) {
        $this->log->debug(get_class($this)."::createImageVariation");
        $objname = $file['name'];
        $ignore = $this->config['settings']['application']['asset']['manager']['variations']['ignore'];
        $version = $ignore . $sizename . '.' . $objname;
        $filename = str_replace($objname, $version, $file['link']);
        if (file_exists(WEB_PATH . $filename) && !$overwrite) {
            return true; //Exists.
        } else {
            if(extension_loaded('imagick')&&($this->config['settings']['application']['asset']['manager']['variations']['generate'] == true)) {
                $this->generateImageFile($filename, $file['type'], $file, $width, $height, $x, $y);
            } else {
                $this->log->err("Can't Generate Variations!");
            }
            return $filename;
        }
    }

    protected function generateImageFile($variation, $filetype, $master, $width=0, $height=0, $x=0, $y=0) {
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
                    $targheight = (int)($targetar * $geo['height']);
                    $x = $y = 0;
                } else if($curntar < 1) {
                    $targwidth = (int)($targetar * $geo['width']);
                    $targheight = $geo['height'];
                    $x = floor(($geo['width'] - $targwidth)  / 2);
                    $y = 0;
                } else if($curntar == 1) {
                    $targwidth = (int)($targetar * $geo['width']);
                    $targheight = $geo['height'];
                }
            } else if($targtar == 1) {
                //Handle Square
                $targwidth = $geo['width'];
                $targheight = $geo['width'];
            } else {
                //Handle Portrait
                $targwidth = $geo['width'];
                $targheight = (int)($targetar * $geo['height']);                    
            }
        }
        $image->cropImage($width, $height, $x, $y);
        $variationpath = WEB_PATH . $variation;
        $image->writeImage($variationpath);
        chmod($variationpath, 0777);
        unset($image);
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
    protected function createVideoVariation($file, $sizename, $overwrite = false, $scale = false, $width=0, $height=0, $x=0, $y=0) {
        $this->log->debug(get_class($this)."::createVideoVariation");
        
        $objname = $file['name'];
        $ignore = $this->config['settings']['application']['asset']['manager']['variations']['ignore'];
        $version = $ignore . $objname;//$sizename . '.' . 
        $ext = ($sizename!='mobile')?$sizename:'3gp';
        $fileparams = pathinfo(WEB_PATH . $file['link']);
        $filename = str_replace($fileparams['extension'], $ext, str_replace($objname, $version, $file['link']));
        if (file_exists(WEB_PATH . $filename) && !$overwrite) {
            return true; //Exists.
        } else {
//            if($sizename == 'poster') {
//                $version = $ignore . $sizename . '.' . $objname;
//                $ext = 'jpg';
//                $filename = $filename = str_replace($fileparams['extension'], $ext, str_replace($objname, $version, $file['link']));
//            }
            if($this->config['settings']['application']['asset']['manager']['variations']['generate'] == true) {
                $this->generateVideoFile($filename, $file['type'], $file, $width, $height, $x, $y);
            } else {
                $this->log->err("Can't Generate Variations!");
            }
            return $filename;
        }
    }
    
    protected function generateVideoFile($variation, $filetype, $master) {
            //increase the max exec time
            ini_set('max_execution_time', 0);

            $this->log->info(date('Y-m-d H:i:s') . ' | Start processing: ' . WEB_PATH . $master['link']);
            switch ($filetype) {
                case '3gp':
                case 'mobile':
                    /**
                    * For this file format, there are predefined valid sizes (-s parameter):
                    * 128x96, 176x144, 352x288, 704x576, and 1408x1152
                    */
                    exec("ffmpeg -y -i " . WEB_PATH . $master['link'] . " -r 20 -s 352x288 -b:v 400k -acodec libfaac -ac 1 -ar 8000 -ab 24k " . WEB_PATH . $variation);
                    $mimetype = 'video/3gpp';   //double p in 3gpp MIME type!
                    break;                
                case 'ogv':
                    exec("ffmpeg2theora " . WEB_PATH . $master['link']);
                    $mimetype = 'video/ogg';                    
                    break;
                case 'webm':
                    exec("ffmpeg -y -i " . WEB_PATH . $master['link'] . " -b 1500k -vcodec libvpx -acodec libvorbis -ab 160000 -f webm -g 30 " . WEB_PATH . $variation);
                    $mimetype = 'video/webm';                    
                    break;

                case 'm4v':
                case 'mp4':
                    exec("ffmpeg -y -i " . WEB_PATH . $master['link'] . " -vcodec mpeg4 -f mp4 -qmax 8 " . WEB_PATH . $variation);
                    $mimetype = 'video/mp4';                    
                    break;
                default:
                    break;
            }
            chmod(WEB_PATH . $variation, 0777);
            $this->log->info(date('Y-m-d H:i:s') . ' | End processing: ' . WEB_PATH . $master['link']);
    }
        
//    /**
//     *
//     */
//    protected function storeResource($file) {
//        #TODO Check to see if files have been added already.
//        $parent = $this->_em->getRepository(self::RESOURCES)->findOneByLink('/assets' . $file['path']);
//        $exists = $this->_em->getRepository(self::RESOURCES)->findOneByLink($file['link']);
//        if (isset($exists) === true) {
////            return;
//            if ($exists->link == $file['link']) {
//                $resource = $exists;
//                $exists = true;
//                $logdata .= "Updated Resource: " . $file['link'];
//            } else {
//                $logdata .= "Adding Resource: " . $file['link'];
//                $resource = new MediaResources();
//                #Don't reset copy for item if it has been set.
//                $resource->name         = (empty($resource->name))?$file['name']:$resource->name;
//                $resource->title        = (empty($resource->title))?$file['title']:$resource->title;//$file['title'];
//                $resource->longtitle    = (empty($resource->longtitle))?$file['longtitle']:$resource->longtitle;//$file['longtitle'];
//                $resource->summary      = (empty($resource->summary))?$file['summary']:$resource->summary;//$file['summary'];
//                $resource->description  = (empty($resource->description))?$file['description']:$resource->description;//$file['description'];
//            }
//        } else {
//            $logdata .= "Adding Resource:: " . $file['link'];
//            $resource = new MediaResources();
//            #Don't reset copy for item if it has been set.
//            $resource->name         = (empty($resource->name))?$file['name']:$resource->name;
//            $resource->title        = (empty($resource->title))?$file['title']:$resource->title;//$file['title'];
//            $resource->longtitle    = (empty($resource->longtitle))?$file['longtitle']:$resource->longtitle;//$file['longtitle'];
//            $resource->summary      = (empty($resource->summary))?$file['summary']:$resource->summary;//$file['summary'];
//            $resource->description  = (empty($resource->description))?$file['description']:$resource->description;//$file['description'];
//        }
////        #Don't reset copy for item if it has been set.
////        $resource->name         = (empty($resource->name))?$file['name']:$resource->name;
////        $resource->title        = (empty($resource->title))?$file['title']:$resource->title;//$file['title'];
////        $resource->longtitle    = (empty($resource->longtitle))?$file['longtitle']:$resource->longtitle;//$file['longtitle'];
////        $resource->summary      = (empty($resource->summary))?$file['summary']:$resource->summary;//$file['summary'];
////        $resource->description  = (empty($resource->description))?$file['description']:$resource->description;//$file['description'];
//        $resource->type = $file['type'];
//        $resource->mimetype = $file['mime'];
//        $resource->path = $file['path'];
//        $resource->link = $file['link'];
//        $resource->sortorder = $file['position'];
//        $resource->cdn = $file['published'];
//        $resource->stored = (object) $file;
////        $this->log->debug($file);
//        $parentset = (isset($parent) === true) ? 'true' : 'false';
////        $this->log->debug($parentset . ' - ' . $parent->id);
//        $plink = "";
//        if (isset($parent) === true) {
//            $resource->parent = $parent;
//            $plink = $parent->link;
//        }
//        #Only store variations for images.
//        if ($file['type'] == 'image') {
//            if(extension_loaded('imagick')&&($this->options['variations']['generate'] == true)) {
//                $this->createVariations($file);
//            } else {
//                #Don't die, just log.
//                $this->log->err("imagemagick not found, can't create new variations");
//            }
//            #Check for existing variations.
//            $metadatas = $this->storeVariations($file);
//            $this->log->debug(count($metadatas));
//            foreach ($metadatas as $metadata) {
//                if(!$resource->metadata->contains($metadata)) {
//                    $resource->metadata->add($metadata);
//                }
//            }
//        } else if($file['type'] == 'video') {
//            $metadatas = $this->createVideoVariationsSingular($file);
//            foreach ($metadatas as $metadata) {
//                if(!$resource->metadata->contains($metadata)) {
//                    $resource->metadata->add($metadata);
//                }
//            }
//        }
//        $this->log->info($logdata);
//        $this->_em->persist($resource);
//        $this->_em->flush();
//        //$resid = $resource;
//        $this->_em->detach($resource);
//        unset($parent);
//        unset($exists);
//        //unset($resource);
//        unset($metadatas);
//        $this->log->debug('storeResource:'.$file['link'].':'.$plink);
//        return $resource;
//    }
//
//    protected function createVariations($file, $overwrite = false) {
////        $objname = $file['name'];
////        $ignore = $this->options['variations']['ignore'];
//        foreach ($this->sizes as $sizename => $sizemap) {
//            //desktop variation
//            $this->log->debug('Create '.$sizename);
////            $this->log->debug($sizemap);
//            $this->createVariation($file, $sizename, $sizemap['scale'], $overwrite, $sizemap['width'], $sizemap['height']);
//        }
//        return true;
//    }
//
//    protected function createVariation($file, $sizename, $scale = false, $overwrite = false, $width=0, $height=0, $x=0, $y=0) {
//        $objname = $file['name'];
//        $ignore = $this->options['variations']['ignore'];
//        $version = $ignore . $sizename . '.' . $objname;
//        $filename = str_replace($objname, $version, $file['link']);
//
//        //Only overwrite existing thumbnails when specified
//        if (file_exists(WEB_PATH . $filename) && !$overwrite) {
//            return;
//        } else {
//            $image = new \Imagick(WEB_PATH . $file['link']);
//            #More logic to handle custom crops... $height,$width,$x,$y
////            
////            $geo = $image->getImageGeometry();
////            $currentar = $geo['width'] / $geo['height']; 
////            $targetar = $width / $height;
////            $x = $y = 0;
////            if($currentar != $targetar) {
////                $targwidth = $geo['width'];
////                $targheight = $geo['height'];
////                if($targetar > 1) {
////                    //Landscape
////                    if($currentar > 1) {
////                        $targwidth = $geo['width'];
////                        $targheight = (int)($targetar * $geo['height']);
////                        $x = $y = 0;
////                    } else if($currentar < 1) {
////                        $targwidth = (int)($targetar * $geo['width']);
////                        $targheight = $geo['height'];
////                        $x = floor(($geo['width'] - $targwidth)  / 2);
////                        $y = 0;
////                    } else if($currentar == 1) {
////                        $targwidth = (int)($targetar * $geo['width']);
////                        $targheight = $geo['height'];
////                    }
////                } else if($targetar == 1) {
////                    //Handle Square
////                    $targwidth = $geo['width'];
////                    $targheight = $geo['width'];                           
////                } else {
////                    //Handle Portrait
////                    $targwidth = $geo['width'];
////                    $targheight = (int)($targetar * $geo['height']);                    
////                }
////            $image->cropImage($targwidth, $targheight, $x, $y, $scale);
//            /*
//              if (isset($this->new_width))
//              {
//              $factor = (float)$this->new_width / (float)$width;
//              $this->new_height = $factor * $height;
//              }
//              else if (isset($this->new_height))
//              {
//              $factor = (float)$this->new_height / (float)$height;
//              $this->new_width = $factor * $width;
//
//              }
//             */
//                
////            } 
//
//            //TODO Handle scaling better.
//            $image->thumbnailImage($width, $height, $scale);
//            
//            $newImage = WEB_PATH . $filename;
//            $image->writeImage($newImage);
//            chmod($newImage, 0777);
//            unset($image);
//        }
//    }
//
//    public function cropVariation($file, $width=0, $height=0, $x=0, $y=0) {
//            $image = new \Imagick(WEB_PATH . $file['link_original']);
//            $image->cropImage($width, $height, $x, $y);
//            $newImage = WEB_PATH . $file['link'];
//            $image->writeImage($newImage);
//            chmod($newImage, 0777);
//            unset($image);
//
//    }
//
//    protected function storeVariations($file) {
//        $ignore = $this->options['variations']['ignore'];
//        $returnmeta = array();
//        $objname = $file['name'];
//        #Store Variations.
//        foreach ($this->sizes as $sizename => $sizemap) {
//            $version = $ignore . $sizename . '.' . $objname;
//            $filename = str_replace($objname, $version, $file['link']);
////            $this->log->debug($version);
////            $this->log->debug($filename);
//            if (file_exists(WEB_PATH . $filename)) {
//                $vexists = $this->_em->getRepository(self::VARIATIONS)->findOneByLink($filename);
//                if(isset($vexists) === true) {
////                    $this->log->debug('exists');
//                    $variation = $vexists;
////                    $vexists
//                    $metadata = $this->_em->getRepository('Wednesday\Models\MetaData')->findOneBy(array('content'=>$variation->id,'type'=>self::VARIATIONS));
//                    if(isset($metadata)===false) {
//                        $metadata = new MetaData();
//                        $metadata->title = $sizename;
//                        $metadata->type = self::VARIATIONS;
//                        $metadata->content = $variation->id;
//                        $this->_em->persist($metadata);
//                        $this->_em->flush();
//                    }
//                    $returnmeta[] = $metadata;
//                    //$variation->id;
//                    $logdata .= "Updated Variation: " . $version;
//                } else {
////                    $this->log->debug('not exists');
//                    $variation = new MediaVariations();
//                    $variation->title = $sizename;
//                    $variation->longtitle = $objname;
//                    $variation->description = "";
//                    $variation->type = filetype(WEB_PATH . $filename);
//                    $variation->mimetype = RackspaceCloudfilesService::getMimeType(WEB_PATH . $filename);
//                    $variation->link = $filename;
//                    $variation->path = $filename;
//                    $variation->stored = null;
//                    $this->_em->persist($variation);
//                    $this->_em->flush();
//                    #Assume metadata already exists
//                    if (isset($vexists) === false) {
//                        $metadata = new MetaData();
//                        $metadata->title = $sizename;
//                        $metadata->type = self::VARIATIONS;
//                        $metadata->content = $variation->id;
//                        $this->_em->persist($metadata);
//                        $this->_em->flush();
//                        $returnmeta[] = $metadata;
//                    }
//                    //unset($metadata);
//                    $logdata .= "Added Variation: " . $version;
//                    unset($variation);
////                    $this->_em->clear();
//                }
//                $this->log->debug($logdata);
//            }
//        }
//        return $returnmeta;
//    }
//
//    public function createVideoVariations() {
//
//        //get all the video files
//        $all_videos = $this->_em->getRepository(self::RESOURCES)->findByType('video');
//
//        /**
//         * For each of the video files, check against the variations
//         * to be sure there are no variations for that file already.
//         */
//        if(!empty($all_videos)){
//            foreach($all_videos as $parent_video){
//
//                //array of possible variations
//                $possible_variations = array(
//                    'ogv'=>str_replace('mp4', 'ogv', $parent_video->link),
//                    'webm'=>str_replace('mp4', 'webm', $parent_video->link),
//                    //'m4v'=>str_replace('m4v', 'mp4', $parent_video->link),
//                    'mp4'=>str_replace('mp4', 'mp4', $parent_video->link),
//                    '3gp'=>str_replace('mp4', '3gp', $parent_video->link),
//                );
//
//                foreach($possible_variations as $key=>$value){
//
//                    if(file_exists(WEB_PATH.$value)){
//                        unset($possible_variations[$key]);
//                    }
//                }
//
//                if(!empty($possible_variations)){
//                    //walk through the possible variations and check for matching records
//                    foreach($possible_variations as $filetype=>$filename){
//
//                        $vexists = $this->_em->getRepository(self::VARIATIONS)->findOneByLink($filename);
//
//                        //if there is no match, process with a new variation
//                        if(isset($vexists)===false) {
//
//                            //increase the max exec time
//                            ini_set('max_execution_time', round(filesize(WEB_PATH.$filename)/6300000));
//
//                            $this->log->debug(date('Y-m-d H:i:s').' | Start processing: '.WEB_PATH.$parent_video->link);
//
//                            if($filetype == 'ogv'){
//                                exec("ffmpeg2theora ".WEB_PATH.$parent_video->link);
//                                $mimetype = 'video/ogg';
//                            }elseif($filetype == 'webm'){
//                                exec("ffmpeg -y -i ".WEB_PATH.$parent_video->link." -b 1500k -vcodec libvpx -acodec libvorbis -ab 160000 -f webm -g 30 ".WEB_PATH.$filename);
//                                $mimetype = 'video/webm';
//                            }elseif($filetype == 'm4v'){
//                                exec("ffmpeg -y -i ".WEB_PATH.$parent_video->link." -vcodec mpeg4 -f mp4 -qmax 8 ".WEB_PATH.$filename);
//                                $mimetype = 'video/mp4';
//                            }elseif($filetype == '3gp'){
//                                /**
//                                 * For this file format, there are predefined valid sizes (-s parameter):
//                                 * 128x96, 176x144, 352x288, 704x576, and 1408x1152
//                                 */
//                                exec("ffmpeg -y -i ".WEB_PATH.$parent_video->link." -r 20 -s 352x288 -b:v 400k -acodec libfaac -ac 1 -ar 8000 -ab 24k ".WEB_PATH.$filename);
//                                $mimetype = 'video/3gpp';   //double p in 3gpp MIME type!
//                            }
//
//                            chmod(WEB_PATH.$filename, 0777);
//
//                            $this->log->debug(date('Y-m-d H:i:s').' | End processing: '.WEB_PATH.$parent_video->link);
//
//                            //create new variation
//                            $variation = new MediaVariations();
//                            $variation->title = $filename;
//                            $variation->longtitle = $filename;
//                            $variation->description = "";
//                            $variation->type = 'video';
//                            $variation->mimetype = $mimetype;
//                            $variation->link = $filename;
//                            $variation->path = $filename;
//                            $variation->stored = null;
//                            $this->_em->persist($variation);
//                            $this->_em->flush();
//
//
//                            $metadata = new MetaData();
//                            $metadata->title = 'videosources-'.$mimetype.'-'.$parent_video->id;
//                            $metadata->type = self::VARIATIONS;
//                            $metadata->content = $variation->id;
//                            $this->_em->persist($metadata);
//                            $this->_em->flush();
//
//                            $parent_video->metadata->add($metadata);
//
//                        }
//                    }//end foreach variations
//
//                    $this->_em->persist($parent_video);
//                    $this->_em->flush();
//                    $this->_em->clear();
//
//                    //break after the first
//                    break;
//
//                }//end if(!empty($possible_variations))
//            }//end foreach all videos
//        }//end if(!empty($all_videos))
//
//        return true;
//    }
//    
//    protected function createVideoVariationsSingular($file) {
//        $ignore = $this->options['variations']['ignore'];
//        $returnmeta = array();
//        $objname = $file['name'];
//        #Store Variations.
//        
//        //array of possible variations
//        $possible_variations = array(
//            'ogv'=>str_replace('mp4', 'ogv', $file['link']),
//            'webm'=>str_replace('mp4', 'webm', $file['link']),
//            'm4v'=>str_replace('m4v', 'mp4', $file['link']),
//            '3gp'=>str_replace('mp4', '3gp', $file['link']),
//        );
//
//        foreach ($possible_variations as $videoFormat => $filename) {
//            if (file_exists(WEB_PATH . $filename)) {
//                $vexists = $this->_em->getRepository(self::VARIATIONS)->findOneByLink($filename);               
////                if (isset($vexists) === true) {
//                //Let this method decide if it should create the variation.
//                $this->generateVideoFile($filename, $videoFormat, $file);
//                    //$this->log->debug('exists');
//                    $variation = $vexists;
////                    $vexists
//                    $metadata = $this->_em->getRepository('Wednesday\Models\MetaData')->findOneBy(array('content'=>$variation->id,'type'=>self::VARIATIONS));
//                    if(isset($metadata)===false) {
//                        $metadata = new MetaData();
//                        $metadata->title = $videoFormat;
//                        $metadata->type = self::VARIATIONS;
//                        $metadata->content = $variation->id;
//                        $this->_em->persist($metadata);
//                        $this->_em->flush();
//                    }
//                    $returnmeta[] = $metadata;
//                    //$variation->id;
//                    $logdata = "Updated Variation: " . $videoFormat;
//                } else {
//                    //$this->log->debug('not exists');
//                    $variation = new MediaVariations();
//                    $logdata = "Added Variation: " . $videoFormat;
//                    $variation->title = $videoFormat;
//                    $variation->longtitle = $objname;
//                    $variation->description = "";
//                    $variation->type = filetype(WEB_PATH . $filename);
//                    $variation->mimetype = RackspaceCloudfilesService::getMimeType(WEB_PATH . $filename);
//                    $variation->link = $filename;
//                    $variation->path = $filename;
//                    $variation->stored = null;
//                    $this->_em->persist($variation);
//                    $this->_em->flush();
//                    #Assume metadata already exists
//                    if (isset($vexists) === false) {
//                        $metadata = new MetaData();
//                        $metadata->title = $videoFormat;
//                        $metadata->type = self::VARIATIONS;
//                        $metadata->content = $variation->id;
//                        $this->_em->persist($metadata);
//                        $this->_em->flush();
//                        $returnmeta[] = $metadata;
//                    }
//                    //unset($metadata);
//                    unset($variation);
////                    $this->_em->clear();
//                }
//                $this->log->debug($logdata);
////            } 
//        }
//        return $returnmeta;
//    }
//    protected function generateVideoFile($variation, $filetype, $master) {
//
//        if (!file_exists(WEB_PATH . $variation)) {
//
//            //increase the max exec time
//            ini_set('max_execution_time', 0);
//
//            $this->log->info(date('Y-m-d H:i:s') . ' | Start processing: ' . WEB_PATH . $master['link']);
//
//            if ($filetype == 'ogv') {
//                exec("ffmpeg2theora " . WEB_PATH . $master['link']);
//                $mimetype = 'video/ogg';
//            } else if ($filetype == 'webm') {
//                exec("ffmpeg -y -i " . WEB_PATH . $master['link'] . " -b 1500k -vcodec libvpx -acodec libvorbis -ab 160000 -f webm -g 30 " . WEB_PATH . $variation);
//                $mimetype = 'video/webm';
//            } else if ($filetype == 'm4v') {
//                exec("ffmpeg -y -i " . WEB_PATH . $master['link'] . " -vcodec mpeg4 -f mp4 -qmax 8 " . WEB_PATH . $filename);
//                $mimetype = 'video/mp4';
//            } else if ($filetype == '3gp') {
//                /**
//                 * For this file format, there are predefined valid sizes (-s parameter):
//                 * 128x96, 176x144, 352x288, 704x576, and 1408x1152
//                 */
//                exec("ffmpeg -y -i " . WEB_PATH . $master['link'] . " -r 20 -s 352x288 -b:v 400k -acodec libfaac -ac 1 -ar 8000 -ab 24k " . WEB_PATH . $variation);
//                $mimetype = 'video/3gpp';   //double p in 3gpp MIME type!
//            }
//
//            chmod(WEB_PATH . $variation, 0777);
//
//            $this->log->info(date('Y-m-d H:i:s') . ' | End processing: ' . WEB_PATH . $master['link']);
//        }
//    }
}
