<?php
//namespace Wednesday\Form\Element;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    \Default_Form_EditHCard as EditHCardForm,
    \Application\Entities\Resources,
    \Wednesday\Renderers\ResourceHtml,
    \Wednesday\Mapping\Form\EntityFormRenderer,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Controller_Front as Front,
    \RecursiveIteratorIterator,
    \RecursiveDirectoryIterator;

/**
 * Description of ResourcePicker
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_HcardEditor extends Zend_Form_Element {
    const HCARDS = "Application\Entities\hCards";
    const BRANDS = "Application\Entities\Brands";
    
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
    public function init()
    {
//        $this->setValue($this->getName());
    }

    protected function renderExtras($value) {
        $elemid = $this->getId();
        $modalid = $this->getName()."-modal";
        $jqnc = JQueryViewHelper::getJQueryHandler();
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $log = $bootstrap->getResource('Log');
        
//        $filters = $this->getAttrib('data-allowed');
//        $path = $this->getAttrib('data-filepath');
//        $assettype = $this->getAttrib('data-type');
        $id = floor($this->getValue());
        $hcard = $em->getRepository(self::HCARDS)->find($id);
        $log->info($id." ".$hcard->id);
        $renderHtml = "";
        $renderHtml .= '<a id="'.$elemid.'-select" data-toggle="modal" href="#'.$modalid.'" class="btn select-items">Update Details</a>'."\n";
        $isnew = true;

        $form = new EditHCardForm();
        if(isset($hcard)===true) {
            $log->info("Show Vcard");
            $isnew = false;
            $renderHtml .= "<hr />".$hcard->toHtml();//"<div class='well'>"."</div>"
            $ent = $hcard->toArray(true);
            $ent2 = array_merge($ent, $ent['addresses']);
            $form->populate($ent2);
        } else {
            $log->info("No Vcard");
        }
        
        $suba = $form->getSubForm('basic');
        $subb = $form->getSubForm('address');
        $suba->removeDecorator('HtmlTag');
        $subb->removeDecorator('HtmlTag');
        $rendered = $suba->render().$subb->render();

        $rendermodal = <<<EOT
        <div id="{$modalid}" class="modal hide fade" style="display: none; ">
            <div class="modal-header">
                <a href="#close" class="close">×</a>
                <h3>{$this->getLabel()}</h3>
            </div>
            <div class="modal-body" style="max-height: 300px; overflow:auto;">
                {$rendered}
            </div>
            <div class="modal-footer">
                <a href="#" id="{$modalid}-save" class="btn btn-primary">Save</a>
                <a href="#" id="{$modalid}-cancel" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
EOT;
        $url = ($isnew)?"/api/vcard/create.json":"/api/vcard/{$id}.json";
        $method = ($isnew)?"POST":"PUT";
        $scr = <<<SCR
        /* <![CDATA[ */
            {$jqnc}(document).ready(function() {
                {$jqnc}('#{$modalid}-cancel').bind('click',function(e){
                    e.preventDefault();
                    {$jqnc}('#{$modalid}').modal('hide');
                });
                {$jqnc}('#{$modalid}-save').bind('click',function(e){
                    e.preventDefault();
                    console.info("Save!!");
                    //var data = {$jqnc}("#{$modalid} .modal-body fieldset input").serialize();
                    var postdata = {
                        'addresses': {
                            'countryname' : {$jqnc}("#{$modalid} .modal-body fieldset input[name='address[countryname]']").val(),
                            'extendedaddress' : {$jqnc}("#{$modalid} .modal-body fieldset input[name='address[extendedaddress]']").val(),
                            'locality' : {$jqnc}("#{$modalid} .modal-body fieldset input[name='address[locality]']").val(),
                            'postalcode' : {$jqnc}("#{$modalid} .modal-body fieldset input[name='address[postalcode]']").val(),
                            'region' : {$jqnc}("#{$modalid} .modal-body fieldset input[name='address[region]']").val(),
                            'streetaddress' : {$jqnc}("#{$modalid} .modal-body fieldset input[name='address[streetaddress]']").val(),
                        },
                        'emails' : { 
                            'email': {$jqnc}("#{$modalid} .modal-body fieldset input[name='basic[email]']").val(),
                            'name': {$jqnc}("#{$modalid} .modal-body fieldset input[name='basic[givenname]']").val()+" "+{$jqnc}("#{$modalid} .modal-body fieldset input[name='basic[familyname]']").val(),
                            'title': {$jqnc}("#{$modalid} .modal-body fieldset input[name='basic[givenname]']").val()+" "+{$jqnc}("#{$modalid} .modal-body fieldset input[name='basic[familyname]']").val()
                        },
                        'telephones' : { 
                            'value': {$jqnc}("#{$modalid} .modal-body fieldset input[name='basic[telephone]']").val(),
                            'areacode': '00',
                            'type': 'tel'
                        },
                        'additionalname' : {$jqnc}("#{$modalid} .modal-body fieldset input[name='basic[additionalname]']").val(),
                        'familyname' : {$jqnc}("#{$modalid} .modal-body fieldset input[name='basic[familyname]']").val(),
                        'givenname' : {$jqnc}("#{$modalid} .modal-body fieldset input[name='basic[givenname]']").val(),
                        'honorificprefix' : {$jqnc}("#{$modalid} .modal-body fieldset input[name='basic[honorificprefix]']").val(),
                        'honorificsufix' : {$jqnc}("#{$modalid} .modal-body fieldset input[name='basic[honorificsufix]']").val(),
                        'brand' : {$jqnc}("#{$modalid} .modal-body fieldset select[name='basic[brand]']").val(),
                        'organizations' : {$jqnc}("#{$modalid} .modal-body fieldset select[name='basic[brand]']").val()
                    };
                    {$jqnc}(this).html("Saving");
                    {$jqnc}(this).removeClass('btn-primary');
                    {$jqnc}(this).addClass('btn-danger');
                    {$jqnc}.ajax({
                        type: '{$method}',
                        url: '{$url}',
                        data: postdata,
                        async: false,
                        success: function(result) {
                            {$jqnc}('#{$elemid}').val(result.response.data.id);
                            console.info("Store "+result.response.data.id+"");
                        }
                    });
                    {$jqnc}('#{$modalid}').modal('hide');
                });
            });
        /* ]]> */
SCR;
        $this->getView()->inlineScript()->appendScript($scr, 'text/javascript');

        $renderHtml .= $rendermodal;
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
                $content = $content.$this->renderExtras($this->getValue(), $this->getAttribs());
            }
            $content = $decorator->render($content);
        }
        return $content;
    }
}
