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

}
