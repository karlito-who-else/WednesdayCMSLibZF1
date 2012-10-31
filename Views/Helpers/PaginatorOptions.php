<?php
//namespace Wednesday\View\Helper;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract;

/**
 * Description of PaginatorOptions
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_View_Helper_PaginatorOptions extends ViewHelperAbstract {

    /**
     *
     * @return type
     */
    public function paginatorOptions($per_page) {
        
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $this->config = $bootstrap->getContainer()->get('config');
        
        $limitPageSelector = new Zend_Form_Element_Select('limit');
        $limitPageSelector->setMultiOptions($this->config['settings']['application']['pagelimit'])
                          ->setValue($per_page)
                          ->setDecorators(array('ViewHelper'));
        
        return $limitPageSelector;
    }
}
