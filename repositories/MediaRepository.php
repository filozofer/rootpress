<?php

namespace Rootpress\repositories;

/**
 * MediaRepository
 */
class MediaRepository {

    // Repository parameters
    public static $instance;

    /**
     * Get class instance
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            $childclass = get_called_class();
            self::$instance = new $childclass;
        }

        return self::$instance;
    }

	/**
     * Find one media by ID and return all it's meta
     * @param $mediaId int ID of the media to retrieve
     * @param $params array of params to retrieve
     * @return array metadata of the media to retrieve
     */
    public function findOne($mediaId, $params = ['alt', 'caption', 'description', 'href', 'src', 'title', 'size', 'id']) {

        //Get media url to test if exist
        $attachment = get_post($mediaId);

        //Return null if no media found
        if($attachment->guid == false) {
            return null;
        }

        //Get metadata ask by user
        $result = [];
        foreach ($params as $param) {
            switch ($param) {
                case 'alt': $result[$param] = get_post_meta($mediaId, '_wp_attachment_image_alt', true); break;
                case 'href': $result[$param] = get_permalink($mediaId); break;
                case 'caption': $result[$param] = $attachment->post_excerpt; break;
                case 'description': $result[$param] =  $attachment->post_content; break;
                case 'src': $result[$param] = $attachment->guid; break;
                case 'title': $result[$param] = $attachment->post_name; break;
                case 'size': $result[$param] = $this->getFileSize(get_attached_file($mediaId)); break;
                case 'id': $result[$param] = $attachment->ID; break;
                default: break;
            }
        }

        //Return metadata
		return $result;
	}

    /**
     * Find one media by ID and return it's public URL
     * @param $mediaId int ID of the media to retrieve
     * @return string url of the media to retrieve
     */
    public function findOneUrl($mediaId) {
        $media = wp_get_attachment_url($mediaId);
        return $media;
    }

}
