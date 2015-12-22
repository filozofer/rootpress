<?php

namespace ChangeThisToYourThemeName\controllers;

use ChangeThisToYourThemeName\repositories\SampleRepository;

/**
 * SampleController
 * Use as example of rootpress controller
 */
class SampleController {

    /**
     * Rooter function for this controller
     */
    public static function router() {

        //Register Actions
        add_action('controller_action_single-sample', ['SampleController', 'singleSampleAction']);
        add_action('controller_action_list-sample', ['SampleController', 'listSampleAction']);

        //Rewrite rules
        add_action('init', ['SampleController', 'rewriteRulesSampleController']);
    }

    /***********
     * Actions *
     ***********/

    /**
     * Action for single Sample
     */
    public static function singleSampleAction() {
        global $data;
        global $post;

        //Create timber context
        $data = Timber::get_context();

        //Get current post (needed if you wanted to print the content of the post type by interpreted shortcodes)
        $data['post'] = new TimberPost();

        //Get current sample
        $sample = SampleRepository::getInstance()->findOne($post->ID);
        $data['sample'] = $sample;
    }

    /**
     * Action for list of samples
     */
    public static function listSampleAction() {
        global $data;
        global $post;

        // Create timber context
        $data = Timber::get_context();

        // Get params
        $order = (isset($_GET['sample_order'])) ? $_GET['sample_order'] : 'desc';
        $pageNumber = (isset($_GET['sample_page_number'])) ? (int) $_GET['sample_page_number'] : 1;

        // Get list of samples
        $samples = SampleRepository::getInstance()->findAll($order, $pageNumber);
        $data['samples'] = $samples;
    }

    /**
     * Rewrite URL rules for this controller
     */
    public function rewriteRuleListNewsAction() {
        global $wp_rewrite;

        //Declare param
        add_rewrite_tag('%sample_order%', '([^&]+)');
        add_rewrite_tag('%sample_page_number%', '([0-9]+)');

        //Add Rewrite rule$
        $wp_rewrite->add_rule('samples/([^/]+)/([^/]+)', 'index.php?pagename=samples&sample_order=$matches[1]&sample_page_number=$matches[2]', 'top');

        //Flush to validate rewrite rule (only uncomment this when you add a new rule, refresh your wordpress site, then comment this) > Performance advice
        //$wp_rewrite->flush_rules();
    }

}
