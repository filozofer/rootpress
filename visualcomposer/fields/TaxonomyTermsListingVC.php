<?php

namespace Rootpress\visualcomposer\fields;

/** 
 * Relation Field for Visual Composer
 */
class TaxonomyTermsListingVC {

    //Name of the field type
    public static $fieldName = "Articles d'une catégorie";
    //Name use for declare the field which is the name of the shortcode
    public static $shortcodeName = 'vc_taxonomy_term_listing';
    //Empty value
    public static $emptyValue = "Aucune";
    //Query params
    public static $defaultCategory = "category";
    public static $queryParams = [
        'orderby'         => 'name',
        'order'           => 'ASC',
        'hide_empty'      => 1,
    ];

    //Shall we use options groups or not
    public static $useOptionGroups = false;
    //Tell us which taxonomy we are using for grouping
    public static $groupingTaxonomy = '';

    /**
     * Field 
     */
    public static function fieldForm($settings, $value) {
        ob_start();

        //Input params
        $paramNameEsc = esc_attr($settings['param_name']);
        $valueEsc =  esc_attr($value);
        $classString = 'wpb_vc_param_value wpb-relationinput ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_field';
        $uniqid = uniqid();

        static::printSimpleField($uniqid, $paramNameEsc, $valueEsc, $classString);

        return ob_get_clean();
    }

    /**
     * Function use in case of single value
     */
    public static function printSimpleField($uniqid, $paramNameEsc, $valueEsc, $classString){
        //Possible values
        $items = static::getValues();

        //Print the field
        echo '<div class="relation_field_block ' . $uniqid . '">' .
                '<select name="' . $paramNameEsc . '" class="' . $classString . '" style="width: 100%;">';
                        echo '<option value="">' . static::$emptyValue . '</option>';

                    foreach ($items as $item) {
                        $id = (isset($item->ID)) ? $item->ID : $item->term_id;
                        $label = (isset($item->post_title)) ? $item->post_title : $item->name;
                        $selected = ($id == $valueEsc) ? 'selected' : '';
                        if(!empty(trim($label))) {
                            echo '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
                        }
                    }

        echo    '</select>' . 
              '</div>';

        echo '<script>jQuery(".' . $uniqid . ' select").select2();</script>';
        echo '<style>.select2-drop.select2-drop-active { z-index: 100000; }</style>';
    }

    public static function getValues() {
        return get_terms(static::$defaultCategory, static::$queryParams);
        //return get_posts(static::$queryParams);
    }
}