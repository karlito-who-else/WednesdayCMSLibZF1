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
    \ZendX_JQuery_Form,
    \Zend_Form_SubForm,
    \Zend_Form_Decorator_Abstract as DecoratorAbstract,
    \ZendX_JQuery_Form_Element_UiWidget,
    \Zend_Controller_Front as Front,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper;

class Wednesday_Form_Form extends EasyBib_Form
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
        $this->addPrefixPath('ZendX_JQuery_Form_Decorator_',
                             'ZendX/JQuery/Form/Decorator/',
                             Zend_Form::DECORATOR);
        $this->setDecorators(array(
            'FormElements',
            //array('HtmlTag', array('tag' => 'div', 'class' => 'form')),
            array('FormTabs',array('placement' => DecoratorAbstract::PREPEND, 'class' => 'nav')),
//            array('AccordionContainer', array('id' => 'container')),
            'Form'
        ));
        $this->setElementDecorators(array(
            'ViewHelper'
        ));
        $this->addElementPrefixPath(
                'Wednesday_Form_Decorator',
                'Wednesday/Form/Decorator',
                Zend_Form::DECORATOR
            );
        $this->addElementPrefixPath(
                'EasyBib_Form_Decorator',
                'EasyBib/Form/Decorator',
                Zend_Form::DECORATOR
            );       
        $this->addElementPrefixPath(
                'ZendX_JQuery_Form_Decorator_',
                'ZendX/JQuery/Form/Decorator/',
                Zend_Form::DECORATOR);
        $this->setDisplayGroupDecorators(array(
            'FormElements',
            'Fieldset',
            new Zend_Form_Decorator_Errors(),
            new Zend_Form_Decorator_HtmlTag(array('tag' => 'div', 'class' => 'control-group'))
        ));
        $this->setElementDecorators(array(
            'ViewHelper'
        ));
//        $formDecorators = array_merge(EasyBibFormDecorator::$_FormDecorator[EasyBibFormDecorator::BOOTSTRAP],array('FormElements',array('FormTabs',array('placement' => DecoratorAbstract::PREPEND, 'class' => 'nav')),'Form'));
//        $this->setDecorators($formDecorators);
//        $this->setDisplayGroupDecorators(EasyBibFormDecorator::$_DisplayGroupDecorator[EasyBibFormDecorator::BOOTSTRAP]);
//        $this->setElementDecorators(EasyBibFormDecorator::$_ElementDecorator[EasyBibFormDecorator::BOOTSTRAP]);
        $this->removeDecorator("DtDdWrapper");
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
        $sub->addElementPrefixPath(
                    'ZendX_JQuery_Form_Decorator_',
                    'ZendX/JQuery/Form/Decorator/',
                    Zend_Form::DECORATOR);        
        return $sub;
    }

    /**
     *
     * @return Zend_Form_SubForm
     */
    public function newJqSubForm() {
        $sub = new ZendX_JQuery_Form();
        $sub->addElementPrefixPath('Wednesday_Form_Decorator',
                    'Wednesday/Form/Decorator/',
                    Zend_Form::DECORATOR);
        $sub->addElementPrefixPath(
                    'EasyBib_Form_Decorator',
                    'EasyBib/Form/Decorator',
                    Zend_Form::DECORATOR);        
        $sub->addElementPrefixPath(
                    'ZendX_JQuery_Form_Decorator_',
                    'ZendX/JQuery/Form/Decorator/',
                    Zend_Form::DECORATOR);
        return $sub;
    }

    /**
     *
     * @param string $section
     * @return string
     */
    public function getSubClearDecorators($section) {
        return array(
            'FormElements',
//            'Fieldset'
        );
    }
    /**
     *
     * @param string $section
     * @return string
     */
    public function getSubFormDecorators($section) {
//        return EasyBibFormDecorator::$_DisplayGroupDecorator[EasyBibFormDecorator::BOOTSTRAP];
        return array(
            'FormElements',
            'Fieldset',
            array('HtmlTag', array('tag' => 'section', 'id' => $section, 'class' => 'tab-pane control-group')),
        );
    }

    /**
     *
     * @return array
     */
    public function getElementDecorators() {
//        return EasyBibFormDecorator::$_ElementDecorator[EasyBibFormDecorator::BOOTSTRAP];
//        return array(
//            'ViewHelper',
//            'Errors',
//            array('DivNestWrapper', array('class' => 'controls')),
//            'Label',
//            new Zend_Form_Decorator_HtmlTag(array('tag' => 'div', 'class' => 'control-group'))
//        );
//        $this->addElementPrefixPath(
//                'EasyBib_Form_Decorator',
//                'EasyBib/Form/Decorator',
//                Zend_Form::DECORATOR
//            );
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
                    'class' => 'controls'
                )
            ),
            array('Label', array(
                    'escape' => false,
                    'class' => 'control-label'
                )
            ),
            array('DivNestWrapper', array('class' => 'control-group'))
