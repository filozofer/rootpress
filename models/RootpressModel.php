<?php

namespace Rootpress\models;

/**
 * Rootpress Model for transversal function to models
 * This abstract model can be use as template for your own abstract parent model
 */
abstract class RootpressModel  {

	//List of params to modify the name
	public static $paramsNameToChange = [
		'ID'      => 'id',
		'term_id' => 'id'
	];

	//List of default params in wordpress which we want to remove by default from the object return by wordpress (allow to have clean object)
	public static $paramsToRemove = [
		'ID', 'term_id', 'post_author', 'post_date_gmt', 'post_content', 'post_excerpt', 'post_status', 'comment_status',
		'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified',
		'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type',
		'post_mime_type', 'comment_count', 'filter', 'post_title', 'post_date',
		'term_group', 'term_taxonomy_id', 'taxonomy', 'description', 'parent', 'count', 'category',
		'post_tag', 'post_format', 'object_id'
	];

	/**
	 * Constructor for this model
	 * Override this function in your child class to do post treatement after hydratation
	 */
	public function construct() {
	}

	/**
	 * Generic getter
	 * Prefer using this to access your attribute
	 */
	public function get($paramName) {
		$getter = 'get' . ucwords($paramName);
		if(method_exists($this, $getter)) {
			return $this->$getter();
		}
		return $this->$paramName;
	}

	/**
	 * Generic setter
	 * Prefer using this to change value of your attribute
	 */
	public function set($paramName, $value) {
		$setter = 'set' . ucwords($paramName);
		if(method_exists($this, $setter)) {
			return $this->$setter($value);
		}
		return $this->$paramName = $value;
	}

	/**
	 * Magic Method to call the generics getter and setter when accessing a private attribute of your class
	 * If you want to respect encapsulation rules you need to declare all your object fields as private attribute inside your child class
	 * When the hydrate process will try to hydrate your field, these magic function will call the generics getter and setter
	 */
	public function __get($name){ $this->get($name); }
	public function __set($name, $value){ $this->set($name, $value); }

	/**
	 * Clean model by removing or rename default attributes inherit from WP_Post object
	 */
	public function clean() {

		//Extract attributes
		$attributes = [];
		foreach ($this as $key => $attribute) {
			$attributes[$key] = $attribute;
			unset($this->$key);
		}

		//Change params name
		$paramsToChange = [];
		foreach (static::$paramsNameToChange as $oldKey => $newKey) {
			if(isset($attributes[$oldKey])) {
				$paramsToChange[$newKey] = $attributes[$oldKey];
				unset($attributes[$oldKey]);
			}
		}

		//Remove params
		foreach (static::$paramsToRemove as $param) {
			unset($attributes[$param]);
		}

		//Re-Set all attributes
		$attributes = array_merge($paramsToChange, $attributes);
		foreach ($attributes as $key => $value) {
			$this->$key = $value;
		}

		//Order attributes
		$this->order();
	}

	/**
	 * Order the attributes as wanted if "order_attributes" static attribute is set in the class
	 * Mostly use when you want to return your object in a web service, it allow to have property in the same order than your documentation for example
	 */
	public function order() {

		//Only if class has defined an order for attributes
		if(isset(static::$order_attributes) && is_array(static::$order_attributes)) {

			//Create empty container
			$data = new \stdClass();
			foreach (static::$order_attributes as $attr) {
				if(isset($this->$attr)) {
					//Keep value attribute
					$data->$attr = $this->$attr;

					//Unset attributes
					unset($this->$attr);

					//Put the attribute at the bottom of the object
					$this->$attr = $data->$attr;
				}
			}

		}
	}

}
