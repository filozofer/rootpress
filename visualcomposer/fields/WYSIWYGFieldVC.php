<?php

namespace Rootpress\visualcomposer\fields;

/** 
 * WYSIWYG Field for Visual Composer
 */
class WYSIWYGFieldVC {

    //Name of the field type
    public static $fieldName = 'WYSIWYG Field';
    //Name use for declare the field which is the name of the shortcode
    public static $shortcodeName = 'vc_wysiwyg_field';
    //Default settings
    public static $defaultSettings = [
        'height' => 200
    ];

    /**
     * Field 
     */
    public static function fieldForm($settings, $value) {
        //Default settings
        $settings = array_replace_recursive(self::$defaultSettings, $settings);
        ob_start();

        //Input params
        $paramNameEsc = esc_attr($settings['param_name']);
        $classString = 'wpb_vc_param_value ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_field';
        $uniqid = uniqid();
 
        //Form input
        echo '<div class="wysiwyg_field_block ' . $uniqid . '">' .
                '<textarea id="' . $uniqid . '" name="' . $paramNameEsc . '" class="' . $classString . '">' .
                    $value .
                '</textarea>' . 
              '</div>';

        //Transform the textarea to WYSIWYG using tinyMCE
        ?>
        <script type="text/javascript">
            // Minify Base64 Util object
            var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(r){var t,e,o,a,h,n,c,d="",C=0;for(r=Base64._utf8_encode(r);C<r.length;)t=r.charCodeAt(C++),e=r.charCodeAt(C++),o=r.charCodeAt(C++),a=t>>2,h=(3&t)<<4|e>>4,n=(15&e)<<2|o>>6,c=63&o,isNaN(e)?n=c=64:isNaN(o)&&(c=64),d=d+this._keyStr.charAt(a)+this._keyStr.charAt(h)+this._keyStr.charAt(n)+this._keyStr.charAt(c);return d},decode:function(r){var t,e,o,a,h,n,c,d="",C=0;for(r=r.replace(/[^A-Za-z0-9\+\/\=]/g,"");C<r.length;)a=this._keyStr.indexOf(r.charAt(C++)),h=this._keyStr.indexOf(r.charAt(C++)),n=this._keyStr.indexOf(r.charAt(C++)),c=this._keyStr.indexOf(r.charAt(C++)),t=a<<2|h>>4,e=(15&h)<<4|n>>2,o=(3&n)<<6|c,d+=String.fromCharCode(t),64!=n&&(d+=String.fromCharCode(e)),64!=c&&(d+=String.fromCharCode(o));return d=Base64._utf8_decode(d)},_utf8_encode:function(r){r=r.replace(/\r\n/g,"\n");for(var t="",e=0;e<r.length;e++){var o=r.charCodeAt(e);128>o?t+=String.fromCharCode(o):o>127&&2048>o?(t+=String.fromCharCode(o>>6|192),t+=String.fromCharCode(63&o|128)):(t+=String.fromCharCode(o>>12|224),t+=String.fromCharCode(o>>6&63|128),t+=String.fromCharCode(63&o|128))}return t},_utf8_decode:function(r){for(var t="",e=0,o=c1=c2=0;e<r.length;)o=r.charCodeAt(e),128>o?(t+=String.fromCharCode(o),e++):o>191&&224>o?(c2=r.charCodeAt(e+1),t+=String.fromCharCode((31&o)<<6|63&c2),e+=2):(c2=r.charCodeAt(e+1),c3=r.charCodeAt(e+2),t+=String.fromCharCode((15&o)<<12|(63&c2)<<6|63&c3),e+=3);return t}};
        </script>
        <script type="text/javascript">
            //Transform textarea value from base64 to normal html
            jQuery('#<?php echo $uniqid; ?>').val(Base64.decode(jQuery('#<?php echo $uniqid; ?>').val()));
            
            //Init TinyMCE on textarea
            tinyMCE.init({
                selector: "#<?php echo $uniqid; ?>",
                plugins: ["paste colorpicker textcolor wplink"],
                paste_as_text: true,
                toolbar1: "undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent",
                toolbar2: "cut copy paste separator link unlink forecolor backcolor",
                setup: function (ed) {
                    //Handle the save as base 64 string
                    ed.on("change", function(){
                        jQuery('#'+ ed.id).val(Base64.encode(ed.getBody().innerHTML));
                    });
                },
                //Change height of editor
                height: <?php echo $settings['height']; ?>,
                content_css: "<?php echo get_stylesheet_directory_uri() . '/assets/css/custom-tinymce.css'; ?>"
            });
            jQuery('#<?php echo $uniqid; ?>').val(Base64.encode(jQuery('#<?php echo $uniqid; ?>').val()));
        </script>
        <?php

        return ob_get_clean();
    }

}