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
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_PageRenderer extends Zend_Form_Element/*_Submit_Multi*/ {

    /**
     * Use formHidden view helper by default
     * @var string
     */
//    public $helper = 'formHidden';
    public $helper = 'formButton';

    /**
     * Initialize object; used by extending classes
     *
     * @return void
     */
    public function init()
    {
        $this->setValue($this->getName());
    }

    protected function renderExtras($value) {
        if(empty($value)) {
            return "";
        }

        $elemid = $this->getId();
        $renderHtml = "";
//        $modalid = $this->getName()."-modal";
//        $formid = 'entityform';
//        $fullentity = 'Application\Entities\\'.ucfirst($this->getName());
//        $mapper = new EntityFormRenderer($fullentity,
//            array('title','longtitle','description','categories','tags','metadata','templates','variablescontent','contentvariable','page')
//        );
//        $parentName = strtolower($this->getAttrib('entityName'));
//        $parentId = $this->getAttrib('entityId');
//        $entityName = $this->getAttrib('entityVariable');
//        $form = $mapper->getForm(false,false,false);
//        $form->addDecorator('HtmlTag', array('tag' => 'dl', 'id'=>$formid));
//        #Get entites to pick.
//        $bootstrap = Front::getInstance()->getParam("bootstrap");
//        $em = $bootstrap->getContainer()->get('entity.manager');
//        $ents = $em->getRepository($fullentity)->findAll();
//        $list = "<ul>\n";
//        foreach($ents as $ent){
//            $list .= "<li><a href=\"#".$ent->title."\" class=\"select-entity\" data-id=\"".$ent->id."\">".$ent->title."</a></li>";
//        }
//        $list .= "</ul>\n";
//        $rendered = $list."<hr /><h4>New</h4>".$form;
//        $rendermodal = <<<EOT
//        <div id="{$modalid}" class="modal hide fade" style="display: none; ">
//            <div class="modal-header">
//                <a href="#close" class="close">×</a>
//                <h3>{$this->getName()}</h3>
//            </div>
//            <div class="modal-body">
//                {$rendered}
//            </div>
//            <div class="modal-footer">
//                <a href="#" id="{$modalid}-save" class="btn primary">Add</a>
//                <a href="#" id="{$modalid}-cancel" class="btn secondary">Cancel</a>
//            </div>
//        </div>
//EOT;
//        $jqnc = JQueryViewHelper::getJQueryHandler();
//        $scr = <<<SCR
//        /* <![CDATA[ */
//            {$jqnc}(document).ready(function() {
//                {$jqnc}('#{$modalid}-cancel').bind('click',function(e){
//                    e.preventDefault();
//                    {$jqnc}('#{$modalid}').modal('hide');
//                });
//                {$jqnc}('#{$modalid} a.select-entity').bind('click',function(e){
//                    e.preventDefault();
//                    var entid = {$jqnc}(this).attr('data-id');
//                    var data = {'entityform': {'{$entityName}': entid}};
//                    {$jqnc}.Update({
//                        url:'/api/{$parentName}/{$parentId}/update.json',
//                        data: data,
//                        success: function(d){
//                            //alert(d);
//                        }
//                    });
//                    {$jqnc}('#{$modalid}').modal('hide');
//                });
//                {$jqnc}('#{$modalid}-save').bind('click',function(e){
//                    e.preventDefault();
//                    var form = {$jqnc}('#{$modalid} form').serialize();
//                    {$jqnc}.Create({
//						url: '/api/{$this->getName()}/create.json',
//						data: form,
//						success: function(e){
//                            alert('save::{$parentName}:{$parentId}');
//                            var data = {'entityform': {'{$this->getName()}': e.response.data.id}};
//                            {$jqnc}.Update({
//                                url:'/api/{$parentName}/{$parentId}/update.json',
//                                data: data,
//                                success: function(d){
//                                    alert(d);
//                                }});
//                    }});
//                    {$jqnc}('#{$modalid}').modal('hide');
//                });
//            });
//        /* ]]> */
//SCR;
//        #TODO save linked entity to master entity.
//        $this->getView()->inlineScript()->appendScript($scr, 'text/javascript');
//        $renderHtml = $rendermodal;
        return $renderHtml;
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
