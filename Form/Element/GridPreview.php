<?php
//namespace Wednesday\Form\Element;

use \Wednesday_Form_Element_ListSorter as ListSorterElement,
    \Wednesday\Renderers\GridRenderer,
    \Wednesday_Form_Form as WednesdayForm,
    \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    \Zend_Form_Element_Select,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Controller_Front as Front;

/**
 * Description of ResourcePicker
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_GridPreview extends Zend_Form_Element_Multi {
    const COLLECTIONS   = "Application\Entities\Collections";
    const IMAGERY       = "Application\Entities\GroupedLooks";
    const SEASONS       = "Application\Entities\SeasonPages";
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

    protected function getElementDecorators()
    {
        return array(
            array('ViewHelper'),
            array('BootstrapErrors'),
//            array('Description', array(
//                    'tag'   => 'p',
//                    'class' => 'help-block span8',
//                    'style' => 'color: #999;'
//                )
//            ),
            array('BootstrapTag', array(
                    'class' => 'grid-show'
                )
            ),
            array('Label'),
//            array('DivNestWrapper', array('class' => 'group')),
            new Zend_Form_Decorator_HtmlTag(array('tag' => 'div', 'class' => 'grid-display'))
        );
    }

    protected function renderExtras($value) {
        $jqnc = JQueryViewHelper::getJQueryHandler();
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        //$frm = new WednesdayForm();

        $elemid = $this->getId();
        $modalid = $this->getName()."-modal";
        $jqnc = JQueryViewHelper::getJQueryHandler();

        $valid = $this->getValue();
        (isset($valid)==true) ? $valid : $valid = 1;

        $this->_getMultiOptions();
        $select = new Wednesday_Form_Element_GridPicker('grid');
        $select->setRequired(false)
                ->clearDecorators()
                ->addPrefixPath('Wednesday_Form_Decorator', 'Wednesday/Form/Decorator/', WednesdayForm::DECORATOR)
                ->addPrefixPath('EasyBib_Form_Decorator', 'EasyBib/Form/Decorator', WednesdayForm::DECORATOR)
                ->addDecorators($this->getElementDecorators())
                ->setValue($valid);
        $select->setBelongsTo('grids');

        $itemOptions = $this->options;
        $item = current($this->options);
        try {
            switch(get_class($item)) {
                case self::COLLECTIONS:
                    $sorterid = $item->seasonpage->id;
                    break;
                case self::IMAGERY:
                    if((isset($item->looks)===true)&&(isset($item->looks->first()->products)===true)){
                        $sorterid = $item->looks->first()->products->first()->collection->id;
                    }
                    break;
                default:
                    $sorterid = $valid;
                    break;
            }

        } catch (Exception $exc) {
            //$this->log->info($exc->getTraceAsString());
            $sorterid = $valid;
        }

        $sorter = new ListSorterElement('items');
        $sorter->setRequired(false)
            ->setAttrib('class', 'item-selector resources')
            ->setMultiOptions($itemOptions)
            ->clearDecorators()
            ->addPrefixPath('Wednesday_Form_Decorator', 'Wednesday/Form/Decorator/', WednesdayForm::DECORATOR)
            ->addPrefixPath('EasyBib_Form_Decorator', 'EasyBib/Form/Decorator', WednesdayForm::DECORATOR)
            ->addDecorators($this->getElementDecorators())
            ->setValue($sorterid);
        $sorter->setBelongsTo('grids');

        $renderHtml  = '';

$controlContainer = <<<TMPL
        <div id="grid-preview">
            <div class="container">
                <div class="row">
                    <div id="items-grid" class="span5">
                        {$select}
                    </div>
                    <div id="items-panel" class="span5">
                        {$sorter}
                    </div>
                </div>
            </div>
        </div>
TMPL;

        $renderHtml = $controlContainer;
        $gridscr = <<<SCR
        /* <![CDATA[ */
            {$jqnc}(document).ready(function() {

                {$jqnc}(document).on( 'mouseenter', 'div#items-panel ul.thumbnails li', function(e) {
                        var num = $(this).index();
                        //console.log('Over look_'+num);
                        {$jqnc}("div.grid-flexible-container .spotlight").removeClass('highlight');
                        {$jqnc}("div.grid-flexible-container .spotlight.look"+num).addClass('highlight');
                    }
                );

            });
        /* ]]> */
SCR;

        $this->getView()->inlineScript()->appendScript($gridscr, 'text/javascript');
        return $renderHtml;
    }

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
            if(get_class($decorator) == 'Zend_Form_Decorator_ViewHelper') {
//            if(get_class($decorator) == 'EasyBib_Form_Decorator_BootstrapTag') {
                $content = $content.$this->renderExtras($this->getValue());
            }
            $content = $decorator->render($content);
        }
        return $content;
    }
}