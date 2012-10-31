<?php
namespace Wednesday\Renderers;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \ZendX_JQuery_View_Helper_JQuery as JQueryHelper,
    \Wednesday\Resource\Containers as ResourceContainers,
    \Wednesday\Resource\Service as ResourceService;

/**
 * Description of ResourceVideo
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author venelin
 */
class ResourceVideoJS implements Renderer {

    private $_resource;
    private $_baseuri;
    private $_basepath;
    private $_options;
    private $_defaultspath;


    public function __construct($resource, $options = false) {

        $this->_resource = $resource;

        $this->_options = $options;
        #TODO Hookin CDNmanager ()
        $resources = ResourceService::getInstance();
        $dest = ($resource->cdn == 1)?'cdn':'local';
        $this->_baseuri = $resources->getBaseUri($dest);
        $this->_basepath = $resources->getBaseUri('local');
        $this->_defaultspath = '/themes/admin/img/custom/';
    }

    public function __toString() {
        return $this->render();
    }

    public function render() {
		// Checks the users device and if a mobile device, it redirects them too the mobile version of our site
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        #Load WURFL data.
        $userAgent = $bootstrap->getResource('useragent');

        $dua = $userAgent->getDevice();
        //$is_mobile = $this->dua->getFeature('is_mobile');
        //$is_desktop = $this->dua->getFeature('is_desktop');
        $device = $dua->getFeature('device');

		$touchDevices = array("ipad", "iphone", "ipod", "android", "webos", "blackberry");

		if(in_array($device, $touchDevices)) {
			$touchDevice = 'true';
		}
		else {
			$touchDevice = 'false';
		}

        $bootstrap = Front::getInstance()->getParam('bootstrap');

        $bootstrap->view->inlineScript()->appendFile("/library/js/video/3.2.0/video.min.js", 'text/javascript');

//        $rendered = "";
        $log = $bootstrap->getResource('Log');
        $data = 'data-id="'.$this->_resource->id.'" ';
        $class = (isset($this->_options['class'])===true)?$this->_options['class']:"video-js vjs-default-skin moncler_player";
//        $id = (isset($this->_options['id'])===true)?'id="'.$this->_options['id'].'" ':"";
        $dims = (isset($this->_options['width'])===true)?' width="'.$this->_options['width'].'" ':" ";
        $dims .= (isset($this->_options['height'])===true)?'height="'.$this->_options['height'].'" ':" ";
        $homepage = (isset($this->_options['dynamicSizing'])===true)?true:false;
//        if($homepage) {
//            $dims = 'height="100%" width="100%" ';
//        }
        $preload = (isset($this->_options['preload']) === true) ? $this->_options['preload'] : 'none'; //auto|metadata|none
        $controls = ($this->_options['controls'] === true || $touchDevice === true) ? ' controls' : ''; //true|false
        $autoplay = ($this->_options['autoplay'] === true) ? ' autoplay' : ''; //true|false
        $loop = (isset($this->_options['loop']) === true) ? ' loop="loop"' : ''; //true|false
        $muted = (isset($this->_options['muted']) === true) ? ' muted="muted"' : '';//true|false
        $scaling = (isset($this->_options['scaling']) === true) ? $this->_options['scaling'] : 'aspectratio';//true|false
        $setup = (isset($this->_options['setup']) === true) ? $this->_options['setup'] : '{}';//true|false
        //'fill';


        $resourceuri = str_replace($this->_basepath, $this->_baseuri, $this->_resource->link);

        $resourceuri = $this->_defaultspath . 'icon-video.png';

        $projVar = 'iP'.$this->_resource->id;
//        //get the additional video sources (variations via metadata)
        $vsources = "";
        $js_vsources = "";
        if(count($this->_resource->metadata) > 0){
	        
            foreach($this->_resource->metadata as $md_key=>$video_variation){
                if($video_variation->type != 'string') {
                    $entClass = str_replace('Metadata', '', $video_variation->type);
                    $video = $bootstrap->getResource('doctrine')->getEntityManager()->getRepository($entClass)->find($video_variation->content);
                    if(isset($video)) {
                        $log->err($this->_resource->id);
                        $log->info($video->id);
                        $log->info($this->_resource->mimetype);
                        $log->info($video->mimetype);
                        if(($video->id > 0)&&($this->_resource->mimetype != $video->mimetype)&&($video->type != 'image')) {
                            //data-id=\"".$video->id."\" data-id=\"".$video->mimetype."-".$video->type."\"
                            $vsources = $vsources."\n\t\t\t<source src=\"".$video->link."\" type=\"".$video->mimetype."\" />";
                            $js_vsources .= "\n\t\t\t".($md_key+1).":{src:'{$video->link}', type: '{$video->mimetype}'},";
                        }
                    }
                }
            }
        }
        if(strlen($js_vsources)>0) {
        	$tmp = ",\n\t\t\t".$js_vsources;
	        $js_vsources = rtrim($tmp,",");
        }
        $vsources .= "\n\t\t\t".'<source src="'.$this->_resource->link.'" type="'.$this->_resource->mimetype.'" />';

$html5video = <<<VIDEO5
                    <video id="{$projVar}" class="{$class}" {$data}{$dims}{$controls}{$autoplay}{$loop} poster="{$this->_resource->link}.poster.jpg" title="{$this->_resource->title}" preload="{$preload}" data-setup="{$setup}">
                        {$vsources}
                    </video>
VIDEO5;


//            if($this->_resource->getMetadata('autoplay')->content == 'hover'){
//                $play_hover = "
//                , function(player){
//                    player.addListener('mouseenter', function () { player.setPlay(); });
//                    player.addListener('mouseleave', function () { player.setPause(); });
//                }";
//            }

$javascript = <<<SOE
        var \$j = jQuery.noConflict();
        /* <![CDATA[ */
        // instantiate Projekktor
        \$j(document).ready(function() {
            window.playerType = 'videojs';
            var {$projVar}Player = _V_("{$projVar}");
            var height = \$j("#{$projVar}").parent().height();
            var width = \$j("#{$projVar}").parent().width();

            var touchDevice = {$touchDevice};

            _V_("{$projVar}").ready(function(){
                {$projVar}Player = this;
                if(\$j('html').hasClass('template-monduck-gallery')){
                    {$projVar}Player.pause();
                }
                else{
                    {$projVar}Player.play();
                }
            });
            if({$homepage}){
                \$j(window).bind
                (
                    'resize',
                    function()
                    {
                        console.log('=============================================');
                        height = \$j("#{$projVar}").parent().height();
                        width = \$j("#{$projVar}").parent().width();
                        var aspectR = width/{$this->_options['width']}
                        //console.log('[height -  '+height +'][width -  '+width +']');

                        var newWidth = Math.floor({$this->_options['width']}*aspectR);
                        var newHeight = Math.floor({$this->_options['height']}*aspectR);
                        if(newWidth<width || newHeight<height)
                        {
                            //console.log('CHANGE'+newWidth+'x'+newHeight+"______"+{$projVar}Player.width()+", "+{$projVar}Player.height());
                            aspectR = height/{$this->_options['height']}
                            newWidth = Math.floor({$this->_options['width']}*aspectR);
                            newHeight = Math.floor({$this->_options['height']}*aspectR);
                        }
                        //console.log('[left -  '+((newWidth-width) / 2)*-1 +'][top -  '+((newHeight-height) / 2)*-1  +']');

                         if(((newWidth) / 2)>0){
                            \$j("#{$projVar}").css("left", function(){return ((newWidth-width) / 2)*-1 });
                            \$j("#{$projVar}").css("top", function(){return ((newHeight-height) / 2)*-1 });
                         }

                        //console.log('[newHeight -  '+newHeight.toString() +'][newWidth -  '+newWidth.toString() +']');
    //                    //window.{$projVar}Player.width(newWidth.toString());
    //                    //window.{$projVar}Player.height(newHeight.toString());
                          console.warn('['+newHeight.toString() +'x'+newWidth.toString() +']');
                          _V_("{$projVar}").size(newWidth.toString(), newHeight.toString());
                    }
                        
                );
            }

//            {$projVar}Player.addListener('fullscreen', FullScreenCheck );
        });
	/* ]]> */
SOE;
            $bootstrap->view->inlineScript()->appendScript($javascript, 'text/javascript');

        if($touchDevice === true) {
	        $html5video = '<img src="' . $this->_resource->link . '.poster.jpg" alt="Poster" />';
        }

        if(isset($this->_options['wrap_video_div']) === true) {
            $html = '<div class="moncler_player moncler_player_centered scale scale-height">'."\n".$html5video."\n".'</div>';
        } else {
            $html = $html5video;
        }

        return $html;
    }
}
