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
class ResourceVideoProjekktor implements Renderer {
    const RESOURCES     = "Application\Entities\MediaResources";
    const VARIATIONS    = "Application\Entities\MediaVariations";
    const METADATA      = "Wednesday\Models\MetaData";
    
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
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $entitymanager = $bootstrap->getResource('doctrine')->getEntityManager();
        $log = $bootstrap->getResource('Log');
        $rendered = "";
        $data = 'data-id="' . $this->_resource->id . '" ';
        $class = (isset($this->_options['class']) === true) ? $this->_options['class'] : "";
        $id = (isset($this->_options['id']) === true) ? 'id="' . $this->_options['id'] . '" ' : "";
        $dims = (isset($this->_options['width']) === true) ? ' width="' . $this->_options['width'] . '" ' : ' ';
        $dims .= (isset($this->_options['height']) === true) ? 'height="' . $this->_options['height'] . '" ' : ' ';
        $preload = (isset($this->_options['preload']) === true) ? $this->_options['preload'] : 'none'; //auto|metadata|none
        $controls = ($this->_options['controls'] === true) ? ' controls="controls"' : ''; //true|false
        $autoplay = ($this->_options['autoplay'] === true) ? ' autoplay="autoplay"' : ''; //true|false
        $loop = (isset($this->_options['loop']) === true) ? ' loop="loop"' : ''; //true|false
        $muted = (isset($this->_options['muted']) === true) ? ' muted="muted"' : ''; //true|false
        $scaling = (isset($this->_options['scaling']) === true) ? $this->_options['scaling'] : 'aspectratio'; //true|false

        $resourceuri = str_replace($this->_basepath, $this->_baseuri, $this->_resource->link);
        $resourceuri = $this->_defaultspath . 'icon-video.png';

        $projVar = 'iP' . $this->_resource->id;
        
        $vsources = "";
        $js_vsources = "";
        $videotypes = array(
            "mp4",
            "ogg",
            "webm"
        );
        if (count($this->_resource->metadata) > 0) {
            
            foreach ($this->_resource->metadata as $md_key => $video_variation) {
                $log->info($video_variation->type);
                $log->info($video_variation->title);
                if(in_array($video_variation->title, $videotypes)) {
                    $video = $entitymanager->getRepository(self::VARIATIONS)->find($video_variation->content);
                    if (($video->id > 0) && ($this->_resource->mimetype != $video->mimetype)) {
                        $vsources = $vsources . "\n\t\t\t<source src=\"" . $video->link . "\" type=\"" . $video->mimetype . "\" />";
                        $js_vsources .= "\n\t\t\t" . ($md_key + 1) . ":{src:'{$video->link}', type: '{$video->mimetype}'},";
                    }
                }
            }
        }
        if (strlen($js_vsources) > 0) {
            $tmp = ",\n\t\t\t" . $js_vsources;
            $js_vsources = rtrim($tmp, ",");
        }
        $vsources .= "\n\t\t\t" . '<source src="' . $this->_resource->link . '" type="' . $this->_resource->mimetype . '" />';

        $html5video = <<<VIDEO5
                    <video id="{$projVar}" class="{$class}" {$data}{$dims}{$controls}{$autoplay}{$loop} poster="{$this->_resource->link}.poster.jpg" title="{$this->_resource->title}" preload="{$preload}">
                        {$vsources}
                    </video>
VIDEO5;

        $play_hover = "";
        $autoplaymeta = $this->_resource->getMetadata('autoplay');
        if($autoplaymeta) {
            if($autoplaymeta->content == 'hover') {
                $play_hover = "
                    , function(player){
                        player.addListener('mouseenter', function () { player.setPlay(); });
                        player.addListener('mouseleave', function () { player.setPause(); });
                    }";
            }            
        }

        $projekktor = <<<SOE
                    var \$j = jQuery.noConflict();
                    /* <![CDATA[ */
                    // instantiate Projekktor
                    \$j(document).ready(function() {
                        var {$projVar} = projekktor('#{$projVar}', {
                            'imageScaling': '{$scaling}',
                            'videoScaling': '{$scaling}',
                            'height': {$this->_options['height']},
                            'width': {$this->_options['width']},
                            'playerFlashMP4': '/library/swf/jarisplayer/v2.0.15b/jarisplayer.swf',
                            'playerFlashMP3': '/library/swf/jarisplayer/v2.0.15b/jarisplayer.swf'
                        }{$play_hover});

                        var FullScreenCheck =  function(data) {
                                if(data==true){
                                    \$j('.projekktor').addClass('fullscreen');
                                    \$j('.carousel-clip, .gallery-item, .panel, .frame').css('overflow','visible');
                                    \$j('div#navigation').hide();
                                    \$j('div#header').hide();
                                    \$j('.moncler_player').css('z-index','9998');
                                } else {
                                    \$j('.projekktor').removeClass('fullscreen');
                                    \$j('.carousel-clip, .gallery-item, .panel, .frame').css('overflow','hidden');
                                    \$j('div#navigation').show();
                                    \$j('div#header').show();
                                    \$j('.moncler_player').css('z-index','4');
                                }
                        };
                        {$projVar}.addListener('fullscreen', FullScreenCheck );
                    });
                /* ]]> */
SOE;
        $bootstrap->view->inlineScript()->appendScript($projekktor, 'text/javascript');
        $html = $html5video;

        if (isset($this->_options['wrap_video_div'])) {
            if ($this->_options['wrap_video_div'] === true) {
                $html = '<div class="moncler_player moncler_player_centered scale scale-height">' . "\n" . $html5video . "\n" . '</div>';
            }
        }

        return $html;
    }

}
