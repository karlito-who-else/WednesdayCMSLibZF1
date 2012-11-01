<?php

//namespace Wednesday\Form\Element;

use \Zend_Form_Element,
    \Zend_Form_Element_Multi,
    Application\Entities\Resources,
    Wednesday\Renderers\ResourceHtml,
    Wednesday\Mapping\Form\EntityFormRenderer,
    \ZendX_JQuery_View_Helper_JQuery as JQueryViewHelper,
    \Zend_Controller_Front as Front;

/**
 * Description of GalleryPicker
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author jamesh
 */
class Wednesday_Form_Element_TabPicker extends Zend_Form_Element {
    const GALLERY = "Application\Entities\MediaGalleries";
    const RESOURCE = "Application\Entities\MediaResources";
    const WIDGETS = "Application\Entities\Widgets";
    const ENTITY_NAMESPACE  = "Application\Entities\\";
    const WEDMODEL_NAMESPACE  = "Wednesday\Models\\";

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
    public function init() {
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $widgetId = $this->getValue();
        if (empty($widgetId) === false) {
            $widgetInstance = $em->getRepository(self::WIDGETS)->findOneById($widgetId);
        }
        //$widgetInstance->title." ".
        $label = $this->getLabel();
        $this->setLabel($label);
    }

    protected function renderExtras($value, $attributes) {
//        $jqnc = JQueryViewHelper::getJQueryHandler();
        $bootstrap = Front::getInstance()->getParam("bootstrap");
        $em = $bootstrap->getContainer()->get('entity.manager');
        $config = $bootstrap->getContainer()->get('config');
        /*
          <label class="radio">
          <input type="radio" checked="" value="media" name="widgetItemType" class="manage-gallery" data-modal-type="multiple-galleries" href="#gallery-manager">
          Gallery, Images and Videos
          </label>
          <label class="radio">
          <input type="radio" value="pagedescription" name="widgetItemType" class="manage-description" data-modal-type="module-description" href="#description-manager">
          Description (Page)
          </label>
          <label class="radio">
          <input type="radio" value="form" name="widgetItemType" class="manage-form" data-modal-type="module-form">
          Forms
          </label>
          <label class="radio">
          <input type="radio" value="map" name="widgetItemType" class="manage-gmaps" data-modal-type="module-gmaps" href="#store_map_container">
          Map (Latitude, longitude and zoom levels)
          </label>
         * settings.application.
          <label class="radio">
          <input type="radio" checked="" value="media" name="widgetItemType" class="manage-gallery" data-modal-type="multiple-galleries" href="#gallery-manager">
          Gallery, Images and Videos
          </label>
         */
        $log = $bootstrap->getResource('Log');
//        $log->crit($config['settings']['application']['widget']['options']);
        $renderHtml = '';

        $elemid = $this->getId();
        $modalid = $this->getName() . "-modal";
        $widgetId = $this->getValue();
        $galleryItems = "";
        $log->debug($elemid . "; " . $modalid . "; " . $widgetId . "; ");

        $widgetConfigs = $config['settings']['application']['widget']['options'];

        if (empty($widgetId) === false) {
            $widgetInstance = $em->getRepository(self::WIDGETS)->findOneById($widgetId);
            $tabKeys = '';
            foreach ($widgetInstance->items as $item) {

                //$item->objectClass
                if((strpos($item->objectClass, self::ENTITY_NAMESPACE)===false)&&(strpos($item->objectClass, self::WEDMODEL_NAMESPACE)===false)) {
                    //Not an entity!!
                    $mappedItem = null;
                } else {
                    $mappedItem = $em->getRepository($item->objectClass)->find($item->content);
                }
                switch ($item->type) {
                    case 'media':
                    case 'galleries':
                    case 'logos':
                        $quickEditClass = 'manage-galleries';
                        $modalID = 'gallery-manager';
                        $url = $mappedItem->items->first()->resource->link;
                        break;
                    default:
                        $quickEditClass = false;
                        $url = '/assets/content/projects/default/preloader-oscar.jpg';
                        break;
                }
                $templateVars = array(  'url' => $url,
                                        'mappedItem' => $mappedItem,
                                        'tab' => $item,
                                        'type' => $item->type,
                                        'typeTitle' => $widgetConfigs[$item->type]['fronttype'],
                                        'widget' => $widgetInstance,
                                        'modalID'=>$widgetConfigs[$item->type]['modalid']
                                );

                if(isset ($widgetConfigs[$item->type]['icon']))
                {
                    $templateVars['icon'] = $widgetConfigs[$item->type]['icon'];
                }


                $galleryItems .= $this->getView()->partial('partials/items/tabbedmediagriditems.phtml', $templateVars);
                $tabbedKeyArray[] = 'tabId_' . $item->id;
            }
            $tabKeys = implode(',', $tabbedKeyArray);
            $tabKeys = rtrim($tabKeys, ',');
        }
        $typeTemplate = <<<TMPL
                    <label class="radio">
                        <input type="radio" checked="" value="[[key]]" name="widgetItemType" class="[[class]]" data-modal-type="[[backbone]]" href="#[[modalid]]"[[dataLimit]]>
                        [[title]]
                    </label>
TMPL;
        $widgetTypes = "";
        foreach ($widgetConfigs as $name => $options) {
            $tmpl = $typeTemplate;
            $tmpl = str_replace("[[key]]", $name, $tmpl);
            $tmpl = str_replace("[[title]]", $options['name'], $tmpl);
            $tmpl = str_replace("[[class]]", "manage-" . $name, $tmpl);
            $tmpl = str_replace("[[backbone]]", $options['backbone'], $tmpl);
            $tmpl = str_replace("[[modalid]]", $options['modalid'], $tmpl);
            $tmpl = str_replace("[[dataLimit]]", ' data-limit="'.$options['limit'].'"', $tmpl);
            $widgetTypes .= $tmpl . "\n";
        }
        $firstWidget = array_reverse($config['settings']['application']['widget']['options']);
//        $firstWidget = array_pop($firstWidget);
        foreach($firstWidget as $type=>$options)
        {
            $firstWidgetType = $type;
            $firstWidget = $options;
        }


        $renderHtml = <<<SCR
			<div class="widget" id="widget-{$widgetId}">

                <div class="control-group widget-types">
{$widgetTypes}
                </div>

                <p>
                    <button type="button" class="btn widget-item-toggle create {$firstWidget['backbone']}" data-toggle="modal" data-modal-type="" href="#{$firstWidget['modalid']}" data-kind="{$firstWidgetType}">Create Widget</button>
                </p>
                <p class="help-block">Upload assets into this gallery, or choose from the the assets that have already been uploaded.</p>
                <br />
                <h4>Widget Media</h4>
                <p class="help-block">Drag and drop the asset thumbnails to set the widget in their display order within the widgets container.</p>
                <br />
                <div class="well span9">
                    <ul class="thumbnails">
                        {$galleryItems}
                    </ul>
                </div>
            </div>
SCR;

        return $renderHtml;
    }

    protected function renderGallery($id) {

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
                $content = $content . $this->renderExtras($this->getValue(), $this->getAttribs());
            }
            $content = $decorator->render($content);
        }
        return $content;
    }

}
