<?php

namespace Rootpress\visualcomposer\fields;

/** 
 * Multiple Relation Field for Visual Composer
 */
class MultipleRelationFieldVC {

    //Name of the field type
    public static $fieldName = 'Multiple Field';
    //Name use for declare the field which is the name of the shortcode
    public static $shortcodeName = 'vc_multiple_relation_field';
    //Empty value
    public static $emptyValue = "Aucune";
    //Query params
    public static $queryParams = [
        'posts_per_page'   => -1,
        'post_status'      => 'publish',
        'post_type'        => 'any'
    ];

    /**
     * Field form
     */
    public static function fieldForm($settings, $values) {
        ob_start();

        //Input params
        $paramNameEsc = esc_attr($settings['param_name']);
        $classString = 'wpb_vc_param_value wpb-relationinput ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_field';
        $uniqid = uniqid();
        $valuesIds = explode(',', $values);

        //Possible values
        $posts = get_posts(static::$queryParams); 

        //Sort by user order
        global $valuesGlobal;
        $valuesGlobal = $valuesIds;
        uasort($posts, array('MultipleRelationFieldVC', 'sortByUserOrder'));

        echo '<div class="relation_field_block ' . $uniqid . '">' .
                '<select multiple="multiple">';

                    foreach ($posts as $post) {
                        $selected = (in_array($post->ID, $valuesIds)) ? 'selected' : '';
                        echo '<option value="' . $post->ID . '" ' . $selected . '>' . $post->post_title . '</option>';
                    }

        echo    '</select>' . 
                //Input use for keep value
                '<input type="hidden" name="' . $paramNameEsc . '" value="' . $values . '" class="' . $classString . '" />';
              '</div>';

        //echo '<script type="text/javascript" src="' . get_stylesheet_directory_uri() . '/assets/js/lib/select2.sortable.js"></script>';
        ?>
        <script type="text/javascript">
            //Activate select in sorting mode which keep order 
            jQuery(".<?php echo $uniqid; ?> select").select2Sortable({
                sortableOptions: {
                    stop: function(e, ui) {
                        select2OrderField<?php echo $uniqid; ?>();
                    }
                }
            });
            jQuery(".<?php echo $uniqid; ?> select").on('change', function(){
                select2OrderField<?php echo $uniqid; ?>();
            });
            function select2OrderField<?php echo $uniqid; ?>() {
                var $ = jQuery;
                var data = $(".<?php echo $uniqid; ?> select").select2('data');
                var array = [];
                $.each(data, function(index, val) {
                    array[index]=val.id;
                });
                $(".relation_field_block.<?php echo $uniqid; ?> input[name=<?php echo $paramNameEsc; ?>]").val(array.join(','));
            }
        </script>
        <?php

        return ob_get_clean();
    }

    /**
     * Sort by User defined order
     */
    private static function sortByUserOrder($a, $b) {
        global $valuesGlobal;

        $posA = array_search($a->ID, $valuesGlobal);
        $posB = array_search($b->ID, $valuesGlobal);

        if($posA != false && $posB != false) {
            return ($posA > $posB) ? 1 : -1;
        }
        else if($posA != false && $posB == false) {
            return 1;
        }
        else if($posA == false && $posB != false) {
            return -1;
        }
        else {
            return ($a->ID < $b->ID) ? -1 : 1;
        }      
    }

}