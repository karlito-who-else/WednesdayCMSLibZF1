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
class Wednesday_Form_Element_CollectionSorter extends Zend_Form_Element_Multi {
    const COLLECTIONS   = "Application\Entities\Collections";
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

        $elemid = $this->getId();
        $modalid = 'grouped-collection-picker';
        $renderHtml = '<div class="controls">'."\n";
        $renderHtml .= '<div class="control-group">';
        $renderHtml .= '<a id="'.$elemid.'-add" class="btn add-items" data-toggle="modal" href="#'.$modalid.'" data-backdrop="static">Add Collection</a>';
        $renderHtml .= '</div>';

        $this->_getMultiOptions();
        $curItems = array();
        $renderHtml .= '<ul id="'.$elemid.'-element-selecable" class="span6" >';
        foreach($this->options as $id => $name) {
            $curItems[] = $id;
            $renderHtml .= '<li class="collection-select ui-state-default" data-id="'.$id.'" ><p><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>'.$this->renderCollection($id).'</p></li>';
        }
        $renderHtml .= '</ul>';
        $renderHtml .= '</div>';
        #TODO use id to grab selected Season Entity.
        $collection = $em->getRepository(self::COLLECTIONS)->find($id);
        $curItemsFlat = implode(',', $curItems);
        #SEASONS
        $scr = <<<SCR
        /* <![CDATA[ */
            \$LAB
            .script(window.CMS.config.site.uri + 'library/js/jquery/plugins/ui/v1.8.16/jquery-ui-1.8.16.js')
            .script(window.CMS.config.site.uri + 'library/js/bootstrap/v2.0.3/bootstrap.js')
            //.script(window.CMS.config.site.uri + 'library/js/bootstrap/v1.4.0/bootstrap-modal.js')
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
                    {$jqnc}('#{$modalid}').modal({backdrop:'static'});
                    {$jqnc}(document).on('click','#{$modalid} a.modal-exit',function(e){
                        e.preventDefault();
                        {$jqnc}('#{$modalid}').modal('hide');
                    });
                    {$jqnc}(document).on('click','#{$modalid} a.modal-confirm',function(e){
                        e.preventDefault();
                        {$jqnc}('#{$modalid}').modal('hide');
                    });

                    {$jqnc}(document).on('click','li.collection-select a.edit',function(e){
                        e.preventDefault();
                    });
                    {$jqnc}(document).on('click','li.collection-select a.remove',function(e){
                        e.preventDefault();
                        var that = this;
                        {$jqnc}.ajax({
                            url:{$jqnc}(this).attr('href'),
                            success: function(d){
                                console.log(d);
                                {$jqnc}(that).closest('li').remove();
                            }
                        });
                    });
                    {$jqnc}(document).on('click', 'a.pick-collection', function(e){
                        e.preventDefault();
                        console.log('Pick Collection '+{$jqnc}(this).data('id'));
                        var data = {
                            'season_id': {$collection->seasonpage->id},
                            'id': {$jqnc}(this).data('id'),
                            'link' : {$jqnc}('img',this).attr('src'),
                            'title': {$jqnc}(this).text()
                        }
                        var html = ich.sortableCollectionTemplate(data);
                        {$jqnc}('#{$elemid}-element-selecable').append(html).sortable('destroy').sortable();
                        var targid = '';
                        {$jqnc}('#{$elemid}-element-selecable li').each(function(){
                            targid += ''+{$jqnc}(this).attr('data-id')+',';
                        });
                        {$jqnc}('#{$elemid}').val(targid);
                        {$jqnc}(this).remove();
                    });

                    {$jqnc}(document).on('shown', '#{$modalid}', function(e){
                        var year = '{$collection->year}';
                        var season = '{$collection->season}';
                        var items = {current: '{$curItemsFlat}'}
                        var ajaxuri = '/admin/manage/collections/ajax/0/0/year/'+year+'/season/'+season;
                        {$jqnc}.ajax({
                            url:ajaxuri,
                            data: items,
                            type: 'POST',
                            success: function(d){
                                var cont = {$jqnc}('<ul/>');
                                cont.addClass('unstyled');
                                for (row in d) {
                                    console.log(row+' '+d[row].title);
                                    cont.append('<li><a data-id="'+row+'" href="#" class="pick-collection"><img src="'+d[row].image+'" height="30" alt="'+d[row].title+'" /> '+d[row].title+'</a></li>');
                                }
                                {$jqnc}('#{$modalid} #replace-me').replaceWith(cont);
                            }
                        });
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
SCR;
    $jstmpl = <<<TMPL
    <li data-id="{{id}}" class="collection-select ui-state-default">
        <p>
        <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
        <span>
            <img class="thumbnail" height="32" src="{{link}}" alt="{{title}}" title="{{title}}" />
            <br />{{title}}
        </span>
        <span class="stick-right">
            <label class="optional" for="hoverstate{{id}}">Permanent</label>
            <input type="hidden" value="0" name="hoverstate[{{id}}]" />
            <input type="checkbox" value="1" id="hoverstate{{id}}" name="hoverstate[{{id}}]" />
            <small>
                <a href="/admin/manage/collections/update/{{id}}" class="edit">Edit</a>
                <br />
                <a href="/admin/manage/seasons/remove/{{season_id}}/0/collection/{{id}}" class="remove">Remove</a>
            </small>
        </span>
        </p>
    </li>
TMPL;
        $this->getView()->placeholder('modals')->append(
            $this->getView()->render('partials/manager/modal.'.$modalid.'.phtml')
        );
        $this->getView()->inlineScript()->appendScript($jstmpl, 'text/html', array('id'=>'sortableCollectionTemplate'));
        $this->getView()->inlineScript()->appendScript($scr, 'text/javascript');
        return $renderHtml;
    }

    protected function renderCollection($id) {
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $res = $em->getRepository(self::COLLECTIONS)->findOneById($id);
        $editLink = "<a class='edit' href='/admin/manage/collections/update/".$res->id."'>Edit</a>";
        $removeLink = "<a class='remove' href='/admin/manage/seasons/remove/".$res->seasonpage->id."/0/collection/".$res->id."'>Remove</a>";
        $hoverState = $res->getMetadata('hoverstate');
        $selected = ($hoverState->content == 'permanent')?'checked="checked" ':'';
        $gridPerm = <<<EOT
        <label class="optional" for="hoverstate-{$res->id}">Permanent</label>
        <input type="hidden" value="0" name="hoverstate[{$res->id}]" />
        <input type="checkbox" value="1" id="hoverstate-{$res->id}" name="hoverstate[{$res->id}]" {$selected}/>
EOT;
        $imagesource = "<img class='thumbnail' height='32' src='".$res->gallery->featured->link."' />";
        return "<span>{$imagesource}<br />{$res->title}</span> <span class='stick-right'>{$gridPerm} <small>{$editLink}<br />{$removeLink}</small></span>";
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