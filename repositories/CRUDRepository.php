<?php

namespace Rootpress\repositories;

use Rootpress\enums\ACFType;
use Rootpress\exception\CRUD\PersistenseCreationFailedException;
use Rootpress\models\LazyACFLoader;
use Rootpress\models\RootpressModel;
use Rootpress\Rootpress;
use Rootpress\utils\CRUDUtils;
use Rootpress\utils\DateUtils;
use Rootpress\utils\Hydratator;
use WP_Query;

/**
 * CRUDRepository.
 * Use this class as your parent repository
 */
class CRUDRepository {

    // Associate post type (can be override in child class)
    public static $associate_post_type = 'post';

	/**
	 * Find one post with id.
	 *
	 * @param $postId int id of post to retrieve
	 *
	 * @return mixed
	 */
    public static function findOne($postId) {
        $post = get_post($postId);
        return Rootpress::getEntityFromWPPost($post);
    }

    /**
     * Find all post.
     */
    public static function findAll() {

        // Get all posts
        $posts = new WP_Query([
            'post_type'         => static::$associate_post_type,
            'post_status'       => 'publish',
            'suppress_filters'  => false,
	        'posts_per_page'    => -1
        ]);
	    $posts = $posts->get_posts();

        // Convert them to models
        return Rootpress::getEntityFromWPPost($posts);

    }

	/**
	 * Find the id of a post by it's post title.
     *
	 * @param string $postTitle
	 *
	 * @return null|string null or the post id
	 */
	public static function findIdByTitle( $postTitle ) {

	    global $wpdb;
		$postType = static::$associate_post_type;
		return $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='$postType' AND post_status='publish'", $postTitle ) );

	}

	/**
	 * Function use to delete post.
	 *
	 * @param RootpressModel|int $post Instance or object id of the post to delete
	 * @param boolean $trash if false, do not send post to trash, delete it for ever
	 *
	 * @return array|false|\WP_Post
	 */
    public static function remove($post, $trash = true) {
    	$postId = (is_object($post)) ? $post->ID : $post;
        return wp_delete_post($postId, !$trash);
    }

	/**
	 * Create the entity
	 *
	 * @param RootpressModel $entity
	 *
	 * @return bool|\WP_Error
	 */
	public static function persist( RootpressModel $entity ) {

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
		$WPPostArray         = [
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_status'    => 'publish',
		];

		// Browse Wordpress post fields and add fields that are set in the object's constants
		foreach ( $wpPostFields as $field ) {
			if ( isset( $entity->$field ) ) {
                $WPPostArray[ $field ] = $entity->get($field);
			}
		}

		// If the getPostTitle method exists, define the post_title field with the returned value
		if(method_exists( $entity, 'getPostTitle')){
            $WPPostArray['post_title'] = $entity->getPostTitle();
		}

		// Create or update the post
		$postId = wp_insert_post( $WPPostArray );

		// If the post id is not an integer, the insertion failed, so throw an error
		if(!is_integer($postId)){
			return $postId;
		}

		// Set the entity ID after creation
		$entity->set('ID', $postId);

		// Persist ACF Fields
        static::persistACF( $entity );

        // Success !
		return TRUE;

	}

	/**
	 * Function use to persist the ACF fields of a post
	 *
	 * @param RootpressModel $entity
	 */
    public static function persistACF( RootpressModel $entity )
    {
    	foreach ($entity::$acf_mapping as $fieldName => $fieldKey) {
    	    if(!is_a($entity->$fieldName, LazyACFLoader::class)) {
                update_field($fieldKey, $entity->get($fieldName), $entity->get('ID'));
            }
        }
        foreach ($entity->acf_mapping_dynamic as $fieldName => $fieldKey) {
            if(!is_a($entity->$fieldName, LazyACFLoader::class)) {
                update_field($fieldKey, $entity->get($fieldName), $entity->get('ID'));
            }
        }
    }

}
