<?php
namespace Wednesday\Form\Element\Groups;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    \Wednesday_Form_Element_CategoryPicker,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Form as Form,
    \Zend_Form_SubForm as SubForm,
    \Wednesday_Form_Form as WednesdayForm,
    \Zend_Controller_Front as Front;

/**
 * Description of TemplateFieldset
 * TemplateFieldset
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
 * @author mrhelly
 */
class TemplateFieldset extends FormGroupAbstract {
    const TEMPLATES = "Application\Entities\Templates";
    const CSSCLASS = "fieldset-template";
    const NAME = "template";

    /**
     * Load the default decorators
     *
     * @return Zend_Form_SubForm
     */
    public function loadDefaultDecorators() {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }
        $this->clearDecorators();
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class' => self::CSSCLASS)),
            //'Fieldset',
        ))->setElementDecorators(WednesdayForm::getElementDecorators());

        return $this;
    }

    /**
     *
     */
    public function init() {
        $jqnc = JQueryViewHelper::getJQueryHandler();
        $this->setLegend('Template Content');
        $this->setName(self::NAME);
        
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        
        $tmplopts = null;
        $tmplopts[0] = 'Select template';
        $templates = $em->getRepository(self::TEMPLATES)->findAll();
        foreach ($templates as $template) {
            $tmplopts[$template->id] = $template->title;
        }
        $this->addElement('select', 'template', array(
                'label' => 'Template',
                'multiOptions' => $tmplopts
            ));
        $inlineScript = <<<SOA
        /* <![CDATA[ */
            {$jqnc}(document).ready(function() {
                {$jqnc}('#contents-template').on('change', null, function(e){
                    var tmplid = {$jqnc}(this).val();
                    console.log('changed!' + tmplid);
                    var that = this;
                    {$jqnc}(this).attr('disabled','disabled');
                    {$jqnc}(this).addClass('disabled');
                    var apiurl = '/api/templates/'+tmplid+'/get.json';
                    {$jqnc}('#fieldset-contents').children(':not(#template-element,legend)').remove();
                    {$jqnc}('#fieldset-contents').append('<input type="hidden" name="pagecontents" value="0" />');
                    {$jqnc}.ajax({
                        url: apiurl,
                        success: function(e) {
                            console.log(e);
                            var tvars = e.response.data.templatevariables, strvar = "";
                            console.log(tvars);
                            for (i in tvars) {
                                var formurl = '/admin/pages/getvarform/'+i;
                                {$jqnc}.ajax({
                                    url: formurl,
                                    success: function(xtmpl){
                                        {$jqnc}('#fieldset-contents').append(xtmpl);
                                        {$jqnc}('#tab-contents').removeClass('ui-state-disabled');
                                    }
                                });
                            }
                            {$jqnc}(that).removeAttr('disabled');
                            {$jqnc}(that).removeClass('disabled');
                        }
                    });
                });
            });
        /* ]]> */
SOA;
        $this->getView()->inlineScript()->appendScript($inlineScript, 'text/javascript');
        $this->removeDecorator('DtDdWrapper');
    }

    public function getEntityMap() {
        $values = parent::getEntityMap();
        return $values;
    }
}
