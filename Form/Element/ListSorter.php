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
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_ListSorter extends Zend_Form_Element_Multi {
    const COLLECTIONS   = "Application\Entities\Collections";
    const IMAGERY       = "Application\Entities\GroupedLooks";
    const SEASONS       = "Application\Entities\SeasonPages";

    /**
     * Use formHidden view helper by default
     * @var string
     */
    public $helper = 'formHidden';

    /**
     * Initialize object; used by extending classes
     * @return void
     */
    public function init() {
//        die($this->getAttrib('entityId'));
    }

    /**
     *
     * @param type $value
     * @return string
     */
    protected function renderExtras($value) {

        $jqnc = JQueryViewHelper::getJQueryHandler();
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $this->log = $bootstrap->getResource('Log');

        $elemid = $this->getId();
        $elemval = $this->getValue();
        $this->_getMultiOptions();
        
        if( end($this->options)===false)
        {
            array_pop($this->options);
            $editable = false;
        }
        else
        {
            $editable = true;
        }
        
        $renderHtml  = "";
        $itemtype    = (get_class(current($this->options))==self::COLLECTIONS)?'collection':'look';// || $modalid == 'collection-picker'
        $modalid     = $itemtype.'-picker';
        $renderHtml .= '<div class="gallery">'."\n";
        $renderHtml .= '<div class="grid-preview-controls">'."\n";
        $renderHtml .= '<div class="control-group">'."\n";
        $renderHtml .= '<button type="button" id="'.$elemid.'-add" class="btn add-items" data-id="'.$elemval.'" data-modal-type="primary" data-toggle="modal" href="#'.$modalid.'">Add '.  ucwords($itemtype).'</button>'."\n";
        $renderHtml .= '</div>'."\n";
        $renderHtml .= '</div>'."\n";
        $renderHtml .= '<div class="well">'."\n";
        $renderHtml .= '<ul id="'.$elemid.'-element-selecable" class="thumbnails">'."\n";
        $curItems = array();
        foreach($this->options as $id => $item) {
                        
            $curItems[] = $id;
            try {
                 switch(get_class($item)) {
                    case self::COLLECTIONS:
                        $collection = $item;
                        break;
                    case self::IMAGERY:
                        if((isset($item->looks)===true)&&(isset($item->looks->first()->products)===true)){
                            $collection = $item->looks->first()->products->first()->collection;
                        }
                        break;
                    default:
                        break;
                    }
                } catch (Exception $exc) {
                    //$this->log->info($exc->getTraceAsString());
                    $sorterid = $valid;
                }
            $renderHtml .= $this->renderItem($item,$editable);
        }
        $renderHtml .= '</ul>'."\n";
        $renderHtml .= '</div>'."\n";
        $renderHtml .= '</div>'."\n";

        $jstmpl = $this->renderItem('js',$editable);

        $curItemsFlat = implode(',', $curItems);

        #SEASONS
        $scr = ($itemtype=='look')?$this->looksScript($elemid, $modalid, $collection, $curItemsFlat) : $this->collectionScript($elemid, $modalid, $collection, $curItemsFlat);
        
        $this->getView()->placeholder('modals')->append(
            $this->getView()->render('partials/manager/modal.'.$modalid.'.phtml')
        );
        $this->getView()->inlineScript()->appendScript($scr, 'text/javascript');
        $this->getView()->inlineScript()->appendScript($jstmpl, 'text/html', array('id'=>'sortableCollectionTemplate'));
        return $renderHtml;
    }
    
    
    protected function looksScript($elemid,$modalid,$collection,$curItemsFlat) {
        $itemtype = 'look';
        $jqnc = JQueryViewHelper::getJQueryHandler();
        return <<<EOT
        /* <![CDATA[ */
            \$LAB
            .script(window.CMS.config.site.uri + 'library/js/jquery/plugins/ui/v1.8.17/jquery-ui-1.8.17.js')
            .script(window.CMS.config.site.uri + 'library/js/bootstrap/v2.0.3/bootstrap.js')
            .script(window.CMS.config.site.uri + 'library/js/icanhaz/v0.10/ICanHaz.js')
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

                    {$jqnc}(document).on('click', 'div.thumbnail i.icon-edit', function(e){
                        var that = this;
                        {$jqnc}(this).siblings('div.control-group').toggleClass('hide');
                        if({$jqnc}(this).siblings('div.control-group').hasClass('hide')) {
                            {$jqnc}(that).closest('li').switchClass( 'span4', 'span1', 500 );
                        } else {
                            {$jqnc}(that).closest('li').switchClass( 'span1', 'span4', 500 );
                        }
                    });
                    {$jqnc}('#{$modalid}').on('shown', function(e){
                        console.info(e);
                        console.info('Pick {$itemtype} '+{$jqnc}(this).data('id'));
                    });
                }
            );
        /* ]]> */
