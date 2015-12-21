<?php

namespace Rootpress\repositories;

/**
 * Repository for retrieve Options fields values
 */
class OptionRepository {

    /**
     * Find one option by key 
     * @param $key string option key
     */
    public function findOne($key) {
       return get_field($key, 'option');
    }

    /**
     * Find multiple options by keys
     * @param $keys array of options keys
     */
    public function findMany(array $keys){
    	$results = [];
    	foreach ($keys as $key) {
    		$results[$key] = $this->findOne($key);
    	}
    	return $results;
    }
    
}