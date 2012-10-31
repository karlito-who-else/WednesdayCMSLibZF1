<?php

use \Zend_Controller_Front as Front;

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Decorator_Abstract */
require_once 'Zend/Form/Decorator/Abstract.php';

/**
 * Zend_Form_Decorator_DtDdWrapper
 *
 * Creates an empty <dt> item, and wraps the content in a <dd>. Used as a
 * default decorator for subforms and display groups.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.7.4 RC1, jameshelly $
 */
class Wednesday_Form_Decorator_DivNestWrapper extends Zend_Form_Decorator_Abstract
{
    /**
     * Default placement: surround content
     * @var string
     */
    protected $_placement = null;

    /**
     * Render
     *
     * Renders as the following:
     * <dt>$dtLabel</dt>
     * <dd>$content</dd>
     *
     * $dtLabel can be set via 'dtLabel' option, defaults to '\&#160;'
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
//        $bootstrap = Front::getInstance()->getParam("bootstrap");
//        $log = $bootstrap->getResource('Log');
//        $log->debug($content);
        $elementName = $this->getElement()->getName();

        $dtLabel = $this->getOption('dtLabel');
        $elementClass = $this->getOption('class');
        if( null === $dtLabel ) {
            $dtLabel = '<!-- dtLabel -->';
        }
        return '<div id="' . $elementName . '-element" class="' . $elementClass . '">' . $dtLabel . $content . '</div>';
    }
}
