<?php
//namespace Wednesday\Form\Element;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    Application\Entities\Resources,
    Wednesday\Renderers\ResourceHtml,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Controller_Front as Front;

/**
 * Description of ResourcePicker
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_PageSorter extends Zend_Form_Element_Multi {
    const IMAGERY       = "Application\Entities\GroupedLooks";
    const SEASONS       = "Application\Entities\SeasonPages";
    const PAGES         = "Application\Entities\Pages";
    const RESOURCE      = "Application\Entities\MediaResources";

    /**
     * Use formHidden view helper by default
     * @var string
     */
    public $helper = 'formHidden';
    
    private $em;

    /**
     * Initialize object; used by extending classes
     * @return void
     */
    public function init() {
//        die($this->getAttrib('entityId'));
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $this->em = $bootstrap->getContainer()->get('entity.manager');
        $this->log = $bootstrap->getResource('Log');
    }

    /**
     *
     * @param type $value
     * @return string
     */
    protected function renderExtras($value) {
        $jqnc = JQueryViewHelper::getJQueryHandler();
        

        $elemid = $this->getValue();
        $this->_getMultiOptions();
        
        $editable = false;
        
        $renderHtml  = "";
        $itemtype    = 'page';//(get_class(current($this->options))==self::PAGES)?'collection':'look';// || $modalid == 'collection-picker'
        $modalid     = 'pages-modal';//$itemtype.'-picker';
        $renderHtml .= '<div class="page-list-editor">'."\n";
        $renderHtml .= '<ul id="'.$elemid.'-element-selecable" class="thumbnails">'."\n";
        $curItems = array();
        foreach($this->options as $id => $item) {
            $curItems[] = $id;
            try {
                 switch(get_class($item)) {
                    case self::PAGES:
                        $page = $item;
                        break;
                    default:
                        break;
                    }
                } catch (Exception $exc) {
                    $sorterid = $valid;
                }
            $renderHtml .= $this->renderItem($item,$editable);
        }
        $this->log->info('==============================');
        $this->log->info($curItems);
        $renderHtml .= '</ul>'."\n";
        $renderHtml .= '</div>'."\n";

        $jstmpl = $this->renderItem('js',$editable);
        
        $curItemsFlat = implode(',', $curItems);

        #SEASONS
        $scr = $this->collectionScript($elemid, $modalid, $page, $curItemsFlat);
//        die($modalid);
//        $this->getView()->placeholder('modals')->append(
//            $this->getView()->render('partials/manager/modal.'.$modalid.'.phtml')
//        );
        
        $this->getView()->inlineScript()->appendScript($scr, 'text/javascript');
        $this->getView()->inlineScript()->appendScript($jstmpl, 'text/html', array('id'=>'sortableCollectionTemplate'));
        return $renderHtml;
    }
    
    
    protected function collectionScript($elemid,$modalid,$collection,$curItemsFlat) {
        $itemtype = 'collection';
        $jqnc = JQueryViewHelper::getJQueryHandler();
        return <<<EOT
        /* <![CDATA[ */
//            Array.prototype. remove = function(from, to) {
//                var rest = this.slice((to || from) + 1 || this.length);
//                this.length = from < 0 ? this.length + from : from;
//                return this.push.apply(this, rest);
//            };        
            \$LAB
            .script(window.CMS.config.site.uri + 'library/js/jquery/plugins/ui/v1.8.17/jquery-ui-1.8.17.js')
            .script(window.CMS.config.site.uri + 'library/js/bootstrap/v2.0.3/bootstrap.js')
            .script(window.CMS.config.site.uri + 'library/js/icanhaz/v0.9/ICanHaz.js')
            .script(window.CMS.config.site.uri + 'library/js/jquery/plugins/jstree/v1.0rc3/jquery.jstree.js')
            .wait
            (
                function()
                {
                    ich.grabTemplates();
                    {$jqnc}('#{$elemid}-element-selecable').sortable();
                    {$jqnc}('#{$elemid}-element-selecable').bind( "sortupdate", function(event, ui) {
                        var targid = '';
                        {$jqnc}('#{$elemid}-element-selecable li').each(function(){
                            targid += ''+{$jqnc}(this).attr('data-id')+',';
                        });
                        {$jqnc}('#{$elemid}').val(targid);
                    });



                    {$jqnc}('#{$modalid}').on('shown', function(e){
                        console.info(e);
////                        {$jqnc}(".pagestree").jstree("refresh");
                        {$jqnc}(".pagestree").trigger("loaded.jstree");


                    });
                    
                    var targid = '';
                    {$jqnc}('#{$elemid}-element-selecable li').each(function(){
                        targid += ''+{$jqnc}(this).attr('data-id')+',';
                    });
                    {$jqnc}('#{$elemid}').val(targid);
                
            
                    ////////////////////////////////////////////////////////////
                    //                  The jsTree Code                       //
                    ////////////////////////////////////////////////////////////
                    {$jqnc}.jstree._themes = window.CMS.config.theme.uri + 'css/jstree/';
                    {$jqnc}(".pagestree").jstree({
                        "themes" : { "theme" : "wednesday" },
                        "plugins" : [ "themes", "html_data", "checkbox", "ui", "dnd" ]
                    }).bind
                    (
                        'loaded.jstree',
                        function(event, data)
                        {
                            {$jqnc}.jstree._reference('.pagestree').uncheck_all();
                            var selected_pages = {$jqnc}("#contents-{$elemid}").val();
                            selected_pages = selected_pages.split(',');
                            for (selected_page in selected_pages) {
                                var node = '#node-'+selected_pages[selected_page];
                                {$jqnc}.jstree._reference('.pagestree').check_node(node);
                            }
                        }
                    );
                                
                                
                    {$jqnc}(document).on
                    (
                        'click', 'div.thumbnail .icon-remove',
                        function(event)
                        {
                            console.log('removing from the list');
                            var selected_pages = {$jqnc}("#contents-{$elemid}").val();
                            var tempArray = new Array();
                            selected_pages = selected_pages.split(',');
                            for (selected_page in selected_pages) {
                                if(selected_pages[selected_page]!= {$jqnc}(event.target).closest('li').data('id'))
                                {
                                    tempArray.push(selected_pages[selected_page]);
                                }
                            }
                            {$jqnc}("#contents-{$elemid}").val(tempArray.join(','));
                            
                        }
                    );
                           

                    {$jqnc}('#{$modalid}-cancel').bind('click',function(e){
                        e.preventDefault();
                        {$jqnc}('#{$modalid}').modal('hide');
                    });
                    {$jqnc}('#{$modalid}-save').bind('click',function(e){
                        e.preventDefault();
                        console.log('save data.');
                        var items = '', selected = {$jqnc}(".pagestree").jstree('get_checked',false,true);
                        var saveIds = new Array(); 
                        selected.each(function(inst) {
                            var txid = {$jqnc}(this).attr('id');
                            var theid = txid.replace('node-','');
                            saveIds.push(theid);
                            {$jqnc}('ul.thumbnails').empty();
                            for (saveId in saveIds) {
                                var data = {
                                                'id': saveIds[saveId],
                                                'link' : {$jqnc}('#node-'+saveIds[saveId]).data('imagescr'),
                                                'title': {$jqnc}('a','#node-'+saveIds[saveId]).val()
                                            };
                                var html = ich.sortableCollectionTemplate(data);
                                {$jqnc}('ul.thumbnails').append(html);
                            }
                            
                        });
                        {$jqnc}("#contents-{$elemid}").val(saveIds.join(','));
                        {$jqnc}('#{$modalid}').modal('hide');
                    });
                }
            );  
        /* ]]> */
EOT;
    }

    protected function renderItem($itemd,$editable =true) {
        #if requesting js template.
        if(!is_string($itemd)) {
            switch(get_class($itemd)) {
                case self::PAGES:
                    $edituri = "/admin/manage/collection/update/{$itemd->id}";
                    $removeuri = "/admin/manage/seasons/remove/{$itemd->seasonpage->id}/0/collection/{$itemd->id}";
                    $itemd->getTvarData('previewimage')->value;
//                    die($itemd->getTvarData('previewimage')->value);
                    $resouce = $this->em->getRepository(self::RESOURCE)->findOneById($itemd->getTvarData('previewimage')->value);
                    $resouce->setVariation('homepagesmall');
                    $imgsrc = $resouce->link;
                    $image = $resouce;
                    $hoverstate = $itemd->getMetadata('hoverstate');
                    $hoverstaten = ($hoverstate->content=='hover')?" active":"";
                    $hoverstatep = ($hoverstate->content=='permanent')?" active":"";
                    $alignment = $itemd->getMetadata('alignment');
                    $alignt = ($alignment->content=='top')?" active":"";
                    $alignc = ($alignment->content=='center')?" active":"";
                    $size = 'homepagesmall';
                    break;
                default:
                    break;
            }
            $item = array(
                'id' => $itemd->id,
                'imgsrc' => $imgsrc,
                'title' => $itemd->title,
                'edituri' => $edituri,
                'hoverstaten' => ' active',
                'hoverstatep' => '',
                'alignt' => ' active',
                'alignc' => '',
                'image'=> $image,
                'size' => $size
            );
        } else /*if($item == 'js')*/ {
            $item = array(
                'id' => '{{id}}',
                'imgsrc' => '{{link}}',
                'title' => '{{title}}',
                'edituri' => '{{edituri}}',
                'hoverstaten' => '{{hoverstaten}}',
                'hoverstatep' => '{{hoverstatep}}',
                'alignt' => '{{alignt}}',
                'alignc' => '{{alignc}}',
                'size' => 'newsthumb'
            );
        }

        $item['editable']=$editable;
        
        $jstmpl = $this->getView()->partial('partials/items/sortListItems.phtml', $item);
        return $jstmpl;
    }

    /**
     * Render form element
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if ($this->_isPartialRendering) {
            return '';
        }

        if (null !== $view) {
            $this->setView($view);
        }

        $content = '';
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
//            if(get_class($decorator) == 'Zend_Form_Decorator_HtmlTag') {
//            if(get_class($decorator) == 'Zend_Form_Decorator_ViewHelper') {
            if(get_class($decorator) == 'EasyBib_Form_Decorator_BootstrapTag') {
                $content = $content.$this->renderExtras($this->getValue());
            }
            $content = $decorator->render($content);
        }
        return $content;
    }
}