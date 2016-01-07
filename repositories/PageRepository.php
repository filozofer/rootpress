<?php

namespace Rootpress\repositories;

use Rootpress\utils\Hydratator;

/**
 *  PageRepository
 */
class PageRepository {

    //Repository parameters
    public static $fields = [];
    public static $instance;

    /**
     * Get class instance
     */
    public static function getInstance($fieldsNeeded = null)
    {
        // Instanciate if null
        if (is_null(self::$instance)) {
            $childclass = get_called_class();
            self::$instance = new $childclass;
        }

        // Set field if user ask for it
        if(!is_null($fieldsNeeded)) {
            static::$fields = (isset(static::$$fieldsNeeded)) ? static::$$fieldsNeeded : [];
        }

        return self::$instance;
    }

    /**
     * Find one page by ID and hydrate it
     * @param $pageId int ID of the page to retrieve
     */
    public function findOne($pageId) {
        $page = get_post($pageId);
        return Hydratator::hydrate($page, self::$fields);
    }

}
