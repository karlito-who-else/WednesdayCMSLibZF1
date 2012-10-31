<?php

namespace Wednesday\Renderers;

use \Zend_View_Interface,
    \Zend_Form;

/**
 * Common package exception interface to allow
 * users of caching only this package specific
 * exceptions thrown
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author James A Helly <james@wednesday-london.com>
 * @package Wednesday
 * @subpackage Template
 * @link http://www.wednesday-london.com
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class Template
{
    /**
     * Following best practices for PHP5.3 package exceptions.
     * All exceptions thrown in this package will have to implement this interface
     *
     * @link http://wiki.php.net/pear/rfc/pear2_exception_policy
     */
    public $baseurl;

    /**
     * Template-enable a view instance
     *
     * @param  Zend_View_Interface $view
     * @return void
     */
    public static function enableView(Zend_View_Interface $view) {
        if (false === $view->getPluginLoader('helper')->getPaths('Wednesday_View_Helper_Template')) {
            $view->addHelperPath('Wednesday/View/Helper', 'Wednesday_View_Helper_Template');
        }
    }

    /**
     * Template-enable a form instance
     *
     * @param  Zend_Form $form
     * @return void
     */
    public static function enableForm(Zend_Form $form)
    {
//        $form->addPrefixPath('ZendX_JQuery_Form_Decorator', 'ZendX/JQuery/Form/Decorator', 'decorator')
//             ->addPrefixPath('ZendX_JQuery_Form_Element', 'ZendX/JQuery/Form/Element', 'element')
//             ->addElementPrefixPath('ZendX_JQuery_Form_Decorator', 'ZendX/JQuery/Form/Decorator', 'decorator')
//             ->addDisplayGroupPrefixPath('ZendX_JQuery_Form_Decorator', 'ZendX/JQuery/Form/Decorator');
//
//        foreach ($form->getSubForms() as $subForm) {
//            self::enableForm($subForm);
//        }
//
//        if (null !== ($view = $form->getView())) {
//            self::enableView($view);
//        }
    }
}