<?php

namespace Rootpress\utils;

/**
 * Static class which hydrate Customs Types, Taxonomies & Users from theirs ACF fields
 */
 class Hydratator {

 	// Allow to keep the trace of call
 	public static $countHydrate = 0;
 	public static $countHydrates = 0;

 	// Cache system to better performance
 	public static $disableCache = false;
 	public static $objectCache = [];

	 /**
	  * Hydrate custom type
	  *
	  * @param $object Object to hydrate
	  * @param array['fields' => [], 'taxonomies' => []] $fields This array allow to choose exactly which custom fields and taxonomies need to be retrieve (performance improvement)
	  * You can set 'fields' attribute for custom fields and 'taxonomies' attribute for taxonomies. The arrays can be a list of string or can have others array inside to describe what to do
	  * on a key (exemple: univers => ['color'] rather than 'univers' which get all the univers fields).
	  * @param integer $depth maximal depth for hydration
	  *
	  * @filter rootpress_before_hydrate Filter occur before begin the hydrate
	  * @filter rootpress_after_hydrate_<post_type> Filter occur after object have been hydrated
	  * @return false|mixed|Object|void|\WP_User
	  */
	public static function hydrate(&$object, $fields = [], $depth = 2) {

		// Case null or not an object
		if(is_null($object) || (!is_object($object) && !is_array($object))) {
			return $object;
		}
		// Case WP_Error
		else if(is_a($object, 'WP_Error')) {
			return $object;
		}
		// Fix for case in which ACF return an array which represent a user and not a WP_User : Detect by the presence of the 'user_registered' key and 'user_avatar' inside the array by supposing these key will not be use in usual array (possible error case)
		else if(is_array($object) && array_key_exists('user_registered', $object) && array_key_exists('user_avatar', $object) && isset($object['ID'])) {
			$object = get_user_by('ID', $object['ID']);
		}
		// Case ACF Repeater Field values
		else if(is_array($object) && isset($fields['fields']) && !empty($fields['fields'])) {
			return self::hydrates($object, $fields, $depth);
		}

		// Determine if object is a post, a taxonomy or a user
		$type = '';
		if(get_class($object) === 'WP_Post') {
			$type = 'post';
		}
		else if(get_class($object) === 'WP_Term') {
			$type = 'taxonomy';
		}
		else if(get_class($object) === 'WP_User') {
			$type = 'user';
		}
		else {
			return $object;
		}
		// Get is ID
		$ID = self::getIdFromWpObject($object);
		// Create hash for cache system
		$fieldsMD5 = md5(serialize($fields));
		// Fire filter which allow rootpress to transform WP_Post as Model entity
		$object = apply_filters('rootpress_before_hydrate', $object);

		// Prevent infinite looping
		if($depth > 0 || (is_array($fields) && !empty($fields))) {

			// Call cached system if we have already cache this item since the beginning of the request (base on ID and fields when caching)
			if(!self::$disableCache && isset(self::$objectCache[$type . '_' . $ID . '_depth_' . $depth . '_fields_' . $fieldsMD5])) {
				return self::$objectCache[$type . '_' . $ID . '_depth_' . $depth . '_fields_' . $fieldsMD5];
			}

			// Keep count
			self::$countHydrate++;

			/* Hydrate customs fields */

			// Get only fields wanted
			if(!empty($fields) && isset($fields['fields'])){
				$customFields = [];
				foreach($fields['fields'] as $field_key => $field) {
					$fieldName = (is_array($field)) ? $field_key : $field;
					$acfKey = self::getAcfKey($object, $type, $ID);
					$customFields[$fieldName] = get_field($fieldName, $acfKey);
				}
			}
			// Get all existings fields (avoid this for prevent performances issues)
			else {

				$acfKey = self::getAcfKey($object, $type, $ID);
	            $customFields = get_fields($acfKey);
				if($customFields === false) {
					$customFields = [];
				}
			}

			// Add the custom fields to the object
			foreach ($customFields as $key => $value) {
				$object->$key = $value;
				$current = method_exists($object, 'get') ? $object->get($key) : $object->$key;

				// Hydrate the child(s) if it's an object
				if(is_array($object->$key) && count($object->$key) > 0 && isset($current[0]) && is_object($current[0]) && in_array(get_class($current[0]), ['WP_Post', 'WP_Term', 'WP_User'])) {
					// If we declare specific fields to return, send them to Hydratator
					$fieldsForThisKey = (isset($fields['fields']) && isset($fields['fields'][$key]) && is_array($fields['fields'][$key])) ? $fields['fields'][$key] : [];

					// Handle WP_User case by using setter to avoid notice from magic WP_User setter function
					// TODO Can we find a better method than disable php notice ? "Notice: Indirect modification of overloaded property WP_User as no effect"
					$actualLevelReporting = error_reporting(E_ERROR | E_WARNING);
					$object->$key = self::hydrates($object->$key, $fieldsForThisKey, $depth - 1);
					error_reporting($actualLevelReporting);
				}
				else if(is_object($current) && in_array(get_class($current), ['WP_Post', 'WP_Term', 'WP_User'])){
					// If we declare specific fields to return, send them to Hydratator
					$fieldsForThisKey = (isset($fields['fields']) && isset($fields['fields'][$key]) && is_array($fields['fields'][$key])) ? $fields['fields'][$key] : [];
					$actualLevelReporting = error_reporting(E_ERROR | E_WARNING);
					$object->$key = self::hydrate($object->$key, $fieldsForThisKey, $depth - 1);
					error_reporting($actualLevelReporting);
				}
				else if(is_array($object->$key) && isset($fields['fields']) && isset($fields['fields'][$key]) && is_array($fields['fields'][$key])) {
					$fieldsForThisKey = $fields['fields'][$key];
					$object->$key = self::hydrates($object->$key, $fieldsForThisKey, $depth - 1);
				}
			}

			// Hydrate taxonomies
			$taxonomies = [];

			// Get only taxonomies wanted
			if (!empty($fields) && isset($fields['taxonomies'])) {
				foreach ($fields['taxonomies'] as $field_key => $field) {
					$fieldName = (is_array($field)) ? $field_key : $field;
					$taxonomies[$fieldName] = get_the_terms($ID, $fieldName);
					if (is_array($taxonomies[$fieldName])) {
						$taxonomies[$fieldName] = array_values($taxonomies[$fieldName]);
					}
				}
			} // Get all taxonomies (avoid this for prevent performances issues)
			else {
				$taxonomiesList = get_post_taxonomies($ID);
				foreach ($taxonomiesList as $taxo) {
					$taxonomies[$taxo] = get_the_terms($ID, $taxo);
					if (is_array($taxonomies[$taxo])) {
						$taxonomies[$taxo] = array_values($taxonomies[$taxo]);
					}
				}
			}

			// Hydrate taxonomies
			foreach ($taxonomies as $key => $value) {
				$object->$key = $value;
				$fieldsForThisKey = (isset($fields['taxonomies']) && isset($fields['taxonomies'][$key]) && is_array($fields['taxonomies'][$key])) ? $fields['taxonomies'][$key] : [];
				if ($value != false) {
					if (is_array($value)) {
						$object->$key = self::hydrates($value, $fieldsForThisKey, $depth - 1);
					} else {
						$object->$key = self::hydrate($value, $fieldsForThisKey, $depth - 1);
					}
				}
			}

		}

		// Filter which allow to do custom hydratation
		if(isset($object->post_type)) {
			$object = apply_filters('rootpress_after_hydrate_' . self::getTypeFromWpObject($object), $object);
		}

		// Call clean method if exist which allow to clean the object from undesired values (default from wordpress for example)
		if(method_exists($object, 'clean')) {
			$object->clean();
		}

		// Call construct if exist which allow to change some values on creation
		if(method_exists($object, 'construct')) {
			$object->construct();
		}

		// Put the object in cache
		self::$objectCache[$type . '_' . $ID . '_depth_' . $depth . '_fields_' . $fieldsMD5] = $object;

		return $object;
	}

	/**
	 * Hydrate an array of custom type
	 * @param array[WP_Post|WP_Term|WP_User] $object Array of objects to hydrate
	 * @param $fields array Which fields we need to hydrate ?
	 * @param $depth int maximal depth for hydration
	 * @return array of hydrated objects
	 */
	public static function hydrates(&$objects, $fields = [], $depth = 2) {

		// If null return empty array
		if($objects == null) {
			return [];
		}

		// Keep count of call
		self::$countHydrates++;

		// Call the hydrate method for each object
		foreach ($objects as $key => $object) {
			$objects[$key] = self::hydrate($object, $fields, $depth);
		}

		// Return an array of hydrated objects
		return $objects;

	}

	 /**
	  * Get ID of a WP Object
	  * @param $object WP Object (WP_Post|WP_Term|WP_User)
	  * @return int ID of the object
	  */
	 private static function getIdFromWpObject($object) {
		 if(get_class($object) === 'WP_Post') {
			 return $object->ID;
		 }
		 else if(get_class($object) === 'WP_Term') {
			 return $object->term_id;
		 }
		 else if(get_class($object) === 'WP_User') {
			 return (isset($object->data)) ? $object->ID : null;
		 }
		 else {
			 return null;
		 }
	 }

	 /**
	  * Get Type of a WP Object (post_type|taxonomy|user)
	  * @param $object WP Object (WP_Post|WP_Term|WP_User)
	  * @return string (post_type|taxonomy|user)
	  */
	 private static function getTypeFromWpObject($object) {
		 if(get_class($object) === 'WP_Post') {
			 return $object->post_type;
		 }
		 else if(get_class($object) === 'WP_Term') {
			 return $object->taxonomy;
		 }
		 else if(get_class($object) === 'WP_User') {
			 return 'user';
		 }
		 else {
			 return '';
		 }
	 }

	 /**
	  * Get ACF key to get a field value
	  * @param $object The object containing the acf field
	  * @param $type For performance send directly the type of the object (post|taxonomy|user)
	  * @param $ID ID of the current object
	  * @return string acf key to use with get_field method
	  */
	 private static function getAcfKey($object, $type, $ID)
	 {
		 if ($type === 'post') {
			 return $ID;
		 } else if ($type === 'taxonomy') {
			 return $object->taxonomy . '_' . $ID;
		 } else if ($type === 'user') {
			 return 'user_' . $ID;
		 } else {
			 return null;
		 }
	 }

}
