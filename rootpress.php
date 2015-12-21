<?php

/*
 Plugin Name: Rootpress
 Plugin URI: 
 Description: All the roots you need to start a new wordpress custom project. Help developers to use MVC architecture inside wordpress.
 Author: Maxime Tual
 Text Domain: rootpress
 Domain path: /languages/
 Version: 0.1-alpha
*/

namespace Rootpress;

use LeVillage\controllers\BiduleTruc;

// Deploy the roots !
Rootpress::deployTheRoots();

/**
 * Main Rootpress class
 * This class contains all the basics hooks which allow to use an MVC like pattern
 */
class Rootpress
{
    // Rootpress configuration
    public static $configuration = [];

    // Current theme namespace guess during the load of rootpress configuration
    public static $currentThemeNamespace = null;

    // Associative array which containing the name of all customs types and theirs associates models
    public static $linkPostTypeToClass = [];

    /**
     * Start Rootpress functionalities
     */
    public static function deployTheRoots() {

        // Load Rootpress configuration
        self::loadRootpressConfiguration();

        // Autoloader System
        self::rootpressAutoload();

        // Controllers System
        add_filter('template_include', ['Rootpress\Rootpress', 'controllersSystem'], 99);
        self::declareControllersRouter();

        // Models System
        add_action('init', ['Rootpress\Rootpress', 'modelsSystem'], 99);
        add_filter('rootpress_before_hydrate', ['Rootpress\Rootpress', 'getEntityFromWPPost'], 99);

        // Launch Rootpress Services according to rootpress configuration
        self::loadServices();

        /*
         Finish Skeleton
         Views
         Templates
        */
    }

    /**
     * Load Rootpress Config  
     * @filter rootpress_config_file_location Allow to override the location of the rootpress config file
     */
    public static function loadRootpressConfiguration() {

        // Get config from Rootpress plugin
        self::$configuration = json_decode(file_get_contents(plugin_dir_path(__FILE__) . '/rootpress-config.json'), true);

        // Get config from actual theme if developer have put a config file in their theme to override rootpress-config and merge them to rootpress config
        $defaultRootpressConfigFileLocation = get_stylesheet_directory() . '/rootpress-config.json';
        $defaultRootpressConfigFileLocation = apply_filters('rootpress_config_file_location', $defaultRootpressConfigFileLocation);
        if(file_exists($defaultRootpressConfigFileLocation)) {
            self::$configuration = array_replace_recursive(self::$configuration, json_decode(file_get_contents($defaultRootpressConfigFileLocation), true));
        }

    }

    /**
     * Getter to get the current theme namespace
     * @filter rootpress_current_theme_namespace Allow to override the actual theme/child theme namespace
     */
    public static function getCurrentThemeNamespace() {
        if(!isset(self::$currentThemeNamespace)) {
            $currentTheme = wp_get_theme();
            self::$currentThemeNamespace = (is_object($currentTheme)) ? str_replace(' ', '', $currentTheme->get('Name')) : 'CustomTheme';
            self::$currentThemeNamespace = apply_filters('rootpress_current_theme_namespace', self::$currentThemeNamespace);
        }
        return self::$currentThemeNamespace;
    }

    /**
     * Add a function to the autoload php system pile which allow to found a class php for which php haven't include the class file for the moment
     * @filter rootpress_namespaces_list Allow to override the list of namespaces handle by rootpress
     */
    public static function rootpressAutoload() {

        // Associative array of namespace and associate root folder
        $namespaces = [
            __NAMESPACE__          => plugin_dir_path( __FILE__ ),
            self::getCurrentThemeNamespace() => get_stylesheet_directory()
        ];
        // Allow developers to alterate the namespace list handle by this autoloader and declare the namespaces list as global to be use inside autoload function
        global $rootpress_namespaces_list;
        $rootpress_namespaces_list = apply_filters('rootpress_namespaces_list', $namespaces);

        // Register the rootpress autoload function
        spl_autoload_register(function($class_name) {
            // Get list of declare namespace
            global $rootpress_namespaces_list;

            // Verify if the class we are looking for is in one of our defined namespace
            foreach($rootpress_namespaces_list as $namespace => $rootDir) {
                if(strpos($class_name, $namespace) === 0) {

                    // If it's the case, look for a file base on the class_name ask and the namespace
                    $file = str_replace('\\', '/', $rootDir . '/' . str_replace($namespace . '\\', '', $class_name) . '.php');
                    if (file_exists($file)) {

                        // Then include the file which allow the class to be found (if class and namespace are properly declare inside that file of course)
                        require_once $file;
                        return true;

                    }
                }
            }
        });
    }

    /**
     * Controllers System Init
     * Fire an action 'controller_action_{template_filename}' just after template is selected to render a page which allow to have controller system base on template
     */
    public static function controllersSystem($template_path) {
        $template_filename = basename($template_path, '.php');
        do_action('controller_action_' . $template_filename);
        return $template_path;
    }

    /**
     * Call router method for each controller which allow to declare the mapping of templates and controller
     * @filter rootpress_controller_folders_list Override the list of folders containing controllers files
     * @filter rootpress_controller_files_list Override the list of controllers files
     * @filter rootpress_controller_class_name_list Override the class name guessing method
     */
    public static function declareControllersRouter() {

        // Get list of controllers folders
        $controllersDefaultFolderPaths = [get_stylesheet_directory() . '/controllers' => self::getCurrentThemeNamespace() . '\controllers'];

        //Load all controllers class and then start them
        self::loadFilesFromPaths('controller', $controllersDefaultFolderPaths, function($classPath){
            // Call router method for each controller
            if(method_exists($classPath, 'router')) {
                $classPath::router();
            }
        });
    }

