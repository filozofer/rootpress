<?php

namespace Rootpress\services;

use Rootpress\Rootpress;

/**
 * VisualComposerService class help you to create custom web service on your wordpress
 * Possible Amelioration: Allow to disable the default visual composer widget rather than disable access to them inside backoffice
 */
class VisualComposerService {

	/**
	 * Start the service
	 */
    public static function startService() {

        // Declare Visual Composer Custom Widgets
        add_action('init', ['Rootpress\services\VisualComposerService', 'declareVisualComposerWidgets']);

        // Declare Visual Composer Custom Fields for widgets
        add_action('init', ['Rootpress\services\VisualComposerService', 'declareVisualComposerFields']);
        
    }

    // Keep trace of error message
    public static $errorMessageAlreadyPrint = false;

    /**
     * Allow to declare visual composer widgets in the project
     * @param $widgetDeclaration array of string, each string is the name of an entity which represent a vc widget
     */
    public static function declareVisualComposerWidgets() {

        // Check for visual composer
        if(!self::checkIfVisualComposerIsEnable()) { return; }

        // Get list of widgets folders
        $widgetsDefaultFolderPaths = [
            get_stylesheet_directory() . '/visualcomposer/widgets' => Rootpress::getCurrentThemeNamespace() . '\visualcomposer\widgets'
        ];

        //Load all widgets class and then start them
        Rootpress::loadFilesFromPaths('visualcomposer_widget', $widgetsDefaultFolderPaths, function($classPath){
            // Call declareAsVisualComposerWidget method for each widget
            if(method_exists($classPath, 'declareAsVisualComposerWidget')) {
                $classPath::declareAsVisualComposerWidget();
            }
        });
    }

    /**
     * Allow to declare custom visual composer field for widgets
     */
    public static function declareVisualComposerFields() {

        // Check for visual composer
        if(!self::checkIfVisualComposerIsEnable()) { return; }

        // Get list of visual composer fields folders
        $widgetsFieldsDefaultFolderPaths = [
            Rootpress::getRootpressDirPath() . '/visualcomposer/fields' => 'Rootpress\visualcomposer\fields',
            get_stylesheet_directory() . '/fields' => Rootpress::getCurrentThemeNamespace() . '\visualcomposer\fields'
        ];

        //Load all visual composer fields class and then declare them as shortcode
        Rootpress::loadFilesFromPaths('visualcomposer_widget', $widgetsFieldsDefaultFolderPaths, function($classPath){

            // Look inside configuration if this field is enable
            $addField = true;
            if(isset(Rootpress::$configuration['services']['Rootpress\\services\\VisualComposerService']['fields']) && !in_array($classPath, Rootpress::$configuration['services']['Rootpress\\services\\VisualComposerService']['fields'])) {
                $addField = false;
            }

            // Only declare field wanted
            if($addField) {
                add_shortcode_param($classPath::$shortcodeName, [$classPath, 'fieldForm']);
            }

        });
    }

    /**
     * Allow to check if visual composer is enable
     */
    private static function checkIfVisualComposerIsEnable() {
        
        // Check if visual composer is enable
        if(!\is_plugin_active('js_composer/js_composer.php')) {
            if(!self::$errorMessageAlreadyPrint) {
                add_action( 'admin_notices', function() {
                    echo '<div class="notice error"><p>You have enable the RootPress service "VisualComposerService" but you haven\'t enable the plugin "Visual Composer". Please enable the plugin or disable this service.</p></div>'; 
                }); 
                self::$errorMessageAlreadyPrint = true;
            }
            return false;
        }
        else {
            // Everything is ok !
            return true;
        }
    }

}
