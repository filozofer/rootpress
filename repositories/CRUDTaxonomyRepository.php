<?php

namespace Rootpress\repositories;

use Rootpress\utils\Hydratator;
use WP_Term;

/**
 * CRUDTaxonomyRepository
 */
class CRUDTaxonomyRepository {

    // Associate taxonomy (can be override in child class)
    public static $associate_post_type = 'category';

    //Repository parameters
    public static $fields = [];
    public static $instance;

    /**
     * Get class instance
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            $childclass = get_called_class();
            self::$instance = new $childclass;
        }

        return self::$instance;
    }

    /**
     * Find one term by id
     * @param $termId int
     * @return WP_Term
     */
    public function findOne($termId)
    {
        $term = get_term($termId, static::$associate_post_type);
        return Hydratator::hydrate($term, self::$fields);
    }

    /**
     * Find All terms and hydrate them
     */
    public function findAll() {

        //Get all the terms
        $terms = get_terms(static::$associate_post_type, ['hide_empty' => false]);

        //Hydrate them all
        $terms = Hydratator::hydrates($terms, self::$fields);

        return $rubrics;
    }

    /**
     * Find All terms IDs
     */
    public function findAllTermsIds() {

        //Get all the terms
        $terms = (get_terms(static::$associate_post_type, ['hide_empty' => false]));

        //Extract all the ids
        $terms_ids = [];
        foreach ($terms as $term) {
            array_push($terms_ids, $term->term_id);
        }

        //Return
        return $terms_ids;
    }

    /**
     * TODO: Create Term
     */
    public function create() {
        throw new Exception("TODO: Create Term", 1);
    }

    /**
     * TODO: Update Term
     */
    public function update() {
        throw new Exception("TODO: Update Term", 1);
    }

    /**
     * TODO: Remove Term
     */
    public function remove() {
        throw new Exception("TODO: Remove Term", 1);
    }

}
