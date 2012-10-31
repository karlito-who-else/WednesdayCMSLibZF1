<?php
//namespace Wednesday\Form\Element;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    Application\Entities\Resources,
    \Wednesday\Renderers\GridRenderer,
    \EasyBib_Form,
    \EasyBib_Form_Decorator as EasyBibFormDecorator,        
    \Zend_Form_Element_Select,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Controller_Front as Front;

/**
 * Description of ResourcePicker
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_QqUploader extends Zend_Form_Element {
    
    /**
     * Use formHidden view helper by default
     * @var string
     */
    public $helper = 'formHidden';

    /**
     * Initialize object; used by extending classes
     *
     * @return void
     * /
    public function init() {

    }
     */

    protected function renderExtras($value) {
        $jqnc = JQueryViewHelper::getJQueryHandler();
        $name = "qquploader";
        $elemid = $this->getId();
        $qqelemid = 'qquploader-'.$this->getId();
        $label = $this->getLabel();
        $this->setLabel('');
        $jqnc = JQueryViewHelper::getJQueryHandler();
        $filters = $this->getAttrib('data-allowed');
        $filepath = $this->getAttrib('data-filepath');
        $assettype = $this->getAttrib('data-type');
        $inlineScript = <<<SOA
        /* <![CDATA[ */
            \$LAB
            .script(window.CMS.config.site.uri + 'library/js/jquery/plugins/jgrowl/v1.2.6/jquery.jgrowl_minimized.js')
            .script(window.CMS.config.site.uri + 'library/js/fileuploader/vb3b20b156d/fileuploader.js')
            .wait(
                function() {
                    window.uploader = new qq.FileUploader({
                        element: {$jqnc}('#{$qqelemid} div.uploader')[0],
                        action: window.CMS.config.site.uri + 'admin/wizard/upload/upload',
                        params: {
                            'filepath':'{$filepath}',
                            'type':'{$assettype}'
                        },
                        allowedExtensions: [{$filters}],
                        sizeLimit: 0, // max size
                        minSizeLimit: 0, // min size
                        template: '<div class="qq-uploader">' + '<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>' + '<div class="qq-upload-button btn btn-primary">{$label}' + '<ul class="qq-upload-list"></ul>' + '</div></div>',
                        debug: true,
                        onSubmit: function(id, fileName) {
                            {$jqnc}.jGrowl("Upload of '"+fileName+"' has begun", { header: 'File Upload' });
                            {$jqnc}('#{$qqelemid} .qq-upload-button').removeClass('btn-primary btn-warning btn-success');
                            {$jqnc}('#{$qqelemid} .qq-upload-button').addClass('btn-danger');
                        },
                        onProgress: function(id, fileName, loaded, total) {
                            var percentage = parseInt((loaded / total) * 100);
                            {$jqnc}.jGrowl(fileName+" "+percentage+"% uploaded.", { header: 'File Upload' });
                        },
                        onComplete: function(id, fileName, responseJSON) {
                            {$jqnc}('#{$qqelemid} .qq-upload-button').removeClass('btn-primary btn-danger btn-warning');
                            {$jqnc}('#{$qqelemid} .qq-upload-button').addClass('btn-success');
                            {$jqnc}.jGrowl("Asset upload process was completed.", {header: 'File Upload'});
                            console.log('complete');
                            {$jqnc}('#{$elemid}').val(responseJSON.upload);
                            
//                            console.log(responseJSON);
                        },
                        onCancel: function(id, fileName) {
                            {$jqnc}.jGrowl("Asset upload process was cancelled.", {header: 'File Upload'});
                            console.log('cancelled');
                        },
                        messages: {
                            // error messages, see qq.FileUploaderBasic for content
                            typeError: '{file} has invalid extension. Only {extensions} are allowed.',
                            sizeError: '{file} is too large, maximum file size is {sizeLimit}.',
                            minSizeError: '{file} is too small, minimum file size is {minSizeLimit}.',
                            emptyError: '{file} is empty, please select files again without it.',
                            onLeave: 'The files are being uploaded, if you leave now the upload will be cancelled.'
                        },
                        showMessage: function(message) {
                            {$jqnc}.jGrowl(message, {header: 'File Upload'});
                            {$jqnc}('#{$qqelemid} .qq-upload-button').removeClass('btn-primary btn-danger btn-success');
                            {$jqnc}('#{$qqelemid} .qq-upload-button').addClass('btn-warning');
                            console.log('message: ' + message);
                        }                            
                    });
                    //{$jqnc}.jGrowl("Uploader Initialised...", { header: 'File Upload' });
                }
            );
        /* ]]> */
SOA;
        $this->getView()->inlineScript()->appendScript($inlineScript, 'text/javascript');
        $renderHtml = <<<EOT
            <div id="{$qqelemid}">
                <div class="uploader">
                    
                </div>
            </div>
EOT;
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