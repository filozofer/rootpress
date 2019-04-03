<?php

namespace Rootpress\repositories;
use Rootpress\Rootpress;

/**
 * Repository for retrieve Options fields values
 */
class OptionRepository {

    /**
     * Find one option by key.
     *
     * @param $key string option key
     * @return mixed
     */
    public static function findOne($key) {

        // Retrieve option value
        $option = get_field($key, 'option');

        // Convert WP_Post if necessary
        $option = self::convertToEntity($option);

        // Return the option values
        return $option;

    }

    /**
     * Find multiple options by keys.
     *
     * @param $keys array of options keys
     * @return array
     */
    public static function findMany(array $keys){
    	$results = [];
    	foreach ($keys as $key) {
    		$results[$key] = self::findOne($key);
    	}
    	return $results;
    }

    /**
     * Find recursively entities in option value and convert them
     */
    protected static function convertToEntity($option) {

        // Case option is an entity
        if(is_a($option, 'WP_Post') || is_a($option, 'WP_User')) {
            return Rootpress::getEntityFromWPPost($option);
        }
        // Case option is an array of entities
        else if(is_array($option) && isset($option[0]) && is_a($option[0], 'WP_Post')) {
            return Rootpress::getEntityFromWPPost($option);
        }
        // Case option is an array and we want to be recursive
        else if(is_array($option)) {
            foreach ($option as $subOptionKey => $subOptionValue) {
                $option[$subOptionKey] = self::convertToEntity($subOptionValue);
            }
            return $option;
        }

        // No conversion applied
        return $option;
    }
    
}