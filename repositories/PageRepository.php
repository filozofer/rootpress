<?php

namespace Rootpress\repositories;

use Rootpress\utils\Hydratator;

/**
 *  PageRepository
 */
class PageRepository {

    //Repository parameters
    public static $fields = [];

    /**
     * Find one page by ID and hydrate it
     * @param $pageId int ID of the page to retrieve
     */
    public function findOne($pageId) {
        $page = get_post($pageId);
        return Hydratator::hydrate($page, self::$fields);
    }

}
