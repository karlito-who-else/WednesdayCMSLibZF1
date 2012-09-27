<?php
namespace Wednesday\Mapping\Form;

use Doctrine\Common\Collections\ArrayCollection,
    ReflectionException,
    Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\ORM\Mapping\Column,
    Wednesday\Mapping\Annotation\Form,
    \Zend_Form_SubForm,
    \Zend_Form,
    \Wednesday_Form_Form as WednesdayForm,
    \Zend_Form_Element,
    \Wednesday_Form_Element_ResourcePicker,
    \Wednesday_Form_Element_ModulePicker,
    \Wednesday_Form_Element_EntityPicker,
    \Wednesday\Form\Element\Groups\StandardFooter as ActionsGroup,
    \Zend_Controller_Front as Front;


/**
 * Description of EntityFormRenderer
 * Wednesday London
 * Copyright 2011
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 */
class EntityFormRenderer extends WednesdayForm {
    const SYSTEM_NAMESPACE = "Wednesday\Models\\";
    const ENTITY_NAMESPACE = "Application\Entities\\";
    const _SKIP_PROPERTIES = 'created,updated,datatype';
    const _RESTRICTED_PROPERTIES = 'id';

    public $entityName;

    protected $entityId;

    protected $plugins;

    public $mapping;

    protected $log;

    /**
     *
     * @param string $entityName
     * @param array $skip
     * @param array $restrict
     */
    public function __construct($entityName, $skip = false, $restrict = false) {
        $this->entityName = $entityName;
    	#Get Logger
        $front = Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        $this->log = $bootstrap->getResource('Log');
        $this->plugins = array();
        $this->mapping = new MapperAbstract($this->entityName, $skip, $restrict);
        $this->log->info(get_class($this).'::EntityFormRenderer('.$this->entityName.')');
    }

    /**
     *
     * @param array $params
     * @return array
     */
    public function getReferences($params) {
        $this->log->info(get_class($this).'::getReferences('.$this->entityName.')');
//        $this->log->info($params);
        $em = Front::getInstance()->getParam("bootstrap")->getContainer()->get('entity.manager');
        $cmf = $em->getMetadataFactory();
        $this->log->info("Error?".$this->entityName);
        $metadata = $cmf->getMetadataFor($this->entityName);
//    	$this->log->info("Error?".$this->entityName);
        $mappings = $this->mapping->getElements($this->entityName);
//        $this->log->info("Error?".$this->entityName);
//        $this->log->info($mappings);
//        $this->log->info($metadata->associationMappings);
        foreach($mappings as $elementName => $elementDetails) {
            $this->log->info($elementName.'::'.$elementDetails['associated']." - ".$params[$elementName]);
            if((isset($elementDetails['associated'])===true)&&($elementDetails['associated']==true)) {
                if((isset($params[$elementName])===true)&&(empty($params[$elementName])===false)) {
                  $targetEntity = $metadata->associationMappings[$elementName]['targetEntity'];
                  $this->log->info($elementName.'::'.$elementDetails['associated']." - ".$params[$elementName]);//".strpos($params[$elementName], ',')." =
                  if(is_array($params[$elementName])===true) {
                      $this->log->info("Array of Refs".$params[$elementName]);
                      $entRefs = new ArrayCollection();
                      foreach($params[$elementName] as $entity) {
                        if(isset($entity['id'])===false){
                            $entityReference = new $targetEntity();
                            $mapr = new EntityFormRenderer($targetEntity);
                            $entityrefd = $mapr->getReferences($entity);
                        } else {
                            $entityReference = $em->getRepository($targetEntity)->find($entity['id']);
                            $entityrefd = $entity;
                        }
                        #Nest references
                        if(isset($entityReference)===true) {
                            $entityReference->updateAction($entityrefd);
                            $em->persist($entityReference);
                            $entRefs->add($entityReference);
                        }
                      }
                      $params[$elementName] = $entRefs;
                  } else if(strpos($params[$elementName], ',')===false) {
                      $this->log->info("Single Ref {$targetEntity} {$this->entityName} ['".$params[$elementName]."']");
                      if(is_numeric($params[$elementName])) {
                          $refLink = 'findOneById';//(is_numeric($params[$elementName]))?'findOneById':'findOneByLink';
                          $entityReference = $em->getRepository($targetEntity)->$refLink($params[$elementName]);
                          if($metadata->associationMappings[$elementName]['type'] > 2) {
                              $entRefs = new ArrayCollection();
                              $entRefs->add($entityReference);
                              $params[$elementName] = $entRefs;
                          } else {
                              $params[$elementName] = $entityReference;
                          }
                      }
                  } else {
                      $this->log->info("Array of Refs {$targetEntity} {$this->entityName} [".$params[$elementName]."]");
                      $ids = explode(',',$params[$elementName]);
                      $entRefs = new ArrayCollection();
                      $newOrder = 0;
                      foreach($ids as $id){
                          if(is_numeric($id)) {
                              $ref = $em->getRepository($targetEntity)->findOneById($id);
                              $this->log->info("Refs {$targetEntity} {$this->entityName} - {$id} {$ref->sortorder}");
                              if(is_numeric($ref->sortorder)){
                                  $newOrder++;
//                                  $this->log->info("Sortorder {$newOrder}");
                                  $ref->sortorder = $newOrder;
                                  $em->persist($ref);
                              }
                              $entRefs->add($ref);
                          }
                      }
                      $params[$elementName] = $entRefs;
                  }
                  $this->log->info($elementDetails['associated']." - ".$elementName." = ".count($params[$elementName]));
                }
            }
    	}
        $this->log->info("Error?".$this->entityName);
        return $params;
    }

