<?php

/**
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 * PHP Version 5
 *
 * @category EasyBib
 * @package  Form
 * @author   Michael Scholl <michael@sch0ll.de>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @version    $Id: 1.7.4 RC1, jameshelly $  git: $id$
 * @link     https://github.com/easybib/EasyBib_Form_Decorator
 */

/**
 * EasyBib_Form
 *
 * Extends Zend_Form
 * - provides model support
 * - provides buildBootstrapErrorDecorators method
 *   for adding css error classes to form if not valid
 *
 * @category EasyBib
 * @package  Form
 * @author   Michael Scholl <michael@sch0ll.de>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT License
 * @version    $Id: 1.7.4 RC1, jameshelly $  Release: @package_version@
 * @link     https://github.com/easybib/EasyBib_Form_Decorator
 */
use \Wednesday\Mapping\Form\EntityFormRenderer,
    \Wednesday_Form_Element_GalleryPicker as GalleryPicker,
    \Wednesday_Form_Element_ResourcePicker as ResourcePicker,
    \Wednesday_Form_Element_GridPicker as GridRenderer,
    \Wednesday\Form\Element\Groups\StandardFooter as ActionsGroup,
    \Wednesday\Form\Element\Groups\WizardFooter as WizardGroup,
    \EasyBib_Form,
    \EasyBib_Form_Decorator as EasyBibFormDecorator,
    \Zend_Date,
    \Zend_Form,
    \Zend_Form_SubForm,
    \Zend_Form_Decorator_Abstract as DecoratorAbstract,
    \Zend_Controller_Front as Front,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper;

class Wednesday_Form_FormClient extends EasyBib_Form
{
    protected $log;
    protected $model;

