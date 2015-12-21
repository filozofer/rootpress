<?php

namespace ChangeThisToYourThemeName\models\taxonomies;

use Rootpress\models\RootpressModel;

/**
 * Model LabelSample Taxonomy
 * Taxonomy representing the label of a sample
 * @linked_post_type sample
 */
class LabelSample extends RootpressModel {

    //Taxonomy name
    public static $linked_taxonomy = 'label';

    //Linked post type (array of post type)
    public static $linked_post_type = ['sample'];

    /**
     * Declare Taxonomy to Wordpress
     */
	public static function taxonomyDeclaration() {
		$labels = array(
            'name'                       => __('Label'),
            'singular_name'              => __('Label'),
            'menu_name'                  => __('Labels'),
            'all_items'                  => __('All the labels'),
            'parent_item'                => __('Parent Label'),
            'parent_item_colon'          => __('Parent Label:'),
            'new_item_name'              => __('New Label'),
            'add_new_item'               => __('Add Label'),
            'edit_item'                  => __('Edit Label'),
            'update_item'                => __('Update Label'),
            'separate_items_with_commas' => __('Separate labels with commas'),
            'search_items'               => __('Search a label'),
            'add_or_remove_items'        => __('Add or remove labels'),
            'choose_from_most_used'      => __('Choose from the most used labels'),
            'not_found'                  => __('No label found')
        );
        $args = array(
            'labels'                     => $labels,
            'public'                     => true,
        );
        register_taxonomy(self::$linked_taxonomy, self::$linked_post_type, $args);
	}

	/**
	 * Construct function to change some params after hydratation
	 */
	public function construct() {

	}
}