    /**
     *
     * @param integer $id
     * @param string $locale
     * @param string $submit
     * @return Zend_Form_SubForm
     */
    public function getForm($id = false, $locale = false, $submit = "Submit", $formname = 'entity-form') {
        $this->entityId=$id;
        $formattedEntityName = str_replace(array('Wednesday\Models\\','Application\Entities\\'), '', $this->entityName);
        \ZendX_JQuery::enableForm($this);
        $this->setLegend($formattedEntityName);
        $this->setName($formname);
//        $this->setDecorators(array(
//            'FormElements',
//            array('HtmlTag', array('tag' => 'div', 'class' => 'form-container box')),
//            'Form'
//        ));
//        $this->removeDecorator('DtDdWrapper');
        //$this->setSubFormDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'box')),'Fieldset'));
        $mappings = $this->mapping->getElements($this->entityName);
        foreach($mappings as $elementName => $elementDetails){
            if($elementDetails['type']!='eachsubform') {
                $this->addElement($this->renderElement($elementName, $elementDetails));
            } else {
                if(is_numeric($id)&&($id != false)) {
                    $linkedEnt = $this->getEntityData($id,$locale);
                    if(count($linkedEnt[$elementName])<=0) {
//                        $this->log->info($linkedEnt);
                        $elem = new \Wednesday_Form_Element_EntityPicker(array(
                                'name' => strtolower($elementDetails['options']['targetEntity']),
                                'class' => 'btn',
                                'entityId' => $this->entityId,
                                'entityName' => $formattedEntityName,
                                'entityVariable' => $elementName,
                                'data-controls-modal'=>strtolower($elementDetails['options']['targetEntity']).'-modal',
                                'data-backdrop'=>'static',
                                'required' => false,
                            ));
                        $this->addElement($elem);
                        #Add script to insert new field for form to use...
                    } else {
//                        $subforms = array();
                        $i = 0;
                        $groupForm = new \Zend_Form_SubForm();
                        $groupForm->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'box'))));
                        foreach($linkedEnt[$elementName] as $ent) {
//                            if(empty($elementDetails['options']['targetEntity'])===false) {
                                $submapper = new EntityFormRenderer('Application\Entities\\'.$elementDetails['options']['targetEntity'],
                                    array('title','longtitle','description','categories','tags','metadata','templates','variablescontent','contentvariable','page')
                                );
                                $linkedForm = $submapper->getForm($ent,false,false);
                                $linkedForm->setLegend($elementDetails['options']['targetEntity']);
                                $linkedForm->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div', 'class' => 'box')),'Fieldset'));
                                $groupForm->addSubForm($linkedForm, $i);
                                $i++;
//                            }
                        }
                        $this->addSubForm($groupForm, $elementName);
                    }
                }
            }
        }

        if($submit!=false){
//            $submitbut = new \Zend_Form_Element_Submit(array(
//                'name'     => 'submit',
//                'ignore'   => true,
//                'class'    => 'btn',
//                'label'    => $submit
//            ));
//            $this->addElement($submitbut);
            $actions = new ActionsGroup();
            $this->addSubForm($actions, 'actions');
        }

        if(is_numeric($id)&&($id != false)) {
            $data = $this->getEntityData($id, $locale);
//            $this->log->info($data);
            if(isset($data)===true) {
                $this->setDefaults($data);
            }
        }
        return $this;
    }

    /**
     *
     * @param string $name
     * @param array $type
     * @return elementClass
     */
    public function renderElement($name, $type) {
        $element = false;
        $type['setOptions'] = false;
        $elementClass = 'Zend_Form_Element_Text';
        $decoratorType = 'text';
        $label = $name;
        $elementOpts = array();
        switch($type['type']) {
            case 'textarea':
                $elementClass = 'Zend_Form_Element_Textarea';
                $elementOpts = array('cols'=>80,'rows'=>8);
                $label = $name;
                break;
            case 'rich':
                $elementClass = 'Zend_Form_Element_Textarea';
                $elementOpts = array('cols'=>80,'rows'=>16);
                $decoratorType = 'jquery_ckeditor';
                $label = $name;
                break;
            case 'select':
                $elementClass = 'Zend_Form_Element_Select';
                $label = $name;
                $type['setOptions'] = true;
                break;
            case 'checkbox':
                $elementClass = 'Zend_Form_Element_Checkbox';
                $label = $name;
                break;
            case 'radioyesno':
                $elementClass = 'Zend_Form_Element_Radio';
                $label = $name;
                break;
            case 'colourpicker':
                $decoratorType = 'colourpicker';
                $elementClass = 'Zend_Form_Element_Text';
//                $elementOpts = array('disabled'=>'disabled');
                break;
            case 'modulepicker':
                $decoratorType = 'modulepicker';
                $elementClass = 'Zend_Form_Element_Select';
                $elementClass = 'Wednesday_Form_Element_ModulePicker';
                $elementOpts = array('entityId'=> $this->entityId);
//                $type['setOptions'] = true;
                break;
//            case 'categorypicker':
//                $decoratorType = 'categorypicker';
//                $elementClass = 'Wednesday_Form_Element_CategoryPicker';
//                $elementOpts = array('entityId'=> $this->entityId);
//                //$elementOpts = array('values' => $type['options']);
////                $type['setOptions'] = true;
//                break;
            case 'resourcepicker':
                $decoratorType = 'resourcepicker';
                $elementClass = 'Zend_Form_Element_Hidden';
                $elementClass = 'Wednesday_Form_Element_ResourcePicker';
//                $type['setOptions'] = true;
                break;
            case 'datepicker':
                $decoratorType = 'datepicker';
                $elementClass = 'ZendX_JQuery_Form_Element_DatePicker';
                $elementOpts = array('jQueryParams'=>array('defaultDate' => '2007/10/10'));
                break;
            case 'hidden':
                $elementClass = 'Zend_Form_Element_Hidden';
                $label = '';
                break;
            case 'categorypicker':            
            case 'eachsubform':
//                die(print_r($this, true));
//                die($this->entityName);
//                $elementClass = 'Zend_SubForm';
//                $label = '';
                break;
        }
        $this->appendPlugin($type['type'], strtolower($name));
        $elementOptions = array_merge(array(
            'name' => strtolower($name),
            'class' => 'input input-xxlarge'.$decoratorType,
            'required' => $type['required'],
            ), $elementOpts);

        $element = new $elementClass( $elementOptions );
        $element->setAttrib('entityId', $this->entityId);
        $tmp = ($label != '')?$element->setLabel($label):false;
        $tmp = ($type['setOptions'])?$element->addMultiOptions($type['options']):false;
        return $element;
    }

    /**
     *
     * @param string $plugin
     * @return void
     */
    protected function appendPlugin($plugin, $name) {
        #Don't add scripts multiple times.
        if(in_array($plugin, $this->plugins)&&($plugin <> 'resourcepicker')) {
            return;
        }
        $id = 'entityform-'.$name;
        $labelid = $id."-label";
        $elemid = $id."-element";
        $jqNoConflict = \ZendX_JQuery_View_Helper_JQuery::getJQueryHandler();
        switch($plugin) {
            case 'rich':
                $inlineScript = <<<SOE
        /* <![CDATA[ */
            {$jqNoConflict}(document).ready(function() {
                var config = {
                    toolbar:
                    [
                        ['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink'],
                        ['UIColor']
                    ]
                };
                // Initialize the editor.
                // Callback function can be passed and executed after full instance creation.
                {$jqNoConflict}('.jquery_ckeditor').ckeditor(config);
            });
        /* ]]> */
SOE;
                break;
            case 'datepicker':
                $inlineScript = <<<SOE
        /* <![CDATA[ */
            {$jqNoConflict}(document).ready(function() {
                //Add datepicker code.
            });
        /* ]]> */
SOE;
                break;
            case 'colourpicker':
                #TODO Remove hardcoded values from this script block.
                $this->getView()->headLink()->appendStylesheet('/library/js/farbtastic/v1.2/farbtastic.css', 'screen');
                $inlineScript = <<<SOA
        /* <![CDATA[ */
            {$jqNoConflict}(document).ready(function() {
                {$jqNoConflict}('#{$labelid}').farbtastic('.colourpicker');
            });
        /* ]]> */
SOA;
                break;
            case 'modulepicker':
                #This case is left empty on purpose, for now
                $inlineScript = "";
                break;
            case 'resourcepicker':
                #$labelid
                if(in_array($plugin, $this->plugins)===false){
                    $theme = Front::getInstance()->getParam("bootstrap")->getContainer()->get('theme');
                    $themePath = $theme->theme->view->path;//$this->view->placeholder('themepath');
                    $this->getView()->inlineScript()->appendFile($themePath.'/js/fileuploader.js', 'text/javascript');
                    $this->getView()->headLink()->appendStylesheet($themePath.'/css/fileuploader.css', 'screen');
                    $this->getView()->inlineScript()->appendFile($themePath.'/js/resourcebrowser.js', 'text/javascript');
                    $this->getView()->inlineScript()->appendFile('/library/js/jquery/plugins/ui/v1.8.15/jquery-ui-1.8.15.min.js', 'text/javascript');
                }
                $inlineScript = <<<SOA
        /* <![CDATA[ */
            {$jqNoConflict}(document).ready(function() {
                {$jqNoConflict}('#{$labelid} label').bind('click', function(){
                    console.log({$jqNoConflict}(this).attr('for'));
                    if(jQuery('#media-browser').length > 0) {
                        if(ResourceBrowserWindow.isVisible()) {
                            ResourceBrowserWindow.hide();
                        } else {
                            ResourceBrowserWindow.show();
                        }
                        //clearListeners
                        ResourceBrowserWindow.clearListeners();
                        ResourceBrowserWindow.addListener('hide',
                           function(evt) {
                               logIt('evt::hide');
                               var sel = Ext.getCmp('selection-view');
                               var str = sel.getStore();
                               logIt(sel);
                               logIt(str.count());
                               str.sync();
                               targetElementStore = new Array();
                               {$jqNoConflict}('#{$elemid} ul#{$elemid}-selecable').empty();
                               {$jqNoConflict}('#{$elemid}').remove('ul#{$elemid}-selecable');
                               var list = {$jqNoConflict}('<ul id="{$elemid}-selecable" />');
                               for(var i=0;i<str.count();i++) {
                                   var rec = str.getAt(i);
                                   var node = {$jqNoConflict}('<img data-id="'+rec.data.id+'" src="/assets'+rec.data.link+'" />');
                                   {$jqNoConflict}(list).append(node);
                                   {$jqNoConflict}(node).wrap('<li class="resource-select" />');
                                   targetElementStore.push(rec.data.id);
                               }
                               var removelink = {$jqNoConflict}('<a id="{$elemid}-delete" class="remove-items" href="#remove">Remove Selected</a>');
                               {$jqNoConflict}('#{$elemid}').append(removelink);
                               {$jqNoConflict}('#{$elemid}').append(list);
                               {$jqNoConflict}('#{$elemid}-selecable').selectable();
                               {$jqNoConflict}('#{$elemid} input').val(targetElementStore);
                               logIt(targetElementStore);
                           },
                           ResourceBrowserWindow
                        );
                        ResourceBrowserWindow.addListener('show',
                           function(evt) {
                               logIt('evt::hide');
                               var sel = Ext.getCmp('selection-view');
                               var str = sel.getStore();
                               str.removeAll();
                               var lst = Ext.getCmp('list-view');
                               var str2 = sel.getStore();
                               str2.sync();
                               logIt(targetElementStore);
                           },
                           ResourceBrowserWindow
                        );
                    }
                });
                {$jqNoConflict}('a.remove-items').bind('click',function(e){
                    e.preventDefault();
                    var elem = {$jqNoConflict}(this).attr('id');
                    var targ = elem.replace('-delete','');
                    var targid = '';
                    console.log(targ);
                    console.log(targid);
                    {$jqNoConflict}('#'+targ+'-element-selecable li.ui-selected').remove();
                    {$jqNoConflict}('#'+targ+'-element-selecable li.resource-select > img').each(function(){
                        targid += ''+{$jqNoConflict}(this).attr('data-id')+',';
                    });
                    {$jqNoConflict}('#'+targ+'').val(targid);
                });
            });
        /* ]]> */
SOA;
                break;
            default:
                #No Code to add.
                return;
        }
        $this->getView()->inlineScript()->appendScript($inlineScript, 'text/javascript');
        array_push($this->plugins, $plugin);
    }

    /**
     *
     * @param integer $id
     * @param string $locale
     * @return array
     */
    protected function getEntityData($id, $locale = false) {
        $em = Front::getInstance()->getParam("bootstrap")->getContainer()->get('entity.manager');
        $ent = $em->getRepository($this->entityName)->findOneById($id);
//        $ent = $em->getPartialReference($this->entityName,$id);
        $this->log->info(get_class($ent)."::".$this->entityName.": ".$ent->id.", ".$id);
        if($locale != false){
            $ent->setTranslatableLocale($locale);
            $em->refresh($ent);
        }
        if(!isset($ent)) {
            return array();
        }
        $this->log->info(get_class($this).'::getEntityData('.$this->entityName.':'.$ent->id.')');
        return $ent->toArray(true);
    }
    
    protected function getEntityNamespace($entity) {
        switch ($entity) {
            case 'Categories':
            case 'Tags':
            case 'Metadata':
            case 'Settings':
                $entityClass = self::SYSTEM_NAMESPACE . $this->entityName;
                break;
            default:
                $entityClass = self::ENTITY_NAMESPACE . $this->entityName;
                break;
        }
        return $entityClass;
    }
}