EOT;
    }
    
    protected function collectionScript($elemid,$modalid,$collection,$curItemsFlat) {
        $itemtype = 'collection';
        $jqnc = JQueryViewHelper::getJQueryHandler();
        return <<<EOT
        /* <![CDATA[ */
            \$LAB
            .script(window.CMS.config.site.uri + 'library/js/jquery/plugins/ui/v1.8.17/jquery-ui-1.8.17.js')
            .script(window.CMS.config.site.uri + 'library/js/bootstrap/v2.0.3/bootstrap.js')
            .script(window.CMS.config.site.uri + 'library/js/icanhaz/v0.9/ICanHaz.js')
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

                    {$jqnc}(document).on('click', 'div.thumbnail i.icon-edit', function(e){
                        var that = this;
                        {$jqnc}(this).siblings('div.control-group').toggleClass('hide');
                        if({$jqnc}(this).siblings('div.control-group').hasClass('hide')) {
                            {$jqnc}(that).closest('li').switchClass( 'span4', 'span1', 500 );
                        } else {
                            {$jqnc}(that).closest('li').switchClass( 'span1', 'span4', 500 );
                        }
                    });

//                    {$jqnc}(document).on('click', 'a.add-items', function(e){
//                        e.preventDefault();
//                        console.info('Pick {$itemtype} '+{$jqnc}(this).data('id'));
//                    });

                    {$jqnc}('#{$modalid}').on('shown', function(e){
                        console.info(e);
                        var year = '{$collection->year}';
                        var season = '{$collection->season}';
                        var items = {current: '{$curItemsFlat}'}
                        var checked;
                        var selected_collections = {$jqnc}('input#collections-gallery, input#grids-items').attr('value');
                        
                        if (selected_collections.charAt( selected_collections.length-1 ) == ",") {
                            selected_collections = selected_collections.slice(0, -1);
                        }
                        
                        var arr_selected_collections = new Array();

                        if (selected_collections != undefined && selected_collections != '') {
                            arr_selected_collections = selected_collections.split(',');
                        }
                        
                        
                        if ({$jqnc}('#{$modalid} .modal-body ul').length == 0)
                        {
                            var ajaxuri = '/api/collections/all.json';
                            {$jqnc}.ajax({
                                url:ajaxuri,
    //                            data: items,
                                type: 'GET',
                                success: function(d){
                                    var cont = {$jqnc}('<ul/>');
                                    cont.addClass('unstyled row-fluid');
                                    for (season in d.response.data) {

                                        var fieldset = {$jqnc}('<fieldset id="' + season + '" class="span4"><b>SEASON ' + season +'</b>');

                                        for (index in d.response.data[season]) {

                                            checked = '';

                                            if ({$jqnc}.inArray(d.response.data[season][index].id.toString(), arr_selected_collections) != -1) {
                                                checked = ' checked="yes" ';
                                            }

                                            fieldset.append('<li><input type="checkbox" '+checked+' imgsrc="'+d.response.data[season][index].imgsrc+'" id="collection-'+d.response.data[season][index].id+'" value="'+d.response.data[season][index].id+'"/> <label for="collection-'+d.response.data[season][index].id+'" id="name" class="pick-collection">'+d.response.data[season][index].title+'</label></li>');
                                        }

                                        cont.append(fieldset);
                                    }



                                    {$jqnc}('#{$modalid} #replace-me').replaceWith(cont);

                                    //save modal content handler
                                    {$jqnc}('#{$modalid} .modal-footer button.modal-confirm').click(function(){

                                        var elem_id;

                                        {$jqnc}('#{$modalid} .modal-body fieldset li input[type=checkbox]:not(:checked)').each(function(index) {

                                            elem_id = {$jqnc}(this).attr('value');                                        
                                            {$jqnc}('#{$elemid}-element-selecable li[data-id='+elem_id+']').remove();

                                        });

                                        var collections_ids = '';
                                        {$jqnc}('#{$modalid} .modal-body fieldset li input[type=checkbox]:checked').each(function(index) {

                                            elem_id = {$jqnc}(this).attr('value');

                                            if ({$jqnc}('#{$elemid}-element-selecable li[data-id='+elem_id+']').length == 0)
                                            {
                                                var data = {
                                                    'id': elem_id,
                                                    'link' : {$jqnc}(this).attr('imgsrc'),
                                                    'title': {$jqnc}(this).next('label#name').text(),
                                                    'edituri': '/admin/manage/collection/update/' + elem_id
                                                }


                                                var html = ich.sortableCollectionTemplate(data);

                                                {$jqnc}('#{$elemid}-element-selecable').append(html);

                                                var targid = '';
                                                {$jqnc}('#{$elemid}-element-selecable li').each(function(){
                                                targid += ''+{$jqnc}(this).attr('data-id')+',';
                                                });

                                                {$jqnc}('#{$elemid}').val(targid);

                                                //{$jqnc}(this).remove();
                                            }

                                            collections_ids += $(this).attr('value') + ',';
                                        });

                                        collections_ids = collections_ids.slice(0, -1);
                                        {$jqnc}('input#collections-gallery').attr('value', collections_ids);
                                        {$jqnc}('input#grids-items').attr('value', collections_ids);


                                    });

                                }
                            });
                        }
                        else {
                            //refresh modal checkboxes
                            {$jqnc}('#{$modalid} .modal-body ul li input').attr('checked',false);
                            
                            for (index in arr_selected_collections) {
                                {$jqnc}('#{$modalid} .modal-body ul li input[value='+arr_selected_collections[index]+']').attr('checked',true);
                            }
                                
                        }
                        console.log('shown {$modalid}');
                        console.log(year+' - '+season+' = '+ajaxuri);
                    });
                    var targid = '';
                    {$jqnc}('#{$elemid}-element-selecable li').each(function(){
                        targid += ''+{$jqnc}(this).attr('data-id')+',';
                    });
                    {$jqnc}('#{$elemid}').val(targid);
                }
            );
        /* ]]> */
