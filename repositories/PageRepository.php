<?php

namespace Rootpress\repositories;

use Rootpress\models\WP_Page;
use Rootpress\Rootpress;

/**
 *  PageRepository
 */
class PageRepository extends CRUDRepository {


    // Associate post type
    public static $associate_post_type = 'page';

    /**
     * Find one page by ID.
     *
     * @param $pageId int ID of the page to retrieve
     * @return WP_Page
     */
    public static function findOne($pageId) {
        $page = get_post($pageId);
        return Rootpress::getEntityFromWPPost($page);
    }

    /**
     * Find one page by his path.
     *
     * @param $pagePath string Path uri of the page to retrieve
     * @return WP_Page
     */
    public static function findOneByPath($pagePath) {
        $page = get_page_by_path($pagePath);
        return Rootpress::getEntityFromWPPost($page);
    }

}
