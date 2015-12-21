<?php

namespace ChangeThisToYourThemeName\models\customtypes;

use Rootpress\models\RootpressModel;

/**
 * Sample model example
 */
class Sample extends RootpressModel {

    //Declare which post_type this entity representing
	public static $linked_post_type = 'sample';

	//List of params to modify the name
	public static $paramsNameToChange = [
		'ID'               => 'id',
		'post_title'	   => 'title',
		'post_date'		   => 'date',
		'post_type'		   => 'type'
	];

	//Order of attributes
	public static $order_attributes = [
		'id', 'title', 'date', 'type'
	];

	/**
	 * Declare Post Type to Wordpress
	 */
	public static function customTypeDeclaration() {
		$args = [
			'label'               => __('sample'),
			'labels'              => [
				'name'                => __('Sample'),
				'singular_name'       => __('Sample'),
				'menu_name'           => __('Samples'),
				'parent_item_colon'   => __('Sample Parent'),
				'all_items'           => __('All the samples'),
				'view_item'           => __('View sample'),
				'add_new_item'        => __('Create sample'),
				'add_new'             => __('Add sample'),
				'edit_item'           => __('Edit sample'),
				'update_item'         => __('Update sample'),
				'search_items'        => __('Search sample'),
				'not_found'           => __('No sample found'),
				'not_found_in_trash'  => __('No sample found in trash')
			],
			'description'         => __('Model Example'),
			'supports'            => [],
			'taxonomies'          => [],
			'public'              => true,
			'menu_icon'           => 'dashicons-id'
		];
		register_post_type(self::$linked_post_type, $args);
	}

	/**
	 * Construct function to change some params after hydratation
	 */
	public function construct() {

	}
}
