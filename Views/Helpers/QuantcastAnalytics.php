<?php
//namespace Wednesday\View\Helper;

use Zend_View_Helper_HtmlElement as ViewHtmlElement;

/** Wednesday_View_Helper_
 * QuantcastAnalytics.php
 *
 * See http://www.scribd.com/doc/2261328/InstallingGATrackingCode
 *
 * @category   BaseZF
 * @package    BaseZF_Framwork
 * @copyright  Copyright (c) 2011 BaseZF
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author     jahelly
 */
class Wednesday_View_Helper_QuantcastAnalytics extends ViewHtmlElement {

    /**
     * Tracker options instance
     */
    static protected $_trackerOptionsByIds = array();

    /**
     *
     * @param string $trackerId the Quantcast analytics tracker id
     * @param array
     *
     * @return $this for more fluent interface
     */
    public function quantcastAnalytics($trackerId = null, array $options = array()) {
        if (!is_null($trackerId)) {
            $this->trackPageview($trackerId);

            if (!empty($options)) {
                $this->addTrackerOptions($trackerId, $options);
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
            throw new Exception('Unknown "' . $optionFunc . '" QuantcastAnalytics options');
        }

        if (empty($optionsArgs)) {
            throw new Exception('Missing TrackerId has first Argument on "$this->QuantcastAnalytics->' . $optionFunc . '()" function call');
        }

        $trackerId = array_shift($optionsArgs);

        $this->_addTrackerOption($trackerId, $optionsName, $optionsArgs);

        return $this;
    }

    /**
     * Add options from array
     *
     * @param string $trackerId the Quantcast analytics tracker id
     * @param array of array option with first value has option name
     *
     * @return $this for more fluent interface
     */
    public function addTrackerOptions($trackerId, array $options) {
        foreach ($options as $optionsArgs) {

            $optionsName = array_shift($optionsArgs);

            $this->_addTrackerOption($trackerId, $optionsName, $optionsArgs);
        }

        return $this;
    }

    /**
     * Add a tracker option
     *
     * @param string $trackerId the Quantcast analytics tracker id
     * @param string $optionsName option name
     * @param array $optionsArgs option arguments
     *
     * @return $this for more fluent interface
     */
    protected function _addTrackerOption($trackerId, $optionsName, array $optionsArgs = array()) {
        $trackerOptions = &$this->_getTrackerOptions($trackerId);

        array_unshift($optionsArgs, $optionsName);

        $trackerOptions[] = $optionsArgs;

        return $this;
    }

    /**
     * Get tracker's options by tracker id
     *
     * @param string $trackerId the Quantcast analytics tracker id
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
     * Rendering Quantcast Anaytics Tracker script
     */
    public function toString() {
        $xhtml = array();
        $xhtml[] = '<!-- Quantcast Tag -->';
        $xhtml[] = '<script type="text/javascript">';
        $xhtml[] = 'var _qevents = _qevents || [];';
        $xhtml[] = '';
        $xhtml[] = '(function() {';
        $xhtml[] = 'var elem = document.createElement(\'script\');';
        $xhtml[] = 'elem.src = (document.location.protocol == "https:" ? "https://secure" : "http://edge") + ".quantserve.com/quant.js";';
        $xhtml[] = 'elem.async = true;';
        $xhtml[] = 'elem.type = "text/javascript";';
        $xhtml[] = 'var scpt = document.getElementsByTagName(\'script\')[0];';
        $xhtml[] = 'scpt.parentNode.insertBefore(elem, scpt);';
        $xhtml[] = '})();';
        $xhtml[] = '';
        $xhtml[] = '_qevents.push({';

        $i = 0;
        foreach (self::$_trackerOptionsByIds as $trackerId => $options) {

            // init tracker
            $xhtml[] = 'qacct:"' . $trackerId . '"';

            $i++;
        }

        $xhtml[] = '});';
        $xhtml[] = '</script>';
        $xhtml[] = '';
        $xhtml[] = '<noscript>';
        $xhtml[] = '<div style="display:none;">';
        $xhtml[] = '<img src="//pixel.quantserve.com/pixel/p-fd0gWvSTdTaiU.gif" border="0" height="1" width="1" alt="Quantcast"/>';
        $xhtml[] = '</div>';
        $xhtml[] = '</noscript>';
        $xhtml[] = '<!-- End Quantcast tag -->';

        return implode("\n", $xhtml);
    }

}
