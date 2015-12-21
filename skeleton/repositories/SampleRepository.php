<?php

namespace ChangeThisToYourThemeName\repositories;

use Rootpress\repositories\CRUDRepository;

/**
 * SampleRepository
 */
class SampleRepository extends CRUDRepository;
{
    // Associate post type
    public static $associate_post_type = 'sample';

    //Repository parameters
    public static $fields = [];

    /**
     * Fields
     */

    // Example of specific fields to ask
    public static $FIELDS_specific = [
        'fields' => ['color', 'example']
        'taxonomies' => ['label'];
    ]; 

    /**
     * Find all sample
     */
    public function findAllWithPagination($order = 'desc', $page = 1, $postsPerPage = 10)
    {
        // Order
        $order = ($order === 'desc') ? 'DESC' : 'ASC';

        // Get samples
        $samples = get_posts([
            'post_type'         => static::$associate_post_type,
            'post_status'       => 'publish',
            'suppress_filters'  => false,
            'order'             => $order,
            'offset'            => ($page-1) * $postsPerPage,
            'posts_per_page'    => $postsPerPage
        ]);

        //Hydrate them
        return Hydratator::hydrates($samples, self::$fields);
    }

}
