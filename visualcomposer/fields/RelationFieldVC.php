<?php

namespace Rootpress\visualcomposer\fields;

/** 
 * Relation Field for Visual Composer
 */
class RelationFieldVC {

    //Name of the field type
    public static $fieldName = 'Relation Field';
    //Name use for declare the field which is the name of the shortcode
    public static $shortcodeName = 'vc_relation_field';
    //Empty value
    public static $emptyValue = "Aucune";
    //Query params
    public static $queryParams = [
        'posts_per_page'   => -1,
        'post_status'      => 'publish',
        'post_type'        => 'any'
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

        if(static::$useOptionGroups && !empty(static::$groupingTaxonomy)){
            //Possible grouped values
            static::printGroupedField($uniqid, $paramNameEsc, $valueEsc, $classString);
        } else {
            static::printSimpleField($uniqid, $paramNameEsc, $valueEsc, $classString);
        }

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
                '<select name="' . $paramNameEsc . '" class="' . $classString . '">';
                        echo '<option value="">' . static::$emptyValue . '</option>';

                    foreach ($items as $item) {
                        $id = (isset($item->ID)) ? $item->ID : $item->term_id;
                        $label = (isset($item->post_title)) ? $item->post_title : $item->name;
                        $selected = ($id == $valueEsc) ? 'selected' : '';
                        echo '<option value="' . $id . '" ' . $selected . '>' . $label . '</option>';
                    }

        echo    '</select>' . 
              '</div>';

        echo '<script>jQuery(".' . $uniqid . ' select").select2();</script>';
    }

    /**
     * Function use in case of multiple values
     */
    public static function printGroupedField($uniqid, $paramNameEsc, $valueEsc, $classString){
        $terms = get_terms(static::$groupingTaxonomy);
        $posts = get_posts(static::$queryParams);
        $posts = Hydratator::hydrates($posts, 1, ['fields' => [], 'taxonomies' => [static::$groupingTaxonomy]]);

        echo '<div class="relation_field_block ' . $uniqid . '">' .
                '<select name="' . $paramNameEsc . '" class="' . $classString . '">';

                foreach ($terms as $term) {
                    echo "<optgroup label='".$term->name."'>";
                    foreach ($posts as $post) {
                        if($term->name == $post->people_group[0]->name){
                            $selected = ($post->ID == $valueEsc) ? 'selected' : '';
                            echo '<option value="' . $post->ID . '" ' . $selected . '>' . $post->post_title . '</option>';
                        }
                    }
                    echo "</optgroup>";
                }

        echo    '</select>' .
            '</div>';
        echo '<script>jQuery(".' . $uniqid . ' select").select2();</script>';
    }

    public static function getValues() {
        return get_posts(static::$queryParams);
    }
}