//            array('HtmlTag', array(
//                    'tag'   => 'div'
//                )
//            )
        );
    }
    /**
     *
     * @return array
     */
    public function getRadioElementDecorators() {
        return array(
            array('ViewHelper'),
            array('BootstrapErrors'),
            array('Description', array(
                    'tag'   => 'p',
                    'class' => 'help-block span8',
                    'style' => 'color: #999;'
                )
            ),
            array('BootstrapTag', array(
                    'class' => 'controls'
                )
            ),
            array('Label', array(
                    'escape' => false,
                    'class' => 'control-label'
                )
            ),
            array('DivNestWrapper', array('class' => 'control-group'))
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
            array('DivNestWrapper', array('class' => 'controls')),
            array('Label', array('escape' => false)),
            new Zend_Form_Decorator_HtmlTag(array('tag' => 'div', 'class' => 'control-group'))
        );
    }

    /**
     *
     * @return string
     */
    public function getUniqid() {
        return uniqid() . dechex(rand(65536, 1048574));
    }

    public function getPopOverText($type)
    {
        #Todo make this pull from the repo
        $popOver = array();
        switch ($type)
        {
            case 'slug':
                $popoverText ='<a  class="notice"
                        data-content=
                            "A slug is the part of a URL which identifies a page using human-readable keywords.  Slugs are used to construct clean URLs that are easy to type, descriptive, and easy to remember.
                            <br/><br/>
                            For example, in this URL:
                            <br /><br />
                            <code>
                            http://site.org/2011/my-blog-post
                            </code>
                            <br /><br />
                            the slug is \'my-blog-post\'."
                        rel="popover preview"
                        href="http://en.wikipedia.org/wiki/Slug_(web_publishing)"
                        html="true"
                        data-placement="bottom"
                        data-original-title="Slug">
                        <i class="icon-info-sign"></i>
                    </a>';
                break;
            case 'homepageAssets':
                $popoverText ='<a  class="notice"
                        data-content=
                            "A slug is the part of a URL which identifies a page using human-readable keywords.  Slugs are used to construct clean URLs that are easy to type, descriptive, and easy to remember.
                            <br/><br/>
                            For example, in this URL:
                            <br /><br />
                            <code>
                            http://site.org/2011/my-blog-post
                            </code>
                            <br /><br />
                            the slug is \'my-blog-post\'."
                        rel="popover preview"
                        href="http://en.wikipedia.org/wiki/Slug_(web_publishing)"
                        html="true"
                        data-placement="bottom"
                        data-original-title="Slug">
                        <i class="icon-info-sign"></i>
                    </a>';
                break;
        }
        

        return $popoverText;
    }

    public function getPopOverLabel($label, $copy) {
        return $label.' <a class="notice" data-content="'.addslashes($copy).'" rel="popover preview" href="#" html="true" data-placement="bottom" data-original-title="'.$label.'"><i class="icon-info-sign"></i></a>';;
    }
    
    /**
     * Build Bootstrap Error Decorators
     */
    public function buildBootstrapErrorDecorators() {
        $this->log->info('Page :: buildBootstrapErrorDecorators');
        $this->parseErrors($this->getErrors());
    }
    
    protected function parseErrors($errorArray, $parent = false) {
        if($parent == false) {
            $parent = $this;
        }
        foreach ($errorArray AS $key => $errors) {
//            $this->log->info($key);
//            $this->log->info($errors);            
            if($parent->getElement($key) != null) {
//                $htmlTagDecorator = $parent->getElement($key)->getDecorator('HtmlTag');
                $htmlTagDecorator = $parent->getElement($key)->getDecorator('DivNestWrapper');
                if (empty($htmlTagDecorator)) {
//                    $this->log->info("htmlTag empty");
                    continue;
                }
                if (empty($errors)) {
//                    $this->log->info("errors empty");
                    continue;
                }
                $class = $htmlTagDecorator->getOption('class');
                $htmlTagDecorator->setOption('class', $class . ' error');
//                $this->log->info("Class:".$class);
            }
            if(is_array($errors)) {
//                $this->log->info("Recurse");
                $this->parseErrors($errors, $parent->getSubForm($key));
            }
            
        }
    }
}