<?php
//namespace Wednesday\View\Helper;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract;

/**
 * Description of Resource
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_View_Helper_ApiLink extends ViewHelperAbstract {

    /**
     *
     * @return type
     */
    public function apiLink($identity) {
        $rendered = '<a id="rbw-open" class="link" data-toggle="modal" href="#modal-test" data-backdrop="static" href="#apilink">ApiLink</a>';
        $rendermodal = <<<EOT
        <div id="modal-test" class="modal hide fade" style="display: none; ">
            <div class="modal-header">
                <a href="#close" class="close">×</a>
                <h3>Modal Heading</h3>
            </div>
            <div class="modal-body">
                <p>Content loading...</p>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn primary">Primary</a>
                <a href="#" class="btn secondary">Secondary</a>
            </div>
        </div>
EOT;
        $rendered .= $rendermodal;
        $jqnc = \ZendX_JQuery_View_Helper_JQuery::getJQueryHandler();
        $inlineScript = <<<EOT
        /* <![CDATA[ */
            {$jqnc}(document).ready(function() {
                {$jqnc}("#modal-test").bind('shown', function(e){
                    e.preventDefault();
//                    alert('show!');
                });
            });
        /* ]]> */
EOT;
        $this->view->inlineScript()->appendScript($inlineScript, 'text/javascript');

        return $rendered;
    }
}
