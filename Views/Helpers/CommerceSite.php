<?php

//namespace Wednesday\View\Helper;

use Zend_View_Helper_HtmlElement as ViewHtmlElement;

/**
 * CommerceSite.php
 *
 */
class Wednesday_View_Helper_CommerceSite extends ViewHtmlElement {

    /**
     *
     * @return $this for more fluent interface
     */
    public function commerceSite($uri) {
        $bootstrap = Zend_Controller_Front::getInstance()->getParam("bootstrap");
        $this->config = $bootstrap->getContainer()->get('config');
        if (($this->view->placeholder('isEcommerce') != '1')) {
            return $this->config['settings']['application']['ecommerceFalse'];
        }
        return $this->config['settings']['application']['ecommerceUrl'] . '/' . $uri;
    }

}