<?php
namespace Wednesday\Renderers;

use \Zend_Controller_Front as Front,
    \Zend_Registry as Registry,
    \Zend_View_Helper_Abstract as ViewHelperAbstract,
    \Application\Entities\Grids as GridEnt,
    \Wednesday\Resource\Containers as Containers,
    \Wednesday\Resource\Service as ResourceService;

/**
 * Description of Renderer
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
class GridRenderer implements Renderer  {

    private $_grid;
    private $_mode;
    private $_options;

    protected static $types = array('show','look','description');
    protected $_lookcount = 0;
    protected $_showcount = 0;
    protected $_imagecount = 0;
    protected $_lookimages;
    protected $_showimages;
    protected $_collections;
    protected $registry;
    protected $trans;

    /**
     *
     * @param GridEnt $resource
     * @param array $options
     */
    public function __construct($resource, $options = false) {
        $this->resService = ResourceService::getInstance();
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $this->log = $bootstrap->getResource('Log');
        $em = $bootstrap->getContainer()->get('entity.manager');
        $this->registry = Registry::getInstance();
        $this->trans = $bootstrap->getContainer('translate');
        $this->trans->translate->setLocale($this->registry->locale->__toString());

        $this->config = $bootstrap->getContainer()->get('config');
        $this->_grid = (is_numeric($resource))?$em->getRepository(GridEnt)->findOneById($resource):$resource;
        $this->_options = $options;
        $this->_mode = $this->_options['type'];

        switch($this->_mode) {
            case 'admin':
                $this->_collections = null;
                $this->_lookimages = array();
                $this->_showimages = array();
                break;
            case 'season':
                $this->_collections = $this->_options['collection'];
                $this->_lookimages = array();
                $this->_showimages = array();
                break;
            case 'collection':
                $this->_collections = $this->_options['collection'];
                $this->_lookimages = $this->_collections->imagery;
                $this->_showimages = $this->_collections->gallery->items;
                if(isset($this->_collections)) {
                    $mdbgc = $this->_collections->getMetadata('bg-colour');
                }
                $bgcolor = <<<EOCOL
                        body #content div.config-overlay-shade-0 img { background-color: #{$mdbgc->content}; }
                        body #content div.config-overlay-shade-0 .copy-block { background-color: #000000; }
EOCOL;
                $bootstrap->view->headStyle()->appendStyle($bgcolor);
                break;
        }

        $this->log = $bootstrap->getResource('Log');
//        #TODO Hookin CDNmanager ()
    }

    /**
     *
     * @return string
     */
    public function __toString() {
        return $this->render();
    }

    /**
     *
     * @return string
     */
    public function render() {
//        $typearray = self::$types;//array('show','look','description');
//        $mode = (isset($this->_options['type'])==true)?$this->_options['type']:'admin';
        $renderHtml = "";
        $renderHtml .= $this->renderGrid($this->_grid->arrangement);

        return $renderHtml;
    }

    protected function renderGrid($arrangement) {
        $this->log->debug("renderGrid");
        $renderHtml = "";
        foreach($arrangement as $row) {
            $renderHtml .= $this->renderRow($row);
        }
        return $renderHtml;
    }

    protected function renderRow($row,$renderContainer = true) {
        $renderHtml = "";
        $renderHtml .= ($renderContainer==true)?'<div class="grid-row clearfix">'."\n":'';
        foreach ($row as $rowtype => $rowitem) {
            if(is_numeric($rowtype)) {
                if(count($rowitem)>=1) {
                    $renderHtml .= $this->renderRow($rowitem,false);
                }
            } else {
                if($this->_mode=='collection') {
                    $this->log->debug("Grid:Count Rows=".((1+$this->_lookcount)/4)." :: ".ceil(((1+$this->_lookimages->count())/4)));
                    if(((1+$this->_lookcount)/4)==ceil(((1+$this->_lookimages->count())/4))) {
                        $this->log->debug("Grid:Finished");
                        break 1;
                    }
                }
                switch($rowtype) {
                    case 'grid-100':
                    case 'grid-50':
                    case 'grid-33':
                    case 'grid-25':
                        $renderHtml .= '<div class="grid '.$rowtype.'">'."\n";
                        $renderHtml .= $this->renderCell($rowitem,$rowtype);
                        $renderHtml .= '</div>'."\n";
                        break;
                    case 'grid-row':
                    default:
                        $renderHtml .= $this->renderCell($rowitem,$rowtype);
                        break;
                }
            }
        }
        $renderHtml .= ($renderContainer==true)?'</div>'."\n":'';
        return $renderHtml;
    }

    protected function renderCell($cell,$class) {
        $this->log->debug("renderCell::".$class);
        $renderHtml = "";
        if(is_array($cell)) {
            $rowrender = ($class=='grid-row')?true:false;
            $renderHtml .= $this->renderRow($cell,$rowrender);
        } else {
            $needle = 'size-';
            $length = strlen($needle);
            if(substr($cell, 0, $length) === $needle) {
                $cellshape = $cell;
            } else if(substr($class, 0, $length) === $needle) {
                $cellshape = str_replace('sizex','size-', str_replace('-','x', $class));
            } else if($class == "grid-50") {
                $cellshape = 'size-640x428';
            } else {
                $cellshape = 'size-640x856';
            }
            if(in_array($cell, self::$types)) {
                $count = ($cell=='look')?$this->_lookcount:$this->_showcount;
                $count = ($cell=='description')?'':$count;
                $cellclass = $cell.''.$count;
//                $renderHtml .= '<div class="spotlight config-flicker config-overlay-shade-0 '.$cell.' '.$cellclass.'">'."\n";
                $renderHtml .= $this->getRenderedResource($cell,$cellshape,$this->_mode,$cellclass);
//                $renderHtml .= '</div>'."\n";
            } else {
//                $renderHtml .= '<div class="spotlight config-flicker config-overlay-shade-0 '.$cell.'">'."\n";
                $count = $this->_imagecount;
                $cellclass = 'look'.$count;
                $renderHtml .= $this->getRenderedResource($cell,$cellshape,$this->_mode,$cellclass);
//                $renderHtml .= '</div>'."\n";
//                $renderHtml .= '<pre class="screen - offset">'.print_r($cell, true).'</pre>'."\n";
            }
        }
        return $renderHtml;
    }

    /**
     *
     * @param string $type
     * @param string $size
     * @param string $typetext
     * @return string
     */
    protected function getRenderedResource($type,$size=false,$typetext='admin',$typeclass='') {
        $size = ($size==false)?"size-640x428":$size;
        $renderHtml = "";
        $this->log->debug("getRenderedResource::".$type."-".$size."-".$typetext."-".$this->_mode);
        $fade = "";
        switch ($type) {
            default:
                switch($this->_mode) {
                    case 'season':
                        $curCollection = $this->_collections[$this->_imagecount];
                        $hovermeta = $curCollection->getMetadata('hoverstate');
                        $hoverstate = @$hovermeta->content;
                        $fade = ($hoverstate=='permanent')?'config-partial-fade':'';
                        $logopath = $curCollection->logo->link;
                        list($logowidth,$logoheight, $logotype, $logoattr) = @getimagesize(WEB_PATH.$logopath);
                        $logooffset = $logowidth / 2;
                        $size = str_replace('-','x', strstr($size, 'size-'));
                        $sizes = $this->getSizeString($size);
                        $curCollection->gallery->featured->setVariation($sizes);
                        $itemimg = new ResourceUri($curCollection->gallery->featured);
                        $itemlink = $curCollection->getUri();
                        $altitle = htmlentities($curCollection->title);
                        $title = $this->trans->translate->_('View the collection');
                        $hpslogo = <<<EOX
<span style="background-image:url({$logopath}); _background:none; _filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='{$logopath}', sizingMethod='image'); height:{$logoheight}px; width:{$logowidth}px;" class="distinct replaced logo" role="banner"></span>
EOX;
                        $hplogo = ($hoverstate=='permanent')?$hpslogo:'<em>'.$hpslogo.'</em>';
                        break;
                    default:
                        $itemimg = '/themes/admin/img/grid/'.str_replace('sizex','display-', str_replace('-','x', $size)).'.gif';
                        $itemlink = "#";
                        $coltitle = $typetext;
                        $hplogo = "";
                        $altitle = "";
                        $title = $type;
                        break;
                }

                $renderHtml = <<<EOT
                <img src="{$itemimg}" class="size-320-428" alt="{$altitle}">
                <!--OVERLAY LINK START-->
                <a href="{$itemlink}" class="overlay-link">
                    <div class="faux-table">
                        <div class="faux-table-cell">
                            <div class="overlay-link-content">
                                {$hplogo}
                                <p class="action navigation" role="navigation"><em>{$title}</em></p>
                            </div>
                        </div>
                    </div>
                </a>
                <!--OVERLAY LINK END-->
EOT;
                $this->_imagecount++;
                break;
            case 'blank':
                $itemimg = '/themes/admin/img/grid/'.str_replace('sizex','grid-', str_replace('-','x', $size)).'.gif';
                $renderHtml = '<img src="'.$itemimg.'" class="size-320-428 spacer" alt="Blank">';
                break;
            case 'show':
                switch($this->_mode) {
                    case 'admin':
                        $itemimg = '/themes/admin/img/grid/'.str_replace('sizex','grid-', str_replace('-','x', $size)).'.gif';
                        $itemlink = "#";
                        $sizes = "";//
                        $coltitle = $typetext;
                        $altitle = strip_tags($coltitle)." ".strip_tags($title);
                        $title = $type." <br /><small>Position ".round(1+$this->_showcount)."</small>";
                        $renderHtml = <<<EOT
                <img src="{$itemimg}" class="{$size}" alt="{$altitle}">
                <!--OVERLAY LINK START-->
                <a href="{$itemlink}" class="overlay-link">
                    <div class="faux-table">
                        <div class="faux-table-cell">
                            <div class="overlay-link-content">
                                <h4><em>{$title}</em></h4>
                                <p class="action navigation" role="navigation"><em>{$sizes}</em></p>
                            </div>
                        </div>
                    </div>
                </a>
                <!--OVERLAY LINK END-->
EOT;
                        break;
                    case 'collection':
                        //$this->_showcount
                        $resource = $this->_showimages[$this->_showcount]->resource;
                        switch($resource->type) {
                            case 'video':
                                $itemrenderer = new ResourceVideo($resource,array(
                                    'videoPlayer'=>'projekktor',
                                    'use_projekktor'=>true,
                                    'height'=>'false',
                                    'width'=>'false',
                                    'scaling'=>'fill',
                                    'controls'=>true,
                                    'wrap_video_div'=>false,
                                    'class'=>'projekktor moncler_player'
                                ));
                                $itemimg = '/themes/admin/img/grid/'.str_replace('sizex','grid-', str_replace('-','x', $size)).'.gif';
                                $renderHtml = '<img src="'.$itemimg.'" class="size-320-428" />'."\n".$itemrenderer->render();
                                break;
                            case 'image':
                                $itemrenderer = new ResourceHtml($resource);
                                $renderHtml = $itemrenderer->render();
                                break;
                            default:
                                break;
                        }

                        break;
                    case 'season':
                    default:
                        $itemimg = '/themes/admin/img/grid/'.str_replace('sizex','display-', str_replace('-','x', $size)).'.gif';
                        $itemlink = "#";
                        $renderHtml = <<<EOT
                        <img src="{$itemimg}" class="size-320-428" alt="Moncler Gamme Bleu" />
EOT;
                        break;
                }
                $this->_showcount++;
                break;
            case 'look':
                $admin = "";
                switch($this->_mode) {
                    case 'admin':
                        $itemimg = '/themes/admin/img/grid/'.str_replace('sizex','grid-', str_replace('-','x', $size)).'.gif';
                        $itemlink = "#";
                        $sizes = $coltitle = $typetext;
                        $admin = "";//<p class='action navigation' role='navigation'><em>{$sizes}</em></p>";
                        //$title = $type;
                        $title = $type." <br /><small>Position ".round(1+$this->_lookcount)."</small>";
                        break;
                    case 'season':
                        $itemimg = '/themes/admin/img/grid/'.str_replace('sizex','display-', str_replace('-','x', $size)).'.gif';
                        $itemlink = "/collections/moncler-gamme-bleu/";
                        $hplogo = <<<EOX
<span style="background-image:url(/assets/content/collections/logos/moncler-gamme-blue-small.png); _background:none; _filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='/assets//img/content/collections/logos/moncler-gamme-blue-small.png', sizingMethod='image'); height:99px; width:176px;" class="distinct replaced logo" role="banner"></span>
EOX;
                        break;
                    case 'collection':
                        $resource = @$this->_lookimages[$this->_lookcount]->featured;
                        if(isset($resource)===true) {
                            $sizes = $this->getSizeString($size);
                            $resource->setVariation($sizes);
                            $itemimg = new ResourceUri($resource);
                            $title = $this->_lookimages[$this->_lookcount]->title;
                            $coltitle = $this->_collections->title;
                            $itemlink = $this->_collections->getUri()."/gallery/?articleId=".$this->_lookimages[$this->_lookcount]->slug;
                        }
                        break;
                }
                if(isset($resource)===false) {
                    $itemimg = '/themes/admin/img/grid/'.str_replace('sizex','grid-', str_replace('-','x', $size)).'.gif';
                    $renderHtml = '<img src="'.$itemimg.'" class="size-320-428 spacer" alt="Blank">';
                } else {
                    $altitle = strip_tags($coltitle)." ".strip_tags($title);
                    $renderHtml = <<<EOT
                <img src="{$itemimg}" class="{$size}" alt="{$coltitle} {$title}">
                <!--OVERLAY LINK START-->
                <a href="{$itemlink}" class="overlay-link">
                    <div class="faux-table">
                        <div class="faux-table-cell">
                            <div class="overlay-link-content">
                                <h4><em>{$title}</em></h4>
                                {$admin}
                            </div>
                        </div>
                    </div>
                </a>
                <!--OVERLAY LINK END-->
EOT;
                }
                $this->_lookcount++;
                break;
            case 'description':
                switch($this->_mode) {
                    case 'admin':
                    case 'season':
                        $itemimg = '/themes/admin/img/grid/'.str_replace('sizex','grid-', str_replace('-','x', $size)).'.gif';
                        $itemlink = "#";
                        $coltitle = "";//$typetext;
                        $title = $type;
                        $renderHtml = <<<EOT
                <img src="{$itemimg}" class="{$size}" alt="{$altitle}">
                <!--OVERLAY LINK START-->
                <a href="{$itemlink}" class="overlay-link">
                    <div class="faux-table">
                        <div class="faux-table-cell">
                            <div class="overlay-link-content">
                                <h4><em>{$title}</em></h4>
                                <p class="action navigation" role="navigation"><em>{$coltitle}</em></p>
                            </div>
                        </div>
                    </div>
                </a>
                <!--OVERLAY LINK END-->
EOT;
                        break;
                    case 'collection':
                        $img = '/themes/admin/img/grid/'.str_replace('sizex','display-', str_replace('-','x', $size)).'.gif';
                        $title = $this->trans->translate->_($this->_collections->title);
                        $description = utf8_encode($this->_collections->description)." <p>".$this->_collections->renderShopLink($this->config['settings']['application']['ecommerceUrl'],true)."</p>";
                        $renderHtml = <<<EOT
                        <div class="spotlight">
                            <img alt="" class="size-320-428 spacer" src="/themes/moncler/img/content/flexible/320-428_blank.gif" />

                            <!--COPY BLOCK START-->
                            <div class="copy-block">
                                <div class="copy-block-inner scroll-pane">
                                    <div class="copy-block-padding">
                                        <h2><span class="screen-offset">About</span> {$title}</h2>
        {$description}
                                    </div>
                                </div>
                            </div>
                            <!--COPY BLOCK START-->
                        </div>
EOT;
                    break;
                }
                break;
            case 'admin':
                $renderHtml .= '<img src="/themes/admin/img/grid/'.str_replace('sizex','grid-', str_replace('-','x', $size)).'.gif" class="grid-item-filler"/>'."\n";
                $renderHtml .= "<p class='fill-center'>{$typetext}</p>";
                break;
        }

        $returnHtml = '<div class="spotlight config-flicker config-overlay-shade-0 '.$fade.' '.$type.' '.$typeclass.'">'."\n";
        $returnHtml .= $renderHtml."\n";
        $returnHtml .= '</div>'."\n";
        return $returnHtml;
    }

    /**
     *
     * @param string $size
     * @return string
     */
    protected function getSizeString($size) {
        /*
         * 'lookportraitlarge'
         * 'looklandscapelarge'
         * 'looklandscapecrop'
         * 'homepagesmall'
         * 'homepagemedium'
         * 'homepagelarge'
         * 'newsthumb'
         */
        switch ($size) {
            case 'size-320x214':
            case 'size-640x428':
                $retsize = 'looklandscapecrop';
                break;
            case 'size-320x428':
            case 'size-640x856':
                $retsize = 'lookportraitcrop';
                break;
            case 'size-960x1284':
                $retsize = 'lookportraitlarge';
                break;
            case 'size-960x642':
                $retsize = 'looklandscapelarge';
                break;
            default:
                $retsize = 'default';
                break;
        }
        $this->log->debug($size." - ".$retsize);
        return $retsize;
    }

}