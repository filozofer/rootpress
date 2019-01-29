<?php

namespace Rootpress\repositories;

use Rootpress\models\RootpressModel;
use Rootpress\Rootpress;

/**
 * CRUDTaxonomyRepository
 */
class CRUDTaxonomyRepository {

    // Associate taxonomy (can be override in child class)
    public static $associate_post_type = 'category';

    /**
     * Find one term by id
     * @param $termId int
     * @return RootpressModel
     */
    public static function findOne($termId)
    {
        // Find term by id
        $term = get_term($termId, static::$associate_post_type);

        // Convert to entity
        return Rootpress::getEntityFromWPPost($term);
    }

    /**
     * Find All terms and hydrate them
     */
    public static function findAll() {

        // Get all the terms
        $terms = get_terms(static::$associate_post_type, ['hide_empty' => false]);

        // Convert to entity
        return Rootpress::getEntityFromWPPost($terms);
    }

}
