<?php

namespace Rootpress\visualcomposer\widgets;

/** 
 * Abstract class for create a visual composer widget
 */
class WidgetVC_Abstract extends \WPBakeryShortCode {

	//Name of the widget display in backoffice
    public static $widgetName = "Widget Name";
    //Name use for declare the widget as vc widget (must be unique)
    public static $base = "widget_name";
    //Class add to the html form
    public static $class = "lame_class_name";
    //Icon use for this widget
    public static $icon = "icon-wpb-vc_pr_emploi_block";
    //Category in which this widget will be put when choosing widget to add in backoffice
    public static $category = 'Default Category';
    //Widget Description
    public static $description = 'Widget Description';
    //Shall we display settings on widget creation
    public static $displaySettings = true;

    /**
     * Default constructor
     */
    public function __construct( $settings ) {
		parent::__construct( $settings );
	}

        /**
     * Allow to declare the widget as a visual composer widget which allow to select it inside visual composer
     * You can edit the parameters by editing the attributes of your class
     */
    public static function declareAsVisualComposerWidget() {
        $params = [];
        $params = apply_filters('set_params_widget_vc_' . static::$base, $params);

        vc_map([
            'base'        => static::$base,
            'name'        => static::$widgetName,
            'class'       => static::$class,
            'icon'        => static::$icon,
            'params'      => $params,
            'description' => static::$description,
            'category'    => static::$category,
            'show_settings_on_create' => static::$displaySettings,
        ]);
    }

    /**
     * Allow to get the widget view rendered
     * @param $view string relative path to the view twig file
     * @param $context mixed timber context object
     */
    protected function renderView($view, $context) {
        ob_start();
        Timber::render($view, $context);
        return ob_get_clean();
    }

    /**
     * Decode a base64 string with accent
     * @param $string string to decode
     */
    protected function decode($string) {
        return utf8_decode(utf8_encode(base64_decode($string)));
    }

    /**
     * Create not set values to default values
     * @param $atts array of vc widget attributes
     * @param $attributes array of attributes which $atts need to contains with field_name => default_value for each field
     */
    protected function handleDefaultValues($atts, $attributes) {
        if(!is_array($atts)) {
            $atts = [];
        }
        foreach ($attributes as $field_name => $defaultValue) {
            $atts[$field_name] = (isset($atts[$field_name])) ? $atts[$field_name] : $defaultValue;
        }
        return $atts;
    }

    /*****************************
    | Work In Progress from here |
    ******************************/

	/**
	 * How the widget is display inside admin visual composer page
	 * DO NOT edit this function in your child class. Prefer edit the contentAdminCustom method.
	 */
    /*public function contentAdmin($atts, $content) {

    	//Shortcode admin content
    	$output = $this->contentAdminCustom($atts, $content);

    	//Add controls
    	$elementControls = $this->getElementHolder('');
    	$finalOutput = str_ireplace('%wpb_element_content%', $output, $elementControls);
        
        //Return view
        return $finalOutput;
    }*/

    /**
     * How the widget is display inside admin visual composer page
     * You need to modify this method in your child class if you want to customize your widget display
     */
    /*public function contentAdminCustom($atts, $content) {
    	global $post;
    	ob_start();
    	var_dump($this->getCurrentWidgetValues());
    	return ob_get_clean();
    }*/

    /**
     * How the widget is render inside the frontend
     * You need to modify this method in your child class if you want to customize your widget display
     */
    /*public function content($atts, $content = null) {

    	/*var_dump($atts);
    	var_dump($content);
    	var_dump($base);
    }*/

    /*protected function getCurrentWidgetValues() {
    	global $post;

    	global $test;
    	var_dump($test);
    	$test++;


    	/*var_dump($GLOBALS);

    	if(!isset($GLOBALS['indexCurrentVCWidget'])) {
    		$GLOBALS['indexCurrentVCWidget'] = [];
    	}
    	if(!isset($GLOBALS['indexCurrentVCWidget'][static::$base])) {
    		$GLOBALS['indexCurrentVCWidget'][static::$base] = 0;
    	}*/

    	/*$matches = [];
    	preg_match_all('/\[' . static::$base . '[^\]]*widget_id="(\d*)"/', $post->post_content, $matches); 
    	//var_dump($GLOBALS['indexCurrentVCWidget'][static::$base]);
    	if(!empty($matches[1])) {
    		$widgetId = $matches[1][$GLOBALS['indexCurrentVCWidget'][static::$base]];
    		$GLOBALS['indexCurrentVCWidget'][static::$base]++;
    		$WidgetRepository = new WidgetRepository();
    		return $WidgetRepository->findOne($widgetId);
    	}
    	return null; 
    }*/


}