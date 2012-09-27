<?php
//namespace Wednesday\View\Helper;

use \Zend_Controller_Front as Front,
    \Zend_View_Helper_Abstract as ViewHelperAbstract;

/**
 * Description of Resource
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author venelin
 */
class Wednesday_View_Helper_LocaleSwitcher extends ViewHelperAbstract {

    /**
     *
     * @return string
     */
    public function localeSwitcher($locale, $available_locales) {

        //Get all the languages
        $languages = Zend_Locale::getTranslationList("language", "auto");
        
        $html = '';
        $format = "\n\t" . '<li><a href="%s"><i class="icon-flag" style="background-image: url(/library/img/flags/%s.png);"></i> %s</a></li>';

        foreach($available_locales as $available_locale){
			$html .= sprintf
			(
				$format,
				$this->localeLinkUrl($available_locale),
				strtolower(substr($available_locale, -2, 2)),
				$languages[strtolower(substr($available_locale, 0, 2))]
			);
        }

        return $html;
    }
    
    public function localeLinkUrl($available_locale){
        if(strstr($_SERVER['REQUEST_URI'], 'locale=')){
            return str_replace('locale='.$_REQUEST['locale'], 'locale='.$available_locale, $_SERVER['REQUEST_URI']);
        }elseif(!empty($_SERVER['QUERY_STRING'])){
            return $_SERVER['REQUEST_URI'].'&locale='.$available_locale;
        }else{
            return $_SERVER['REQUEST_URI'].'?locale='.$available_locale;
        }        
    }
}
