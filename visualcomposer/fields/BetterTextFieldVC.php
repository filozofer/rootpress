<?php

namespace Rootpress\visualcomposer\fields;

/**
 * Better Text Field for Visual Composer
 */
class BetterTextFieldVC {

    //Name of the field type
    public static $fieldName = 'Better Text Field';
    //Name use for declare the field which is the name of the shortcode
    public static $shortcodeName = 'vc_better_textfield';
    //Default settings
    public static $defaultSettings = [
    ];

    /**
     * Field
     */
    public static function fieldForm($settings, $value) {
        //Default settings
        $settings = array_replace_recursive(self::$defaultSettings, $settings);
        ob_start();

        //Input params
        $paramNameEsc = esc_attr($settings['param_name']);
        $classString = 'wpb_vc_param_value ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_field';
        $uniqid = uniqid();

        // Custom attrs
        $attrs = '';
        if(isset($settings['custom_attrs'])) {
            foreach($settings['custom_attrs'] as $attrKey => $attrValue) {
                $attrs .= ' ' . esc_attr($attrKey) . '="' .  esc_attr($attrValue) . '"';
            }
        }

        //Form input
        echo '<div class="vc_better_textfield ' . $uniqid . '">' .
            '<input id="' . $uniqid . '" name="' . $paramNameEsc . '" class="' . $classString . '" value="' . $value . '" ' . $attrs . '>' .
            '</div>';

        return ob_get_clean();
    }

}