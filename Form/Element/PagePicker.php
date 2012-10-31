<?php

//namespace Wednesday\Form\Element;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    \Application\Entities\Resources,
    \Wednesday\Renderers\ResourceHtml,
    \Wednesday\Mapping\Form\EntityFormRenderer,
    \Wednesday_Form_Element_PageSorter as PageSorterElement,
    \Wednesday_Form_Form as WednesdayForm,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Controller_Front as Front;

/**
 * Description of PagePicker
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_PagePicker extends Zend_Form_Element/* _Submit_Multi */ {
    const PAGES = "Application\Entities\Pages";
    const RESOURCE      = "Application\Entities\MediaResources";

    /**
     * Use formHidden view helper by default
     * @var string
     */
    public $helper = 'formHidden';
    public $log;
    private $em;

    /**
     * Initialize object; used by extending classes
     *
     * @return void
     */
    public function init() {
        
        $bootstrap = Front::getInstance()->getParam("bootstrap");

        $this->log = $bootstrap->getResource('Log');
        $this->em = $bootstrap->getContainer()->get('entity.manager');
//        $this->setValue($this->getName());
    }

    protected function getJSTreeHtml($pages) {
        $returnHtml = "";
        $returnHtml .= "<ul>";
        foreach ($pages as $page) {
            $returnHtml .= '<li id="node-' . $page['id'] . '" '.((isset ($page['childrenIds']))? 'data-child-ids="'.$page['childrenIds'].'"':'').''.$this->loadPagePreview($page['id'],'data-imagescr').'>';
            $returnHtml .= '<a href="#' . $page['slug'] . '">' . $page['title'] . '</a>';
            if (is_array($page['children'])) {
                $returnHtml .= $this->getJSTreeHtml($page['children']);
            }
            $returnHtml .= "</li>";
        }
        $returnHtml .= "</ul>";
        return $returnHtml;
    }

    private function organiseArray($nodes, $selected) {

        $organisedArray = array();
        foreach ($selected as $key => $value) {
            foreach ($nodes as $index => $page) {
                if (count($page['children']) > 0) {
                    $nodes[$index]['children'] = $this->organiseArray($page['children'], $selected);
                    $childIds = array();
                    foreach ($nodes[$index]['children'] as $child)
                    {
                        array_push($childIds, $child['id']);
                    }
                    $nodes[$index]['childrenIds'] = implode(',', $childIds);
                }
                if ($page['id'] == $value) {
                    array_push($organisedArray, $page);
                    unset($nodes[$index]);
                    break;
                }
            }
        }
        if(count($organisedArray)>0) {
            foreach ($nodes as $page) {
                array_push($organisedArray, $page);
            }
            $this->log->info($organisedArray);
            return $organisedArray;
        } else {
            return $nodes;
        }
    }
    
    private function loadPagePreview($pageId,$dataName)
    {
        //so far ONLY INTERESTED IN THE PREVIEW IMAGE
//        $tvars = array();
        $imgsrc= false;
            $ent = $this->em->getRepository(self::PAGES)->findOneById($pageId);
            if(is_numeric($ent->getTvarData('previewimage')->value))
            {
                $resouce = $this->em->getRepository(self::RESOURCE)->findOneById($ent->getTvarData('previewimage')->value);
                $resouce->setVariation('homepagesmall');
                $imgsrc = $dataName.'="'.$resouce->link.'"';
            }
        return $imgsrc;
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

    protected function renderExtras($value,$tvarsId) {
        $renderHtml = "";
        $elemid = $this->getId();
        $modalid = "pages-modal";
        $jqnc = JQueryViewHelper::getJQueryHandler();

        $pageSelected = $this->em->getRepository(self::PAGES)->getInOrder(explode(',', $value));
        $sorter = new PageSorterElement('items');
        $sorter->setRequired(false)
            ->setAttrib('class', 'item-selector resources')
            ->setMultiOptions($pageSelected)
            ->clearDecorators()
            ->addPrefixPath('Wednesday_Form_Decorator', 'Wednesday/Form/Decorator/', WednesdayForm::DECORATOR)
            ->addPrefixPath('EasyBib_Form_Decorator', 'EasyBib/Form/Decorator', WednesdayForm::DECORATOR)
            ->addDecorators($this->getElementDecorators())
            ->setValue($tvarsId);
//        $sorter->setBelongsTo('grids');
        
        
//        $renderHtml .= '<a id="' . $elemid . '-select" data-toggle="modal" data-modal-type="' . $modalid . '" href="#' . $modalid . '" class="btn input select-pages">Select Pages</a>' . "\n";
$renderHtml .= <<<SCR
        <div class="page-picker" id="pige-picker-{$tvarsId}">
                <a id="{$elemid}-select" data-toggle="modal" data-modal-type="{$modalid}" href="#{$modalid}" class="btn input select-pages">Select Pages</a>
                <h4>Page Selected</h4>
                <br />
                <div class="well span9">
                    {$sorter}
                </div>
        </div>
SCR;
                        
                        
                        
        $pages = $this->em->getRepository(self::PAGES)->childrenHierarchy();

        $selected = $this->getValue();
        $selected = explode(',', $selected);

        $this->log->info('++++++++++++++++++++++++++++++++++++++++++++++++++');
        $organised = $this->organiseArray($pages, $selected);
        $this->log->info($organised[0]['children'][3]);
        $this->log->info('++++++++++++++++++++++++++++++++++++++++++++++++++');
        
        $modalbody = "Page list here:<div class=\"pagestree\">" . $this->getJSTreeHtml($organised) . "</div>";

        $rendermodal = <<<EOT
        <div id="{$modalid}" class="modal hide fade">
            <div class="modal-header">
                <a href="#close" class="close">&times;</a>
                <h3>Pages</h3>
            </div>
            <div class="modal-body" style="max-height: 300px; overflow:auto;">
                {$modalbody}
            </div>
            <div class="modal-footer">
                <a href="#" id="{$modalid}-save" class="btn primary">Save</a>
                <a href="#" id="{$modalid}-cancel" class="btn secondary">Cancel</a>
            </div>
        </div>
EOT;

        $scr = <<<SCR
        /* <![CDATA[ */
//            {$jqnc}(document).ready(function() {
            \$LAB
                              
//            });
        /* ]]> */
SCR;
        $this->getView()->inlineScript()->appendScript($scr, 'text/javascript');
        $renderHtml .= $rendermodal;
        return $renderHtml;
    }

    /**
     * Render form element
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null) {
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
            if (get_class($decorator) == 'EasyBib_Form_Decorator_BootstrapTag') {
                $content = $content . $this->renderExtras($this->getValue(),$this->getName());
            }
            $content = $decorator->render($content);
        }
        return $content;
    }

}