    /**
     *
     */
    public function loadDefaultDecorators() {
        #Reset Decorators
        $this->setDisableLoadDefaultDecorators(true);
        #Specify Decorators
        $this->addPrefixPath('Wednesday_Form_Decorator',
                             'Wednesday/Form/Decorator/',
                             Zend_Form::DECORATOR);
        $this->addPrefixPath('EasyBib_Form_Decorator',
                             'EasyBib/Form/Decorator',
                             Zend_Form::DECORATOR);
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class' => 'form')),
            'Form'
        ));
        $this->setElementDecorators(array(
            'ViewHelper'
        ));
        $this->addElementPrefixPath(
                'EasyBib_Form_Decorator',
                'EasyBib/Form/Decorator',
                Zend_Form::DECORATOR
            );
        $this->setDisplayGroupDecorators(array(
            'FormElements',
            'Fieldset',
            new Zend_Form_Decorator_Errors(),
            new Zend_Form_Decorator_HtmlTag(array('tag' => 'div', 'class' => 'control-group'))
        ));
        $this->setElementDecorators(array(
            'ViewHelper'
        ));

        $this->removeDecorator("DtDdWrapper");
        $this->removeDecorator("Legend");
    }

    /**
     *
     * @return Zend_Form_SubForm
     */
    public function newSubForm() {
        $sub = new Zend_Form_SubForm();
        $sub->addElementPrefixPath('Wednesday_Form_Decorator',
                    'Wednesday/Form/Decorator/',
                    Zend_Form::DECORATOR);
        $sub->addElementPrefixPath(
                    'EasyBib_Form_Decorator',
                    'EasyBib/Form/Decorator',
                    Zend_Form::DECORATOR);
        return $sub;
    }

    /**
     *
     * @param string $section
     * @return string
     */
    public function getSubFormDecorators($section) {
        return array(
            'FormElements',
            array('Fieldset', array('class' => 'form-skin-1 validate')),
            array('HtmlTag', array('tag' => 'div', 'id' => $section, 'class' => 'form-seperator columns-2 clearfix')),
        );
    }

    /**
     *
     * @param string $section
     * @return string
     */
    public function getSubColumnDecorators($section) {
        return array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'div', 'class' => 'column '.$section)),
        );
    }

    /**
     *
     * @return array
     */
    public function getElementDecorators() {
        return array(
            array('ViewHelper'),
            array('BootstrapErrors'),
            array('Description', array(
                    'tag'   => 'p',
                    'class' => 'help-block span8',
                    'style' => 'color: #999;',
                    'escape' => false
                )
            ),
            array('BootstrapTag', array(
                    'class' => 'form-divide form-config-v clearfix',
                    'data-validation' => "mandatory"
                )
            ),
            array('Label', array(
                    'class' => 'control-label screen-offset'
                )
            ),
            array('DivNestWrapper', array('class' => 'input'))
        );
    }

    /**
     *
     * @return array
     */
    public function getVisibleElementDecorators() {
        return array(
            array('ViewHelper'),
            array('BootstrapErrors'),
            array('Description', array(
                    'tag'   => 'p',
                    'class' => 'help-block span8',
                    'style' => 'color: #999;',
                    'escape' => false
                )
            ),
            array('BootstrapTag', array(
                    'class' => 'form-divide form-config-v clearfix'
//                    'data-validation' => "mandatory"
                )
            ),
            array('Label', array(
                    'class' => 'control-label'
                )
            ),
            array('DivNestWrapper', array('class' => 'input'))
        );
    }

    /**
     *
     * @return array
     */
    public function getSubmitElementDecorators() {
        return array(
            array('ViewHelper'),
            array('BootstrapErrors'),
            array('Description', array(
                    'tag'   => 'p',
                    'class' => 'validation-instructions',
                    'style' => 'float: left;',
                    'escape' => false
                )
            ),
            array('BootstrapTag', array(
                    'class' => 'form-divide form-config-v clearfix'
                )
            ),
            array('Label', array(
                    'class' => 'screen-offset'
                )
            ),
            array('DivNestWrapper', array('class' => 'input submit'))
        );
    }

    /**
     *
     * @return array
     */
    public function getRadioElementDecorators() {
        return array(
            'ViewHelper',
            'Errors',
            //array('DivNestWrapper', array('class' => 'check-radio')),
            'Label',
            new Zend_Form_Decorator_HtmlTag(array('tag' => 'div', 'class' => 'check-radio'))
        );
    }

    /**
     *
     * @return array
     */
    public function getCheckboxDecorators() {
        return array(
            'ViewHelper',
            'Errors',
            array('DivNestWrapper', array('class' => 'checkbox-row clearfix')),
            'Label',
            new Zend_Form_Decorator_HtmlTag(array('tag' => 'div', 'class' => 'checkbox-group'))
        );
    }

    /**
     *
     * @return string
     */
    public function getUniqid() {
        return uniqid() . dechex(rand(65536, 1048574));
    }

    /**
     *
     * @return string
     */
    public function getPopOverLabel($label, $copy) {
        return $label.' <a class="notice" data-content="'.addslashes($copy).'" rel="popover preview" href="#" html="true" data-placement="bottom" data-original-title="'.$label.'"><i class="icon-info-sign"></i></a>';;
    }

    public function translateFormPlaceholders($subform = false) {
        if($subform == false) {
            $subform = $this;
        }
        foreach($subform->getElementsAndSubFormsOrdered() as $key => $element) {
            if($element instanceof Zend_Form || $element instanceof Zend_Form_DisplayGroup) {
                $this->translateFormPlaceholders($element);
            } else if($element instanceof Zend_Form_Element) {
                $this->translateElementPlaceholders($element);
            }
        }
    }

    protected function translateElementPlaceholders($element) {
        $placeholder = $element->getAttrib('placeholder');

//        $front = Front::getInstance();
//        $bootstrap = $front->getParam("bootstrap");
//        $this->log = $bootstrap->getResource('Log');
//        $this->log->info($element);
//        $this->log->info($placeholder);
        if(isset($placeholder)) {
            $transliterated = $this->getTranslator()->_($placeholder);
            $element->setAttrib('placeholder',$transliterated);
//            $this->log->info($transliterated);
        }
    }

}