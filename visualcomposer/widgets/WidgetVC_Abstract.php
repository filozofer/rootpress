<?php

namespace Rootpress\visualcomposer\widgets;

use Timber;

/** 
 * Abstract class for create a visual composer widget
 */
class WidgetVC_Abstract {

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
     * Handle output of widget in front
     * Empty by default, override inside child class
     */
    public static function content($atts, $content = null, $base = '') {
        return '';
    }

    /**
     * Allow to declare the widget as a visual composer widget which allow to select it inside visual composer
     * You can edit the parameters by editing the attributes of your class
     */
    public static function declareAsVisualComposerWidget() {
        $params = [];
        $params = apply_filters('set_params_widget_vc_' . static::$base, $params);

        add_shortcode(static::$base, [__CLASS__, 'content']);
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
    protected static function renderView($view, $context) {
        ob_start();
        Timber::render($view, $context);
        return ob_get_clean();
    }

    /**
     * Decode a base64 string with accent
     * @param $string string to decode
     */
    protected static function decode($string) {
        return utf8_decode(utf8_encode(base64_decode($string)));
    }

    /**
     * Create not set values to default values
     * @param $atts array of vc widget attributes
     * @param $attributes array of attributes which $atts need to contains with field_name => default_value for each field
     */
    protected static function handleDefaultValues($atts, $attributes) {
        if(!is_array($atts)) {
            $atts = [];
        }
        foreach ($attributes as $field_name => $defaultValue) {
            $atts[$field_name] = (isset($atts[$field_name])) ? $atts[$field_name] : $defaultValue;
        }
        return $atts;
    }

}