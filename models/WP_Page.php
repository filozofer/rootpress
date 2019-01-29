<?php

namespace Rootpress\models;

/**
 * WP_Page class to manipulate page
 */
class WP_Page extends RootpressModel {

    /**
     * Linked this class to the native post type "page"
     * @var string
     */
    public static $linked_post_type = 'page';

    /**
     * Allow to be find by Rootpress as a Custom Post Type class
     */
    public static function customTypeDeclaration() {
        // Already declare by Wordpress Core
    }

    /**
     * Constructor for this model
     * Override this function in your child class to do post treatement after hydratation
     */
    public function construct() {

        // Get all the linked acf fields of this page
        $acfFields = get_field_objects($this->ID);
        $acfFields = ($acfFields) ? $acfFields : [];

        // Keep them in ACF mappings attribute to be use later when persist the entity
        $this->acf_mapping_dynamic = array_combine (
            array_map(function($field){ return $field['name']; }, $acfFields),
            array_map(function($field){ return $field['key']; }, $acfFields)
        );

        // Declare each ACF fields on the entity
        foreach ($acfFields as $field) {
            $this->set($field['name'], $field['value']);
        }

    }

}
