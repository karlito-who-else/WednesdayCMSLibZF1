<?php
//namespace Wednesday\Controller\Action\Helper;

use Zend_Controller_Action_Helper_Abstract as ControllerActionHelper,
    Wednesday\qqUploader\qqFileUploader;
/**
 * Description of MultipleUploader
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author mrhelly
 */
class Wednesday_Controller_Action_Helper_MultipleUploader extends ControllerActionHelper {

    /*
    //list of valid extensions, ex. array("jpeg", "xml", "bmp")
    $allowedExtensions = array();
    //max file size in bytes
    $sizeLimit = 10 * 1024 * 1024;
    $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
    $result = $uploader->handleUpload('uploads/');
    //to pass data through iframe you will need to encode all html tags
    echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
    */

    /**
     *
     * @var array
     */
    private $allowed;

    /**
     *
     * @var int
     */
    private $maxsize;

    /**
     *
     * @var qqFileUploader
     */
    private $uploader;

    /**
    *
    */
    public function init() {
        $this->allowed = array("jpeg", "jpg", "gif", "png", "mov", "mp4");
        $this->maxsize = 10 * 1024 * 1024;
        $this->uploader = new qqFileUploader($this->allowed, $this->maxsize);
    }
}

