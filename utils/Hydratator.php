<?php

/**
 * Static class which hydrate Custom Type & Taxonomies from theirs ACF fields
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
	 * @param $object Object to hydrate
	 * @param $fields Array['fields' => [], 'taxonomies' => []] This array allow to choose exactly which custom fields and taxonomies need to be retrieve (performance improvement)
	 * You can set 'fields' attribute for custom fields and 'taxonomies' attribute for taxonomies. The arrays can be a list of string or can have others array inside to describe what to do
	 * on a key (exemple: univers => ['color'] rather than 'univers' which get all the univers fields).
	 * @param $depth maximal depth for hydration
	 * @filter rootpress_before_hydrate Filter occur before begin the hydrate
	 * @filter rootpress_after_hydrate_<post_type> Filter occur after object have been hydrated
	 */
	public static function hydrate(&$object, $fields = [], $depth = 2) {

		// Case null
		if($object == null) {
			return null;
		}
		// Case WP_Error
		else if(is_a($object, 'WP_Error')) {
			return $object;
		}

		// Determine if object is a post or a taxonomy
		$type = (isset($object->ID)) ? 'post' : 'taxonomy';
		// Get is ID
		$ID = ($type == 'post') ? $object->ID : $object->term_id;
		// Create hash for cache system
		$fieldsMD5 = md5(serialize($fields));
		// Fire filter which allow rootpress to transform WP_Post as Model entity
		$object = apply_filters('rootpress_before_hydrate', $object);

		// Prevent infinite looping
		if($depth > 0) {

			// Call cached system if we have already cache this item since the beginning of the request (base on ID and fields when caching)
			if(!$disableCache && isset(self::$objectCache[$type . '_' . $ID . '_depth_' . $depth . '_fields_' . $fieldsMD5])) {
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
					$customFields[$fieldName] = get_field($fieldName, ($type == 'post') ? $ID : $object->taxonomy . '_' . $ID);
				}
			}
			// Get all existings fields (avoid this for prevent performances issues)
			else {
	            $customFields = get_fields(($type == 'post') ? $ID : $object->taxonomy . '_' . $ID);
				if($customFields === false) {
					$customFields = [];
				}
			}

			// Add the custom fields to the object
			foreach ($customFields as $key => $value) {
				$object->$key = $value;
				$current = $object->$key;

				// Hydrate the child(s) if it's a post or a taxonomy
				if(is_array($object->$key) && count($object->$key) > 0 && (isset($current[0]->post_type) || isset($current[0]->taxonomy))) {
					// If we declare specific fields to return, send them to Hydratator
					$fieldsForThisKey = (isset($fields['fields']) && isset($fields['fields'][$key]) && is_array($fields['fields'][$key])) ? $fields['fields'][$key] : [];
					$object->$key = self::hydrates($object->$key, $fieldsForThisKey, $depth--);
				}
				else if(isset($object->$key->post_type)){
					// If we declare specific fields to return, send them to Hydratator
					$fieldsForThisKey = (isset($fields['fields']) && isset($fields['fields'][$key]) && is_array($fields['fields'][$key])) ? $fields['fields'][$key] : [];
					$object->$key = self::hydrate($object->$key, $fieldsForThisKey, $depth--);
				}
			}

			/* Hydrate taxonomies */
			$taxonomies = [];

			// Get only taxonomies wanted
			if(!empty($fields) && isset($fields['taxonomies'])){
				foreach($fields['taxonomies'] as $field_key => $field) {
					$fieldName = (is_array($field)) ? $field_key : $field;
					$taxonomies[$fieldName] = get_the_terms($ID, $fieldName);
					if(is_array($taxonomies[$fieldName])) {
						$taxonomies[$fieldName] = array_values($taxonomies[$fieldName]);
					}
				}
			}
			// Get all taxonomies (avoid this for prevent performances issues)
			else {
				$taxonomiesList = get_post_taxonomies($ID);
				foreach ($taxonomiesList as $taxo) {
					$taxonomies[$taxo] = get_the_terms($ID, $taxo);
					if(is_array($taxonomies[$taxo])) {
						$taxonomies[$taxo] = array_values($taxonomies[$taxo]);
					}
				}
			}

			// Hydrate taxonomies
			foreach ($taxonomies as $key => $value) {
				$object->$key = $value;
				$fieldsForThisKey = (isset($fields['taxonomies']) && isset($fields['taxonomies'][$key]) && is_array($fields['taxonomies'][$key])) ? $fields['taxonomies'][$key] : [];
				if($value != false) {
					if(is_array($value)) {
						$object->$key = self::hydrates($value, $fieldsForThisKey, $depth--);
					}
					else {
						$object->$key = self::hydrate($value, $fieldsForThisKey, $depth--);
					}
				}
			}

		}

		// Filter which allow to do custom hydratation
		if(isset($object->post_type)) {
			$object = apply_filters('rootpress_after_hydrate_' . $object->post_type, $object);
		}

		// Call clean method if exist which allow to clean the object from undesired values (default from wordpress for example)
		if(method_exists($object, 'clean')) {
			$object->clean();
		}

		// Call construct if exist which allow to change some values on creation
		if(method_exists($object, 'construct')) {
			$this->construct();
		}

		// Put the object in cache
		self::$objectCache[$type . '_' . $ID . '_depth_' . $depth . '_fields_' . $fieldsMD5] = $object;

		return $object;
	}

	/**
	 * Hydrate an array of custom type
	 * @param $object Object to hydrate
	 * @param $fields Which fields we need to hydrate ?
	 * @param $depth maximal depth for hydration
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

}