    /**
     * Models System Init
     * @filter rootpress_model_folders_list Override the list of folders containing models files
     * @filter rootpress_model_files_list Override the list of models files
     * @filter rootpress_model_class_name_list Override the class name guessing method
     */
    public static function modelsSystem() {

        // Get list of models folders
        $modelsDefaultFolderPaths = [
            get_stylesheet_directory() . '/models/customtypes' => self::getCurrentThemeNamespace() . '\models\customtypes',
            get_stylesheet_directory() . '/models/taxonomies' => self::getCurrentThemeNamespace() . '\models\taxonomies'
        ];

        //Load all models class
        self::loadFilesFromPaths('model', $modelsDefaultFolderPaths, function($classPath){

            // Call the model declaraction method if exist and keep trace of association between custom type and class
            if (method_exists($classPath, 'customTypeDeclaration')) {
                $classPath::customTypeDeclaration();
                Rootpress::$linkPostTypeToClass[$classPath::$linked_post_type] = $classPath;
            } 
            elseif (method_exists($classPath, 'taxonomyDeclaration')) {
                $classPath::taxonomyDeclaration();
                Rootpress::$linkPostTypeToClass[$classPath::$linked_taxonomy] = $classPath;
            }

        });
    }

    /**
     * Transform a WP Post to an entity by using the associative array self::linkPostTypeToClass which have been populate during models declaration
     * This method is call at the beginning of the hydrate process by Hydratator using a filter
     * @param $object WP_Post object to convert into model
     */
    public static function getEntityFromWPPost($object) {
        $WPC = null;

        // Transforme the object to one of the declare model if we found his associate class
        if (isset($object->post_type) && isset(self::$linkPostTypeToClass[$object->post_type])) {
            $WPC = new self::$linkPostTypeToClass[$object->post_type]();
        } 
        elseif (isset($object->taxonomy) && isset(self::$linkPostTypeToClass[$object->taxonomy])) {
            $WPC = new self::$linkPostTypeToClass[$object->taxonomy]();
        }

        // Importe all the key in the new object model
        if ($WPC != null) {
            foreach ($object as $key => $value) {
                $WPC->$key = $value;
            }
            // Return the new object model
            return $WPC;
        }

        // No filter has been executed, return the same object we got
        return $object;
    }

    /**
     * Load Services declare in options file and start theme
     */
    public static function loadServices() {

        // Get list of service folders
        $servicesDefaultFolderPaths = [plugin_dir_path(__FILE__) . 'services' => 'Rootpress\services'];

        //Load all services class and then start them
        self::loadFilesFromPaths('service', $servicesDefaultFolderPaths, function($classPath){

            // Is this service is enable inside rootpress configuration ?
            if(isset(self::$configuration['services']) && isset(self::$configuration['services'][$classPath]) && (self::$configuration['services'][$classPath] || is_array(self::$configuration['services'][$classPath]))) {

                // Call startService method for each service
                if(method_exists($classPath, 'startService')) {
                    $classPath::startService();
                }
                else {
                    throw new \Exception('Error during Rootpress services loading. The service class ' . $classPath . ' seem to be enable in your rootpress configuration file but the method "startService" cannot be found inside that class.', 1);
                }

            }
        });
        
    }

    /**
     * Get Rootpress dir path
     */
    public static function getRootpressDirPath() {
        return plugin_dir_path(__FILE__);
    }

    /**
     * Get list of class from folders list and execute a call back on all this class
     * @param $filterKeyword string keyword use in apply_filters of this function
     * @param $foldersPaths array associative array [folder path => associate namespace, ...]
     * @param $callback function callback to launch on each class
     * @filter rootpress_[$filterKeyword]_folders_list Override the list of folders containing files to load
     * @filter rootpress_[$filterKeyword]_files_list Override the list of files to load
     * @filter rootpress_[$filterKeyword]_class_name_list Override the class name guessing method
     */
    public static function loadFilesFromPaths($filterKeyword, $foldersPaths, $callback) {

        // Filter the list of folders
        $foldersPaths = apply_filters('rootpress_' . $filterKeyword . '_folders_list', $foldersPaths);

        // Get list of files paths from the folders
        $filesPaths = [];
        foreach ($foldersPaths as $folderPath => $namespace) {
            $files = glob($folderPath . '/*.php');
            foreach ($files as $filePath) {
                $filesPaths[$filePath] = $namespace;
            }
        }
        // Override the list of filepath
        $filesPaths = apply_filters('rootpress_' . $filterKeyword . '_files_list', $filesPaths);

        // Guess class list by using file name
        $classNames = [];
        foreach ($filesPaths as $filePath => $namespace) {
            $classNames[basename($filePath, '.php')] = $namespace;
        }
        // Override the class name guessing method
        $classNames = apply_filters('rootpress_' . $filterKeyword . '_class_name_list', $classNames);

        // Launch callback on each class if class exist
        foreach ($classNames as $class => $namespace) {
            $classFullNamespace = $namespace . '\\' . $class;
            if(class_exists($classFullNamespace)) {
                $callback($classFullNamespace);
            }
        }
    }

}
