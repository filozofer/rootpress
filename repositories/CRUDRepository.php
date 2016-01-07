<?php

namespace Rootpress\repositories;

use Rootpress\utils\Hydratator;

/**
 * CRUDRepository
 */
class CRUDRepository
{
    // Associate post type (can be override in child class)
    public static $associate_post_type = 'post';

    // Repository parameters
    public static $fields = [];
    public static $instances;

    /**
     * Get class instance
     * $instances is an array which contains each instanced child class
     */
    public static function getInstance($fieldsNeeded = null)
    {
        $childClass = get_called_class();

        if(!isset(self::$instances[$childClass])){
            self::$instances[$childClass] = new $childClass;
        }

        // Set field if user ask for it
        if(!is_null($fieldsNeeded)) {
            static::$fields = (isset(static::$$fieldsNeeded)) ? static::$$fieldsNeeded : [];
        }

        return self::$instances[$childClass];
    }

    /**
     * Find one post with id
     * @param $postId int id of post to retrieve
     */
    public function findOne($postId)
    {
        $post = get_post($postId);
        return Hydratator::hydrate($post, static::$fields);
    }

    /**
     * Find all post
     */
    public function findAll()
    {
        //Magazines
        $posts = get_posts([
            'post_type' => static::$associate_post_type,
            'post_status' => 'publish',
            'suppress_filters' => false
        ]);

        //Hydrate them
        return Hydratator::hydrates($posts, static::$fields);
    }

    /**
     * TODO
     */
    public function findOneBy()
    {
        throw new Exception("TODO: FindBy function", 1);
    }


    /**
     * TODO
     */
    public function findBy()
    {
        throw new Exception("TODO: findBy function", 1);
    }

    /**
     * Function use to delete post
     * @param postId int Id of the post to delete
     * @param $trash boolean if false, do not send post to trash, delete it for ever
     */
    public function remove($postId, $trash = true)
    {
        return wp_delete_post($postid, !$trash);
    }

    /**
     * Function use to create post
     * @param postId int Id of the post to delete
     * @param $trash boolean if false, do not send post to trash, delete it for ever
     */
    public function create($postId, $trash = true)
    {
        throw new Exception("TODO: create function", 1);
    }

    /**
     * Function use to update multiple fields of a post
     */
    public function update()
    {
        throw new Exception("TODO: update function", 1);
    }

    /**
     * Function update one field of a post
     */
    public function updateOneField()
    {
        throw new Exception("TODO: updateOneField function", 1);
    }

}
