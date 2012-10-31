<?php
//namespace Wednesday\Form\Element;

use \Zend_Form_Decorator,
    \Zend_Form_Decorator_Abstract,
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
class Wednesday_Form_Decorator_HelpBlock extends Zend_Form_Decorator_Abstract {
    public function render($content)
    {
        $placement = $this->getPlacement();
        $text = $this->getOption('text');
        $output = '<p class="controls help-block">' . $text . '</p>';
        switch($placement)
        {
            case 'PREPEND':
                return $output . $content;
            case 'APPEND':
            default:
                return $content . $output;
        }
    }
}
