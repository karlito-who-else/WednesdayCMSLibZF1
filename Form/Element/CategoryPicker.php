<?php
//namespace Wednesday\Form\Element;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    Application\Entities\Resources,
    Wednesday\Renderers\ResourceHtml,
    Wednesday\Mapping\Form\EntityFormRenderer,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Controller_Front as Front;

/**
 * Description of ResourcePicker
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_CategoryPicker extends Zend_Form_Element {
    const CATEGORIES = 'Wednesday\Models\Categories';

    /**
     * Use formHidden view helper by default
     * @var string
     */
    public $helper = 'formHidden';

    /**
     * Initialize object; used by extending classes
     *
     * @return void
     */
    public function init() {
//        $this->setValue($this->getName());
    }

    protected function renderExtras() {
        $value = $this->getValue();
        $elemid = $this->getId();
        $modalid = $this->getName()."-modal";
        $jqnc = JQueryViewHelper::getJQueryHandler();
        $bootstrap = Front::getInstance()->getParam("bootstrap");
//        $em = $bootstrap->getContainer()->get('entity.manager');
//        $cats = $em->getRepository(self::CATEGORIES)->getRootNodes();
        
        $log = $bootstrap->getResource('Log');

        $renderHtml = '';
        $renderHtml .= '<p><a id="'.$elemid.'-select" data-toggle="modal" href="#'.$modalid.'" data-backdrop="static" class="btn input select-items">Select Categories</a></p>'."\n";
        $renderHtml .= '<div class="well" style="width:730px;">'."\n";
        $renderHtml .= '<span><strong>Selected:</strong> ';
        if(!is_array($value)) {
            $value = explode(",",$this->getValue());
        }
        $selectedCategories = "";
        foreach($value as $id) {
           $selectedCategories .= ''.$this->renderCategory($id).',';
        }
        foreach($value as $id) {
           $selectedNodes .= '"node-'.$id.'",';
        }
        $selectedNodes = trim($selectedNodes,' ,');
//        $log->info($selectedCategories);
        $renderHtml .= trim(trim($selectedCategories,' ,'),',');
        $value = implode(",",$value);
        $this->getView()->placeholder('entity.selected')->set($value);

        $renderHtml .= '</span>'."\n";
        $renderHtml .= '</div>'."\n";

        $rendered = '<div id="catree" class="jstree-wednesday">';
        //$rendered .= '<ul class="tree-view-leaf">'.$this->getView()->partialLoop('treeview/treeleafs.render.phtml', $cats);
        $rendered .= '</div>';

        $rendermodal = <<<EOT
        <div id="{$modalid}" class="modal hide fade" style="display: none; ">
            <div class="modal-header">
                <a id="{$modalid}-close" href="#close" class="close">x</a>
                <h3>{$this->getName()}</h3>
            </div>
            <div class="modal-body" style="max-height: 300px; overflow:auto;">
                {$rendered}
            </div>
            <div class="modal-footer">
                <a href="#" id="{$modalid}-save" class="btn primary">Add</a>
                <a href="#" id="{$modalid}-cancel" class="btn secondary">Cancel</a>
            </div>
        </div>
EOT;
        $scr = <<<SCR
        /* <![CDATA[ */
            {$jqnc}(document).ready(function() {               
                {$jqnc}('#{$modalid}-cancel, #{$modalid}-close').bind('click',function(e){
                    e.preventDefault();
                    {$jqnc}('#{$modalid}').modal('hide');
                });
                {$jqnc}('#{$modalid}-save').bind('click',function(e){
                    e.preventDefault();
                    var items = '', selected = {$jqnc}("#catree").jstree('get_checked',false,true);
                    console.log(selected);
                    selected.each(function(idx, inst) {
//                    console.log(inst);
                        var txid = {$jqnc}(this).attr('id');
                        var theid = txid.replace('node-','');
                        items += ''+theid+',';
                        console.log({$jqnc}(this).attr('id'));
                        //console.log({$jqnc}('a',this).text());
                    });
                    {$jqnc}(".well span").empty().append('<strong>Please save the page to set the selected categories.</strong>');
                    {$jqnc}("#{$elemid}").val(items);
                    {$jqnc}('#{$modalid}').modal('hide');
                });
                //When jsTree is ready.
                var selectInitial = [{$selectedNodes}];
                var t=setTimeout(function(){
                    for(var name in selectInitial) {
                        console.log(selectInitial[name]);
                        var node = {$jqnc}('#'+selectInitial[name]);
                        {$jqnc}("#catree").jstree('check_node', node);
                    }
                    console.log(selectInitial);
                },8000);

            });
        /* ]]> */
SCR;
        $this->getView()->inlineScript()->appendScript($scr, 'text/javascript');
        $renderHtml .= $rendermodal;
        return $renderHtml;
    }

    protected function renderCategory($id) {
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $log = $bootstrap->getResource('Log');
        $log->info($id);
        $res = $em->getRepository(self::CATEGORIES)->findOneById($id);
        $log->info($res->title);
        if($res->id > 0) {
            return "{$res->title}";
        }
        
        return "No Categories Selected";
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
