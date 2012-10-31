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
class Wednesday_Form_Element_TemplatePicker extends Zend_Form_Element {

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
        //$this->setValue($this->getName());
    }

    protected function renderExtras($value, $attribs) {
        $elemid = $this->getId();
        $modalid = $this->getName()."-modal";
        $jqnc = JQueryViewHelper::getJQueryHandler();
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
//        $cats = $em->getRepository("Application\Entities\Templates")->getRootNodes();
        #TODO Get list of themes
        $themepath = WEB_PATH . "/themes/";
        $themes = array();
        foreach(scandir($themepath) as $folder){
            if(!in_array($folder, array('.','..','.DS_Store'))){
                $themes[$folder] = $folder;
            }
        }
        $templatelist = array();
        foreach($themes as $themename => $themealias) {
            foreach(scandir($themepath.$themealias.'/views/templates/') as $template){
                if(!in_array($template, array('.','..','.DS_Store'))) {
                    if(!is_dir($themepath.$themealias.'/views/templates/'.$template)){
                        $tmplformatted = str_replace('.html.phtml','',$template);
                        $templatelist[$themename][] = $tmplformatted;
                    }
                }
            }
        }

        $renderHtml .= '<p>'."\n";
        $renderHtml .= '<a id="'.$elemid.'-select" data-toggle="modal" href="#'.$modalid.'" class="btn select-items">Select Template File</a>'."\n";
        $renderHtml .= '</p>'."\n";

        $templateListHtml = "";
//        $templateListHtml .= '<ul class="tabs" data-tabs="tabs">'."\n";
//        foreach($templatelist as $theme => $templates){
//            $templateListHtml .= "<li>"."\n";
//            $templateListHtml .= '<a href="#'.$theme.'">'.$theme.'</h4>'."\n";
//            $templateListHtml .= "</li>"."\n";
//        }
//        $templateListHtml .= "</ul>"."\n";
        $templateListHtml .= '<ul class="tab-content unstyled muted">'."\n";
        foreach($templatelist as $theme => $templates){
            $templateListHtml .= '<li id="'.$theme.'">'."\n";
            $templateListHtml .= '<h4>Theme: '.$theme.'</h4>'."\n";
            $templateListHtml .= '<ul class="unstyled">'."\n";
            foreach($templates as $template) {
                $templateListHtml .= '<li><a href="#'.$theme.'/views/templates/'.$template.'" class="template-item">'.$template.'</a></li>'."\n";
            }
            $templateListHtml .= "</ul>"."\n";
            $templateListHtml .= "</li>"."\n";
        }
        $templateListHtml .= "</ul>"."\n";

        if(!is_object($value)) {
            $value = $em->getRepository("Application\Entities\Templates")->find($this->getValue());
        }
        $selectedTemplate = $value->title;
//        foreach($value as $id) {
//           $selectedTemplate .= ' '.$this->renderCategory($id).',';
//        }

        $renderHtml .= '<div class="well" style="width:730px;">'."\n";
        $renderHtml .= '<p><strong>Selected:</strong> ';
        $renderHtml .= ''.$selectedTemplate.'';
        $renderHtml .= '</p>'."\n";
        $renderHtml .= '</div>'."\n";


        $rendered = $templateListHtml;

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
                <a href="#" id="{$modalid}-save" class="btn primary">Add</a>
                <a href="#" id="{$modalid}-cancel" class="btn secondary">Cancel</a>
            </div>
        </div>
EOT;

        $scr = <<<SCR
        /* <![CDATA[ */
            {$jqnc}(document).ready(function() {
                {$jqnc}('#{$modalid}-cancel').bind('click',function(e){
                    e.preventDefault();
                    {$jqnc}('#{$modalid}').modal('hide');
                });
                {$jqnc}('#{$modalid}-save').bind('click',function(e){
                    e.preventDefault();
                    {$jqnc}('#{$modalid}').modal('hide');
                });
                {$jqnc}('.template-item').bind('click',function(e){
                    e.preventDefault();
                    var tmpl = {$jqnc}(this).attr('href');
                    var data = {'tmpl': tmpl.substring(1, tmpl.length)};

                    {$jqnc}.post('/admin/templates/getvars', data, function(d){
                        {$jqnc}("#tab-variables").removeClass('ui-state-disabled');
                        {$jqnc}("#fieldset-variables").empty().append(d);
                        {$jqnc}("#{$modalid}").modal('hide');
                        {$jqnc}("#tab-variables a").trigger('click');
                    });
                    var partiala = tmpl.substring(1, tmpl.length);//.replace(/.html.phtml/gi,'');
                    var partialArray = partiala.split('/');
                    var theme = partialArray[0];
                    var partial = partiala.replace(partialArray[0]+'/views/','');
                    {$jqnc}("#{$elemid}").val(partial);
                    {$jqnc}("#template-partial").val(partial);
                    {$jqnc}("#template-theme").val(theme);
                    {$jqnc}.jGrowl("Please wait while template information is loaded.", { header: 'Template Manager' });
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
