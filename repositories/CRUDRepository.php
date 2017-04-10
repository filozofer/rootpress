<?php

namespace Rootpress\repositories;

use Rootpress\enums\ACFType;
use Rootpress\exception\CRUD\PersistenseCreationFailedException;
use Rootpress\models\RootpressModel;
use Rootpress\utils\CRUDUtils;
use Rootpress\utils\DateUtils;
use Rootpress\utils\Hydratator;
use WP_Query;

/**
 * CRUDRepository
 */
class CRUDRepository
{
    // Associate post type (can be override in child class)
    public static $associate_post_type = 'post';

    // Repository parameters
    public static $fields = [];
    public static $depth = 2;
    public static $instances;

    /**
     * Get class instance
     * $instances is an array which contains each instanced child class
     * @param $neededFields array of fields to set as settings
     * @param $neededDepth int depth to set as settings
     */
    public static function getInstance($neededFields = null, $neededDepth = null)
    {
        $childClass = get_called_class();

        if(!isset(self::$instances[$childClass])){
            self::$instances[$childClass] = new $childClass;
        }

        // Set fields if user ask for it
        if(!is_null($neededFields)) {
            if(is_string($neededFields)) {
                static::$fields = (isset(static::$$neededFields)) ? static::$$neededFields : [];
            }
            else if(is_array($neededFields)) {
                static::$fields = $neededFields;
            }
        }
        // Set depth if user ask for it
        if(!is_null($neededDepth)) {
            static::$depth = $neededDepth;
        }

        return self::$instances[$childClass];
    }

	/**
	 * Find one post with id
	 *
	 * @param $postId int id of post to retrieve
	 *
	 * @return mixed
	 */
    public function findOne($postId)
    {
        $post = get_post($postId);
        return Hydratator::hydrate($post, static::$fields, static::$depth);
    }

    /**
     * Find all post
     */
    public function findAll()
    {
        // Get all posts
        $posts = new WP_Query([
            'post_type'         => static::$associate_post_type,
            'post_status'       => 'publish',
            'suppress_filters'  => false,
	        'posts_per_page'    => -1
        ]);
	    $posts = $posts->get_posts();

        //Hydrate them
        return Hydratator::hydrates($posts, static::$fields, static::$depth);
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
	 * Find the id of a post by it's post title
	 * @param string $postTitle
	 *
	 * @return null|string null or the post id
	 */
	public function findIdByTitle( $postTitle ) {
		global $wpdb;
		$postType = static::$associate_post_type;
		return $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='$postType' AND post_status='publish'", $postTitle ) );
	}

	/**
	 * Function use to delete post
	 *
	 * @param RootpressModel|int $post Instance or object id of the post to delete
	 * @param boolean $trash if false, do not send post to trash, delete it for ever
	 *
	 * @return array|false|\WP_Post
	 */
    public function remove($post, $trash = true)
    {
    	$postId = (is_object($post)) ? $post->ID : $post;
        return wp_delete_post($postId, !$trash);
    }

	/**
	 * Create the entity
	 *
	 * @param RootpressModel $entity
	 *
	 * @return bool
	 * @throws PersistenseCreationFailedException thrown when the insertion failed
	 */
	public function persist( RootpressModel $entity ) {

		// Wordpress Post fields
		$wpPostFields = [
			'ID',
			'post_author',
			'post_content',
			'post_content_filtered',
			'post_title',
			'post_excerpt',
			'post_status',
			'post_type',
			'comment_status',
			'ping_status',
			'post_password',
			'post_name',
			'to_ping',
			'pinged',
			'post_parent',
			'menu_order',
			'post_mime_type',
			'guid',
			'post_category',
			'tax_input',
			'meta_input',
		];

		// Post fields default values
		$data         = [
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_status'    => 'publish',
		];

		// Browse Wordpress post fields and add fields that are set in the object's constants
		foreach ( $wpPostFields as $field ) {
			if ( isset( $entity->$field ) ) {
				$data[ $field ] = $entity->get($field);
			}
		}

		// If the getPostTitle method exists, define the post_title field with the returned value
		if(method_exists( $entity, 'getPostTitle')){
			$data['post_title'] = $entity->getPostTitle();
		}

		// Create the post
		$postId = wp_insert_post( $data );

		// If the post id is not an integer, the insertion failed, so throw an error
		if(!is_integer($postId)){
			throw new PersistenseCreationFailedException();
		}
		// Set the entity ID
		$entity->set('ID', $postId);
		return $this->persistACF( $entity );
	}

	/**
	 * Function use to persist the ACF of a post
	 * The method getAttributeMapping is mandatory to work
	 *
	 * @param RootpressModel $entity
	 *
	 * @return bool
	 */
    public function persistACF( RootpressModel $entity )
    {
    	$result = true;

	    if(!empty(self::$fields['fields'])){
    		$attributeMap = $entity->getAttributeMapping();

		    foreach(self::$fields['fields'] as $acfName){

			    if((is_string($acfName) || is_integer($acfName)) && array_key_exists($acfName, $attributeMap)) {
				    $fieldId = key( $attributeMap[ $acfName ] );
				    $attr    = $attributeMap[ $acfName ][ $fieldId ];

				    // Check if attribute has to be formatted before persist
				    $formattedAttrValue = CRUDUtils::formatACF($attributeMap[ $acfName ], $entity->get( $attr ));
				    if($formattedAttrValue !== $entity->get($attr)){
				    	$entity->set($attr, $formattedAttrValue);
				    }

				    update_field($fieldId, $entity->get($attr), $entity->get('ID'));
			    }
		    }
	    }
	    return $result;
    }

    /**
     * Function update one field of a post
     */
    public function updateOneField()
    {
        throw new Exception("TODO: updateOneField function", 1);
    }

}
