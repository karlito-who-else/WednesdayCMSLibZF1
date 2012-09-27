<?php

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
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Decorator_HelpBlock extends Zend_Form_Decorator_Abstract {
    public function render($content)
    {
        $placement = $this->getPlacement();
        $text = $this->getOption('text');
        $output = '<p class="help-block">' . $text . '</p>';
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