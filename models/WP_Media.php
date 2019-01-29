<?php

namespace Rootpress\models;

/**
 * WP_Media class to manipulate meida
 */
class WP_Media extends RootpressModel {

    /**
     * Linked this class to the native post type "attachment"
     * @var string
     */
    public static $linked_post_type = 'attachment';

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

    }

    /**
     * Get attachement alt
     */
    public function getAlt() {
        return get_post_meta($this->ID, '_wp_attachment_image_alt', true);
    }

}
