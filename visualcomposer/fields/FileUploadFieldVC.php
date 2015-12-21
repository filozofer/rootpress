<?php

namespace Rootpress\visualcomposer\fields;

/** 
 * File Upload field for Visual Composer
 */
class FileUploadFieldVC {

    //Name of the field type
    public static $fieldName = 'File Upload Field';
    //Name use for declare the field which is the name of the shortcode
    public static $shortcodeName = 'vc_file_upload_field';
    //Default settings
    public static $defaultSettings = [
    ];

    /**
     * Field form
     */
    public static function fieldForm($settings, $fileId) {
        global $post;

        //Default settings
        $settings = array_replace_recursive(self::$defaultSettings, $settings);
        ob_start();

        //Input params
        $paramNameEsc = esc_attr($settings['param_name']);
        $classString = 'wpb_vc_param_value ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_field';
        $uniqid = uniqid();

        // Get WordPress media upload URL
        $uploadLink = esc_url(get_upload_iframe_src('image'));

        // Get the file url
        $fileUrl = wp_get_attachment_url($fileId);
        $fileExtension = pathinfo($fileUrl, PATHINFO_EXTENSION);
        $fileName = pathinfo($fileUrl, PATHINFO_FILENAME);

        //File already set ?
        $weGotFile = ($fileUrl && !empty($fileUrl));

        //Default file url
        $defaultDocumentImage = get_site_url() . '/wp-includes/images/media/document.png';
        $fileUrl = (self::isImageExtension($fileExtension)) ? $fileUrl : $defaultDocumentImage;


        //Form input
        ?>
        <div class="file_upload_field_block <?php echo $uniqid; ?>"> 
            <input type="hidden" value="<?php echo esc_attr($fileId); ?>" id="<?php echo $uniqid; ?>" name="<?php echo $paramNameEsc; ?>" class="<?php echo $classString ?>">
            <a class="upload-vc-file <?php if($weGotFile) { echo 'hidden'; } ?>" href="#" use-single="true" title="Add file" href="<?php echo $uploadLink ?>">Add File</a>
            <div class="file-upload-container <?php if(!$weGotFile) { echo 'hidden'; } ?> <?php echo (self::isImageExtension($fileExtension)) ? 'image' : 'document'; ?>">
                <div class="thumbnail">
                    <div class="centered">
                        <img src="<?php echo $fileUrl; ?>" class="icon" draggable="false">
                    </div>
                    <div class="filename <?php echo (self::isImageExtension($fileExtension)) ? 'hidden' : ''; ?>">
                        <div><?php echo $fileName . '.' . $fileExtension; ?></div>
                    </div>
                </div>
                <a href="#" class="icon-remove delete-vc-file"></a>
            </div>
        </div>

        <?php
        //Allow to choose the file inside media wordpress gallery
        ?>
        <script type="text/javascript">
            jQuery(function($){
                // Set all variables to be used in scope
                var frame;
                var $metaBox = $('.file_upload_field_block.<?php echo $uniqid; ?>'); 
                var $addFileLink = $metaBox.find('.upload-vc-file');
                var $delFileLink = $metaBox.find( '.delete-vc-file');
                var $fileContainer = $metaBox.find( '.file-upload-container');
                var $fileIdInput = $metaBox.find( '.<?php echo esc_attr($settings['param_name']); ?>' );

                //Add file
                $addFileLink.on('click', function(e){
                    e.preventDefault();

                    // If the media frame already exists, reopen it.
                    if (frame) {
                        frame.open();
                        return;
                    }

                    // Create a new media frame
                    frame = wp.media({
                        title: 'Selectionnez ou téléchargez un fichier de votre choix',
                        button: {
                            text: 'Selectionner'
                        },
                        multiple: false  // Set to true to allow multiple files to be selected
                    });

                    // When an image is selected in the media frame...
                    frame.on('select', function() {
                      
                        // Get media attachment details from the frame state
                        var attachment = frame.state().get('selection').first().toJSON();

                        // Display the document in the form
                        if($.inArray(attachment.subtype, ['png', 'jpeg', 'jpg', 'gif']) >= 0) {
                            $fileContainer.removeClass('document');
                            $fileContainer.addClass('image');
                            $fileContainer.find('img').attr('src', attachment.url);
                            $fileContainer.find('.filename').addClass('hidden');
                        }
                        else {
                            $fileContainer.removeClass('image');
                            $fileContainer.addClass('document');
                            $fileContainer.find('img').attr('src', attachment.icon);
                            $fileContainer.find('.filename div').text(attachment.filename);
                            $fileContainer.find('.filename').removeClass('hidden');
                        }
                        $fileContainer.removeClass('hidden');

                        // Send the attachment id to our hidden input
                        $fileIdInput.val(attachment.id);

                        // Hide the add image link
                        $addFileLink.addClass('hidden');
                    });

                    // Finally, open the modal on click
                    frame.open();
                });

                //Delete file
                $delFileLink.on('click', function(e){
                    e.preventDefault();

                    // Clear out the preview image
                    $fileContainer.addClass('hidden');

                    // Un-hide the add image link
                    $addFileLink.removeClass('hidden');

                    // Delete the image id from the hidden input
                    $fileIdInput.val('');
                });
            });
        </script>
        <style>
            /* CSS is here for have all the field associate code in one file */
            .file-upload-container{
                position: relative;
                -webkit-box-shadow: inset 0 0 15px rgba( 0, 0, 0, 0.1 ), inset 0 0 0 1px rgba( 0, 0, 0, 0.05 );
                box-shadow: inset 0 0 15px rgba( 0, 0, 0, 0.1 ), inset 0 0 0 1px rgba( 0, 0, 0, 0.05 );
                background: #eee;
            }
            .file-upload-container.document {
                width: 180px;
                height: 120px;
            }
            .file-upload-container.image {
                width: 140px;
                height: 140px;
            }
            .file-upload-container .thumbnail {
                overflow: hidden;
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                opacity: 1;
                -webkit-transition: opacity .1s;
                transition: opacity .1s;
            }

            .file-upload-container .thumbnail .centered {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                -webkit-transform: translate( 50%, 50% );
                -ms-transform: translate( 50%, 50% );
                transform: translate( 50%, 50% );
            }
            .file-upload-container .thumbnail .centered img {
                transform: translate( -50%, -70% );
                -webkit-transform: translate( -50%, -70% );
                -ms-transform: translate( -50%, -70% );
                max-height: 100%;
            }
            .file-upload-container.image .thumbnail .centered img {
                transform: translate( -50%, -50% );
                -webkit-transform: translate( -50%, -50% );
                -ms-transform: translate( -50%, -50% );
            }
            .file-upload-container .thumbnail .filename {
                position: absolute;
                left: 0;
                right: 0;
                bottom: 0;
                overflow: hidden;
                max-height: 100%;
                word-wrap: break-word;
                text-align: center;
                font-weight: bold;
                background: rgba( 255, 255, 255, 0.8 );
                -webkit-box-shadow: inset 0 0 0 1px rgba( 0, 0, 0, 0.15 );
                box-shadow: inset 0 0 0 1px rgba( 0, 0, 0, 0.15 );
            }
            .file-upload-container .thumbnail .filename div {
                padding: 5px 10px;
            }
            .file-upload-container .delete-vc-file {
                background: center center no-repeat;
                background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAAM1BMVEUAAAD/aEb/hmT/oX/9Xjz/k3HuUjD9YkDOIwL/qYf7VjTQJgTYMQ/4a0n/d1XgORfMIQDn4o98AAAACXRSTlMA////////gIBfH5JAAAAAR0lEQVQIHRXAAQLAEAwEwVVJOBT/f23TgVZIpUEzGZisMWrV+6rWAeEuuQcpnhT8wvf2IC2VpAVLZueYadHnmDDH7NAv6XY+Y+cCC0UGlLkAAAAASUVORK5CYII=');
                width: 16px;
                height: 16px;
                display: block;
                position: absolute;
                top: 0;
                right: 0;
            }
            .upload-vc-file {
                margin-top: 0px;
                display: block;
                float: left;
                height: 80px;
                width: 80px;
                background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAHlBMVEVMaXEYswF25gCU7AAe0gA72QCy8gDP+QBZ3wDs/wAqGC7kAAAAAXRSTlMAQObYZgAAADhJREFUCNdjYMALBAWhDMmJUIZ4IZhiFBRLFBQAMYyBAMxQAgIwQ1CiESzFwCAaCNUl4ohuIA4AAHwTBR7lJK51AAAAAElFTkSuQmCC');
                background-repeat: no-repeat;
                background-color: #f5f5f5;
                background-position: center;
                border: 1px solid #DFDFDF;
                font-size: 0px;
                color: #F5F5F5;
            }
        </style>
        <?php

        return ob_get_clean();
    }

    /**
     * Allow to determine if an extension is an image extension
     * @param $ext string Extension to test
     */
    private static function isImageExtension($ext) {
        return (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']));
    }
}