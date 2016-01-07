<?php

namespace Rootpress\repositories;

use Rootpress\utils\Hydratator;

/**
 *  PageRepository
 */
class PageRepository {

    //Repository parameters
    public static $fields = [];
    public static $depth = 2;
    public static $instance;

    /**
     * Get class instance
     * @param $neededFields array of fields to set as settings
     * @param $neededDepth int depth to set as settings
     */
    public static function getInstance($neededFields = null, $neededDepth = null)
    {
        if (is_null(self::$instance)) {
            $childclass = get_called_class();
            self::$instance = new $childclass;
        }

        // Set fields if user ask for it
        if(!is_null($neededFields)) {
            static::$fields = (isset(static::$$neededFields)) ? static::$$neededFields : [];
        }
        // Set depth if user ask for it
        if(!is_null($neededDepth)) {
            static::$depth = (isset(static::$$neededDepth)) ? static::$$neededDepth : [];
        }

        return self::$instance;
    }

    /**
     * Find one page by ID and hydrate it
     * @param $pageId int ID of the page to retrieve
     */
    public function findOne($pageId) {
        $page = get_post($pageId);
        return Hydratator::hydrate($page, static::$fields, static::$depth);
    }

}
