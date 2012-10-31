<?php
//namespace Wednesday\Form\Element;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    Application\Entities\Resources,
    \Wednesday\Renderers\GridRenderer,
    \EasyBib_Form,
    \EasyBib_Form_Decorator as EasyBibFormDecorator,
    \Wednesday_Form_Form as WednesdayForm,
    \Zend_Form_Element_Select,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Controller_Front as Front;

/**
 * Description of ResourcePicker
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_GridPicker extends Zend_Form_Element {
    const GRIDS         = "Application\Entities\Grids";

    /**
     * Use formHidden view helper by default
     * @var string
     */
    public $helper = 'formHidden';

    /**
     * Initialize object; used by extending classes
     *
     * @return void
     */
    public function init()
    {
//        $this->setValue($this->getName());
    }

    protected function renderExtras($value) {
        $jqnc = JQueryViewHelper::getJQueryHandler();
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $frm = new WednesdayForm();
        
        $valid = $this->getValue();
        (isset($valid)==true) ? $valid : $valid = 1;

        $elemid = $this->getId();
        $modalid = $this->getName()."-modal";
        $jqnc = JQueryViewHelper::getJQueryHandler();
        $grids = $em->getRepository(self::GRIDS)->findAll();
        $gridOptions = array();
        foreach($grids as $grd) {
            $gridOptions[$grd->id] = $grd->title;
        }
        
        $selectitem = new Zend_Form_Element_Select('grid');
        $selectitem->setRequired(false)
            ->setLabel('Select Grid')
            ->setAttrib('class', 'grid-selector span3')
            ->setMultiOptions($gridOptions)
            ->clearDecorators()
            ->addPrefixPath('Wednesday_Form_Decorator', 'Wednesday/Form/Decorator/', WednesdayForm::DECORATOR)
            ->addPrefixPath('EasyBib_Form_Decorator', 'EasyBib/Form/Decorator', WednesdayForm::DECORATOR)
            ->addDecorators($frm->getElementDecorators())
            ->setValue($valid);
        $selectitem->setBelongsTo('grids');
        $grid = $em->getRepository(self::GRIDS)->findOneById($valid);
        $gridRenderer = new GridRenderer($grid, array('type' => 'admin'));

        $renderHtml = <<<EOT
                        <div class="grid-preview-controls">
                            {$selectitem}
                        </div>
                        <div class="grid-flexible-container">
                            {$gridRenderer}
                        </div>
EOT;

    $scr = <<<SCR
        /* <![CDATA[ */
            {$jqnc}(document).ready(function() {

                {$jqnc}('select.grid-selector').on('change',function(e){
                    e.preventDefault();
                    //console.log({$jqnc}(this).val());
                    {$jqnc}.ajax({
                        url:'/admin/grid/'+{$jqnc}(this).val(),
                        type: 'POST',
                        success: function(d){
                            {$jqnc}('div.grid-flexible-container').replaceWith(d);
                            //console.log(d);
                        }
                    });
                });

            });
        /* ]]> */
SCR;

        $this->getView()->inlineScript()->appendScript($scr, 'text/javascript');
        return $renderHtml;
    }

//    protected function renderCategory($id) {
//        $bootstrap = Front::getInstance()->getParam("bootstrap");
//        $em = $bootstrap->getContainer()->get('entity.manager');
//        $res = $em->getRepository("Wednesday\Models\Categories")->findOneById($id);
//        return "{$res->title}";
//    }

    /**
     * Render form element
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if ($this->_isPartialRendering) {
            return '';
        }

        if (null !== $view) {
            $this->setView($view);
        }

        $content = '';
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
//            if(get_class($decorator) == 'Zend_Form_Decorator_HtmlTag') {
//            if(get_class($decorator) == 'Zend_Form_Decorator_ViewHelper') {
            if(get_class($decorator) == 'EasyBib_Form_Decorator_BootstrapTag') {
                $content = $content.$this->renderExtras($this->getValue());
            }
            $content = $decorator->render($content);
        }
        return $content;
    }
}