EOT;
    }

    protected function renderItem($itemd,$editable =true) {
        #if requesting js template.
        if(!is_string($itemd)) {
            switch(get_class($itemd)) {
                case self::COLLECTIONS:
                    $edituri = "/admin/manage/collection/update/{$itemd->id}";
                    $removeuri = "/admin/manage/seasons/remove/{$itemd->seasonpage->id}/0/collection/{$itemd->id}";
                    $itemd->gallery->featured->setVariation('homepagesmall');
                    $imgsrc = $itemd->gallery->featured->link;
                    $image = $itemd->gallery->featured;
                    $hoverstate = $itemd->getMetadata('hoverstate');
                    $hoverstaten = ($hoverstate->content=='hover')?" active":"";
                    $hoverstatep = ($hoverstate->content=='permanent')?" active":"";
                    $alignment = $itemd->getMetadata('alignment');
                    $alignt = ($alignment->content=='top')?" active":"";
                    $alignc = ($alignment->content=='center')?" active":"";
                    $size = 'homepagesmall';
                    break;
                case self::IMAGERY:
                    $edituri = "/admin/manage/imagery/update/{$itemd->id}";
                    $removeuri = "/admin/manage/collection/remove/{$itemd->collection->id}/0/imagery/{$itemd->id}";
                    if(isset($itemd->gallery->featured)) {
                        $itemd->gallery->featured->setVariation('newsthumb');
                    }
                    $imgsrc = $itemd->featured->link;
                    $image = $itemd->featured;
                    $size = 'newsthumb';
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