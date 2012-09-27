<?php
//namespace Wednesday\Form\Element;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    Application\Entities\Resources,
    Wednesday\Renderers\ResourceHtml,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Controller_Front as Front;

/**
 * Description of ResourcePicker
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_ModulePicker extends Zend_Form_Element {

    /**
     * Use formHidden view helper by default
     * @var string
     */
    public $helper = 'formHidden';

    /**
     * Initialize object; used by extending classes
     * @return void
     */
    public function init()
    {
//        die($this->getAttrib('entityId'));
    }

    /**
     *
     * @param type $value
     * @return string
     */
    protected function renderExtras($value) {
        if(empty($value)) {
            return "";
        }
//
//        if(!isset ($this->getAttrib('entityId'))|| $this->getAttrib('entityId')==0)
//        {
//            return "";
//        }
        $jqNoConflict = JQueryViewHelper::getJQueryHandler();
        $elemid = $this->getId();
        $renderHtml = '<a id="'.$elemid.'-add" class="add-items" href="#new-module">Add New Module</a>';


        $renderHtml .= '<ul id="'.$elemid.'-element-selecable">';

        if(strpos($value, ',')===false) {
            #Render One.
            $renderHtml .= '<li class="module-select">'.$value.'</li>';
        } else {
            #Render lots.
            $ids = explode(',',$value);
            foreach($ids as $id) {
                $renderHtml .= '<li class="module-select ui-state-default" data-id="'.$id.'" ><p><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>'.$this->renderModule($id).'</p></li>';
            }
        }
        $renderHtml .= '</ul>';
//        {$jqNoConflict}('#{$elemid}-element-selecable').disableSelection();
        $scr = <<<SCR
        /* <![CDATA[ */
            {$jqNoConflict}(document).ready(function() {
                {$jqNoConflict}('#{$elemid}-element-selecable').sortable();
                {$jqNoConflict}('#{$elemid}-element-selecable').bind( "sortupdate", function(event, ui) {
                    var targid = '';
                    {$jqNoConflict}('#{$elemid}-element-selecable li').each(function(){
                        targid += ''+{$jqNoConflict}(this).attr('data-id')+',';
                    });
                    {$jqNoConflict}('#{$elemid}').val(targid);
                });
//                {$jqNoConflict}('#{$elemid}-element-selecable li p small a.edit').bind('click', function(e){
//                    e.preventDefault();
//                    var url = {$jqNoConflict}(this).attr('href');
//                    var prnt = {$jqNoConflict}(this).parent().parent();
//                    {$jqNoConflict}.ajax({
//                        url: url,
//                        success: function(d) {
//                            prnt.append(d);
//                            console.log(prnt);//.attr('data-id');
//                        }
//                    });
//                });
            });
        /* ]]> */
SCR;
        $this->getView()->inlineScript()->appendScript($scr, 'text/javascript');
        #Add jQ UI css?
        $styles = "#".$elemid."-element-selecable {
            position: relative;
            display: block;
        }
#".$elemid."-element-selecable li span {
            float: left;
        }
#".$elemid."-element-selecable li p {
            font-weight: normal;
        }
#".$elemid."-element-selecable li p small {
            float: right;
        }
#".$elemid."-element-selecable li {
            margin: 5px 0;
            padding: 5px;
            max-width: 470px;
        }";
        $this->getView()->headStyle()->appendStyle($styles);
//        $this->getView()->headLink()->appendStylesheet('/library/css/jquery/ui/v1.8.16/smoothness/jquery-ui-1.8.16.custom.css', 'screen');
        return $renderHtml;
    }

    /**
     *
     * @param type $id
     * @return type
     */
    protected function renderModule($id) {
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $res = $em->getRepository("Application\Entities\Modules")->findOneById($id);
        $projectid = $res->project->id;
        $thmbs = "";
        if(in_array($res->moduletype,array('photos','lifestyle','floor-plans','logo'))/*isset($res->images)*/) {
            foreach($res->images as $image){
               $thmbs .= " ".$this->getView()->Resource($image);
            }
        }
        if($res->moduletype='floor-plans')
            $moduletype = 'floorplan';

        else
            $moduletype = $res->moduletype;

        $thmbs = str_replace('alt=', 'height="32" alt=', $thmbs);
        $editLink = "<a class='edit' href='/admin/modules/update/".$res->id."?moduleinput=".$moduletype."&project=".$projectid."'>Edit</a>";
        $removeLink = "<a class='remove' href='/admin/modules/delete/".$res->id."?moduleinput=".$moduletype."&project=".$projectid."'>Remove</a>";

        return " {$res->moduletype} : {$res->title} {$thmbs} <small>{$editLink} {$removeLink}</small>";
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
//            if(get_class($decorator) == 'Zend_Form_Decorator_ViewHelper') {
            if(get_class($decorator) == 'EasyBib_Form_Decorator_BootstrapTag') {
                $content = $content.$this->renderExtras($this->getValue());
            }
            $content = $decorator->render($content);
        }
        return $content;
    }
}