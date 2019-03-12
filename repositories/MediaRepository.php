<?php

namespace Rootpress\repositories;

use Rootpress\models\WP_Media;
use Rootpress\Rootpress;

/**
 * MediaRepository
 */
class MediaRepository extends CRUDRepository {

	/**
     * Find one media by ID.
     *
     * @param $mediaId int ID of the media to retrieve
     * @return WP_Media metadata of the media to retrieve
     */
    public static function findOne($mediaId) {

        //Get media url to test if exist
        $attachment = get_post($mediaId);
        return Rootpress::getEntityFromWPPost($attachment);

	}

    /**
     * Retrieve public url of media from his ID.
     *
     * @param $mediaId int ID of the media to retrieve
     * @return string url of the media to retrieve
     */
    public static function findUrlFromId($mediaId) {
        return wp_get_attachment_url($mediaId);
    }


    /**
     * Insert an attachment from an URL address.
     *
     * @param string $url
     * @param int    $parent_post_id Attachment parent id
     * @param array  $metadatas Attachement metadata
     * @return int attachment id
     */
    public static function createFromUrl($url, $parent_post_id = null, $metadatas = []) {

        // Try to download attachment from url
        $http = new \WP_Http();
        $response = $http->request( $url );
        if(is_a($response, \WP_Error::class)) {
            return false;
        }
        if($response['response']['code'] != 200 ) {
            return false;
        }
        $upload = wp_upload_bits(basename(strtok($url, '?')), null, $response['body'] );
        if( !empty( $upload['error'] ) ) {
            return false;
        }

        // Insert attachment inside database
        $file_path = $upload['file'];
        $file_name = basename( $file_path );
        $file_type = wp_check_filetype( $file_name, null );
        $attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
        $wp_upload_dir = wp_upload_dir();
        $post_info = array(
            'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
            'post_mime_type' => $file_type['type'],
            'post_title'     => $attachment_title,
            'post_content'   => '',
            'post_status'    => 'inherit',
        );
        $attach_id = wp_insert_attachment( $post_info, $file_path, $parent_post_id );

        // Handle metadata
        require_once( get_home_path() . '/wp/wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
        wp_update_attachment_metadata( $attach_id,  $attach_data );
        foreach($metadatas as $metadataKey => $metadataValue) {
            update_post_meta( $attach_id, $metadataKey, $metadataValue);
        }

        // Return attachment ID
        return $attach_id;
        
    }

}
