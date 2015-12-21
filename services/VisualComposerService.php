<?php

namespace Rootpress\services;

/**
 * VisualComposerService class help you to create custom web service on your wordpress
 */
class VisualComposerService {

	/**
	 * Start the service
	 */
    public static function startService() {
    	echo('WIN VisualComposerService');
    	//TODO
    }

    /**
     * Allow to declare visual composer widgets in the project
     * @param $widgetDeclaration array of string, each string is the name of an entity which represent a vc widget
     */
    public static function declareVCWidgets($widgetDeclaration)
    {
        //Declare each widget as shortcode and as vc widget
        foreach ($widgetDeclaration as $widget) {
            $widget::declareAsVCWidget();
        }
    }

    /**
     * Allow to declare custom visual composer field for widgets
     */
    public static function declareVCFields($vcFieldsDeclaration)
    {
        //Declare each custom VC fields
        foreach ($vcFieldsDeclaration as $vcField) {
            add_shortcode_param($vcField::$shortcodeName, array($vcField, 'fieldForm'));
        }
    }

}
