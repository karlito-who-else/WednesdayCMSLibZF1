<?php
//namespace Wednesday\Form\Element;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
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
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_FilePicker extends Zend_Form_Element {

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
        
        $filters = $this->getAttrib('data-allowed');
        $path = $this->getAttrib('data-filepath');
        $assettype = $this->getAttrib('data-type');
        #TODO Get list of themes
        $filepath = WEB_PATH . "/{$path}/";
        $exts = "(".implode("|",explode(",", str_replace("'", "", $filters))).")";
        $regex = '/^.+\.'.$exts.'$/i';
//        var_dump($regex);
        $Directory = new RecursiveDirectoryIterator($filepath);
        $Iterator = new RecursiveIteratorIterator($Directory);
        $Regex = new RegexIterator($Iterator, $regex, RecursiveRegexIterator::GET_MATCH);
        $Files = iterator_to_array($Regex,true);
//        var_dump($Files); 

//        $renderHtml .= '<div class="input">'."\n";
        $renderHtml .= '<a id="'.$elemid.'-select" data-toggle="modal" href="#'.$modalid.'" class="btn select-items">Select File</a>'."\n";

        $templateListHtml = "";
        $templateListHtml .= '<ul class="unstyled muted">'."\n";
        foreach($Files as $filename => $filedetail) {
            $fnamear = explode("/", $filename);
            $fname = array_pop($fnamear);
            $templateListHtml .= '<li><a href="'.str_replace(WEB_PATH, "", $filename).'" class="template-item">'.$fname.'</a></li>'."\n";
        }
        $templateListHtml .= "</ul>"."\n";
//
//        $renderHtml .= '<p><strong>Selected:</strong> ';
//        $renderHtml .= ''.$value.'';
//        $renderHtml .= '</p>'."\n";

//        $renderHtml .= '<div>'."\n";

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
                    {$jqnc}("#{$elemid}").val(tmpl);
                    {$jqnc}('#{$modalid}').modal('hide');
//                    {$jqnc}.jGrowl("Please wait while template information is loaded.", { header: 'Template Manager' });
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
