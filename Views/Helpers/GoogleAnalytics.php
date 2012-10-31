<?php

//namespace Wednesday\View\Helper;

use Zend_View_Helper_HtmlElement as ViewHtmlElement;

/**
 * GoogleAnalytics.php
 *
 * See http://www.scribd.com/doc/2261328/InstallingGATrackingCode
 *
 * @category   BaseZF
 * @package    BaseZF_Framwork
 * @copyright  Copyright (c) 2008 BaseZF
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author     Harold Thétiot (hthetiot)
 */
class Wednesday_View_Helper_GoogleAnalytics extends ViewHtmlElement {

    /**
     * Tracker options instance
     */
    static protected $_trackerOptionsByIds = array();

    /**
     * Available Trackers options
     */
    static protected $_availableOptions = array
        (
        // Standard Options
        'trackPageview',
        'setVar',
        // ECommerce Options
        'addItem',
        'addTrans',
        'trackTrans',
        // Tracking Options
        'setClientInfo',
        'setAllowHash',
        'setDetectFlash',
        'setDetectTitle',
        'setSessionTimeOut',
        'setCookieTimeOut',
        'setDomainName',
        'setAllowLinker',
        'setAllowAnchor',
        // Campaign Options
        'setCampNameKey',
        'setCampMediumKey',
        'setCampSourceKey',
        'setCampTermKey',
        'setCampContentKey',
        'setCampIdKey',
        'setCampNoKey',
        // Other
        'addOrganic',
        'addIgnoredOrganic',
        'addIgnoredRef',
        'setSampleRate',
    );

    /**
     *
     * @param string $trackerId the google analytics tracker id
     * @param array
     *
     * @return $this for more fluent interface
     */
    public function googleAnalytics( array $options = array()) {
            if (!empty($options)) {
                foreach($options as $name => $value)
                {
                    $this->addTrackerOptions($name, $value);
                }
            }
        return $this;
    }

    /**
     * Alias to _addTrackerOption
     *
     * @param string $optionsName
     * @param array $optionsArgs
     *
     * @return $this for more fluent interface
     */
    public function __call($optionsName, $optionsArgs) {
        if (in_array($optionsName, self::$_availableOptions) === false) {
            throw new Exception('Unknown "' . $optionFunc . '" GoogleAnalytics options');
        }
        if (empty($optionsArgs)) {
            throw new Exception('Missing TrackerId has first Argument on "$this->GoogleAnalytics->' . $optionFunc . '()" function call');
        }
//        $trackerId = array_shift($optionsArgs);
        $this->_addTrackerOption($trackerId, $optionsName, $optionsArgs);
        return $this;
    }

    /**
     * Add options from array
     *
     * @param string $trackerId the google analytics tracker id
     * @param array of array option with first value has option name
     *
     * @return $this for more fluent interface
     */
    public function addTrackerOptions($trackerId, array $options) {
        foreach ($options as $optionsName => $optionsArgs) {
            //$optionsName = array_shift($optionsArgs);
            $this->_addTrackerOption($trackerId, $optionsName, $optionsArgs);
        }
        
        return $this;
    }
    /**
     * Add options from array
     *
     * @param string $trackerId the google analytics tracker id
     * @param array of array option with first value has option name
     *
     * @return $this for more fluent interface
     */
    public function addTrackerAccount($trackerId,array $accounts) {
        $trackerOptions = &$this->_getTrackerOptions($trackerId);
        foreach ($accounts as $acc) {
            array_push($trackerOptions, $acc);
        }
        return $this;
    }

    /**
     * Add a tracker option
     *
     * @param string $trackerId the google analytics tracker id
     * @param string $optionsName option name
     * @param array $optionsArgs option arguments
     *
     * @return $this for more fluent interface
     */
    protected function _addTrackerOption($trackerId, $optionsName, $optionsArgs = array()) {
        $trackerOptions = &$this->_getTrackerOptions($trackerId);
//        array_unshift($optionsArgs, $optionsName);
        $trackerOptions[$optionsName] = $optionsArgs;
        return $this;
    }

    /**
     * Get tracker's options by tracker id
     *
     * @param string $trackerId the google analytics tracker id
     *
     * @return array an array of options for requested tracker id
     */
    protected function &_getTrackerOptions($trackerId) {
        if (!isset(self::$_trackerOptionsByIds[$trackerId])) {
            self::$_trackerOptionsByIds[$trackerId] = array();
        }
        return self::$_trackerOptionsByIds[$trackerId];
    }
    

    //
    // Render

    //

    /**
     * Cast to string representation
     *
     * @return string
     */
    public function __toString() {
        return $this->toString();
    }

    /**
     * Rendering Google Anaytics Tracker script
     */
    public function toString() {
        $xhtml = array();
        $xhtml[] = '<script type="text/javascript">';
        $xhtml[] = "var _gaq = _gaq || [];";
        
        
        foreach (self::$_trackerOptionsByIds as $trackerId => $options) {
            switch($trackerId)
            {
                case "accounts":
                    $count=0;
                    foreach ($options as $account)
                    {   
                        $instanceName="";
                        if($count>=1)
                        {
                            $instanceName = 'tracker' . $count.'.';
                        }
                        $xhtml[] = "_gaq.push(['{$instanceName}_setAccount', '" . $account . "']);";
                        $xhtml[] = "_gaq.push(['{$instanceName}_trackPageview']);";
                        $count++;
                    }
                    break;
                case 'options':
                default :
                    foreach ($options as $optionName => $optionArgs) {
                        if(in_array($optionArgs,array('true','false')))
                        {
                            $optionValue = $optionArgs;
                        }
                        else
                        {
                            $optionValue = "'{$optionArgs}'";
                        }
                        $xhtml[] = "_gaq.push(['_". $optionName . "', " . $optionValue . "]);";
                    }
                    break;
            }
            
        }
        
        
//        foreach (self::$_trackerOptionsByIds as $trackerId => $options) {
//            // add options
//            foreach ($options as $optionName => $optionArgs) {
//                $xhtml[] = "_gaq.push(['tracker" . $count . '.' . $optionName . "', '" . $optionArgs . "']);";
//            }
//            // init tracker
//            $xhtml[] = "_gaq.push(['tracker" . $count . "._setAccount', '" . $trackerId . "']);";
//            $xhtml[] = "_gaq.push(['tracker" . $count . "._trackPageview']);";
//            $count++;
//        }
        
        
        $xhtml[] = "(function() {";
        $xhtml[] = "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;";
        $xhtml[] = "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';";
        $xhtml[] = "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);";
        $xhtml[] = "})();";
        $xhtml[] = '</script>';
        return implode("\n", $xhtml);
    }
}