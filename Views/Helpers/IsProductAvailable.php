<?php

//namespace Wednesday\View\Helper;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_HtmlElement as ViewHtmlElement;

/**
 * IsProductAvailable.php
 *
 */
class Wednesday_View_Helper_IsProductAvailable extends ViewHtmlElement {

    public function isProductAvailable($sku, $title) {
        $link = utf8_decode($title);
        $bootstrap = Front::getInstance()->getParam('bootstrap');
        $this->log = $bootstrap->getResource('Log');
        $this->log->info($sku." ".$title);
        if (empty($sku) === true) {
            return $link;
        }
        //$bootstrap = Zend_Controller_Front::getInstance()->getParam("bootstrap");
        $this->config = $bootstrap->getContainer()->get('config');
        $this->locale = $bootstrap->getContainer()->get('locale');
        
        $this->log->info($sku." ".$title);
        
        #Default link if we are not in a shoppable locale is...
        if (($this->view->placeholder('isEcommerce') != '1')) {
            $link = '<a href="' . $this->config['settings']['application']['ecommerceFalse'] . '">' . utf8_decode($title) . '</a>';
            return $link;
        }
        $country = strstr($this->locale, '_');
        $this->log->info($sku." ".$title);
        //$productDetailsPageUrl = "http://xymoncler.yoox.biz/item.asp?cod10=%d";
        $productDetailsPageUrl = $this->config['settings']['application']['ecommerceUrl'] . "/item.asp?cod10=%d";
        $productDetailsApiUrl = $this->config['settings']['application']['apiUrl'] . "/Item.API/1.0/MONCLER%s/item/%d.json"; //GB, 39223569
        $productDetailsApiUrlCurrent = sprintf($productDetailsApiUrl, $country, $sku);
        $productDetailsApiClient = new Zend_Http_Client();
        $productDetailsApiClient->setUri($productDetailsApiUrlCurrent);
        $productDetailsApiResponse = $productDetailsApiClient->request();

        if ($productDetailsApiResponse->isSuccessful()) {
            $productDetails = Zend_Json::decode($productDetailsApiResponse->getBody());
            $productQuantity = 0;
            foreach ($productDetails['Item']['ModelColorSize'] as $modelColorSize) {
                $productQuantity = $productQuantity + (int) $modelColorSize['Quantity'];
            }
            if ($productDetails['Item']['Code8'] && ($productQuantity > 0)) {
                $productDetailsPageUrlCurrent = sprintf($productDetailsPageUrl, $productDetails['Item']['Code8']);
                $link = '<a href="' . $productDetailsPageUrlCurrent . '">' . utf8_decode($title) . '<!-- productDetailsApiUrlCurrent: ' . $productDetailsApiUrlCurrent . ', productQuantity: ' . $productQuantity . ' --></a>';
            }
        } else {
            error_log('API response not successful');
            error_log($productDetailsApiUrlCurrent);
        }
        $this->log->info($sku." ".$title." - ".$link);
        return $link;
    }

}