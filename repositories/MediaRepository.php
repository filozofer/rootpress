<?php

namespace Rootpress\repositories;
use rootpress\exception\Media\PersistenseAttachmentInsertionException;
use rootpress\exception\Media\PersistenseChmodAttachmentFailedException;
use rootpress\exception\Media\PersistenseMediaException;
use rootpress\exception\Media\PersistenseUploadAttachmentFailedException;
use Rootpress\utils\FileUtils;
use Valitron\Validator;

/**
 * MediaRepository
 */
class MediaRepository {

    // Repository parameters
    public static $instance;

    /**
     * Get class instance
     *
     * @return MediaRepository
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

	/**
	 * Persist a media file
	 *
	 * @param $media
	 * @param $uploadDir
	 *
	 * @return int
	 * @throws PersistenseMediaException
	 */
	public function persist( $media, $uploadDir = null ) {
		$media_id = false;
		// Detect what kind of media we have
		if($this->mediaIsAttachment($media)){
			$media_id = $this->persistAttachment($media, $uploadDir);
		}

		if(!is_integer($media_id) || $media_id === 0){
			throw new PersistenseMediaException();
		}
		return $media_id;
	}

	/**
	 * Check if the media is an attachment file from $_FILES
	 * @param $media
	 *
	 * @return bool
	 */
	private function mediaIsAttachment( $media ){
		$result = false;
		if(is_array($media)){
			$v = new Validator($media);
			$v->rule('required', ['name', 'type', 'tmp_name']);

			$result = $v->validate();
		}

		return $result;
	}

	/**
	 * Persist an attachment file from $_FILES in WP database
	 *
	 * @param array $media
	 * @param string $uploadDir
	 *
	 * @return int
	 * @throws PersistenseAttachmentInsertionException
	 * @throws PersistenseChmodAttachmentFailedException
	 * @throws PersistenseUploadAttachmentFailedException
	 */
	private function persistAttachment(array $media, $uploadDir = null){
		// Get path to upload directory
		$path = is_null($uploadDir) ? wp_upload_dir()['path'] . '/' : $uploadDir;

		// Get formated file name with datetime
		$formattedFileName = FileUtils::formatFileName($media['name']);

		// Concat the upload path with the formated file name
		$fullPath = $path . $formattedFileName;

		// Move uploaded attachment to upload directory and check that it returned a success
		if(!move_uploaded_file($media['tmp_name'], $fullPath)){
			throw new PersistenseUploadAttachmentFailedException();
		}

		// Change attachment mod to 660 and check that it returned a success
		if(!chmod($fullPath, 0660)){
			throw new PersistenseChmodAttachmentFailedException();
		}

		// Create the WP attachment array
		$attachment = [
			'guid' => $fullPath,
			'post_title' => $formattedFileName,
			'post_content' => '',
			'post_status' => 'private',
			'post_mime_type' => finfo_file(finfo_open(FILEINFO_MIME_TYPE), $fullPath),
			'post_size' => FileUtils::getFileSize($fullPath),
		];

		// Try to insert the new attachment
		$attachmentId = wp_insert_attachment($attachment, $fullPath);
		if(!is_integer($attachmentId) || $attachmentId === 0){
			throw new PersistenseAttachmentInsertionException();
		}

		// Return the attachment id
		return $attachmentId;
	}
}
