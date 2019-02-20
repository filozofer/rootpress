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
        $option = (is_a($option, 'WP_Post') || (is_array($option) && is_a($option[0], 'WP_Post'))) ? Rootpress::getEntityFromWPPost($option) : $option;
        
        // Return the option value
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
    
}