<?php

/*
 Plugin Name: Rootpress
 Plugin URI: 
 Description: All the roots you need to start a new wordpress custom project. Help developers to use MVC architecture inside wordpress.
 Author: Maxime Tual
 Text Domain: rootpress
 Domain path: /languages/
 Version: 0.3.0-beta
*/

namespace Rootpress;

// Deploy the roots !
use Rootpress\models\RootpressModel;
use Rootpress\models\RootpressUser;
use Rootpress\models\WP_Page;
use WP;

Rootpress::deployTheRoots();

/**
 * Main Rootpress class
 * This class contains all the basics hooks which allow to use an MVC like pattern
 */
class Rootpress
{

    // Current theme namespace guess during the load of rootpress configuration
    public static $currentThemeNamespace = null;

    // Associative array which containing the name of all customs types and theirs associates models
    public static $linkPostTypeToClass = [];

    /**
     * Start Rootpress functionalities
     */
    public static function deployTheRoots() {

        // Autoloader System
        self::rootpressAutoload();

        // Controllers System
        add_filter('template_include', ['Rootpress\Rootpress', 'controllersSystem'], 99);
        add_action('get_header', ['Rootpress\Rootpress', 'controllersSystemHeader'], 99);
        add_action('get_footer', ['Rootpress\Rootpress', 'controllersSystemFooter'], 99);
        self::declareControllersRouter();

        // Models System
        add_action('init', ['Rootpress\Rootpress', 'modelsSystem'], 99);

	    // Load migrations
        add_action('init', ['Rootpress\Rootpress', 'loadMigrations'], 99);

        // Launch Rootpress Hooks according to rootpress configuration
        add_action('init', ['Rootpress\Rootpress', 'loadHooks'], 1);

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
	 *
	 * @param string $template_path
	 *
	 * @return mixed
	 */
    public static function controllersSystem($template_path) {
        $template_filename = basename($template_path, '.php');
        do_action('controller_action_' . $template_filename);
        return $template_path;
    }
    public static function controllersSystemHeader() {
        do_action('controller_action_header');
    }
    public static function controllersSystemFooter() {
        do_action('controller_action_footer');
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
            plugin_dir_path(__FILE__)  . 'models' => 'Rootpress\models',
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
	 * Transform a WP Object to an entity by using the associative array self::linkPostTypeToClass which have been populate during models declaration
	 * This method must be call inside your repositories to convert your WP_Post / WP_User / ..  into your proper model
     * Exemple: A WP_Post with a post_type "book" become a Book entity
	 *
	 * @param Object|Object[] $object WP object to convert into model
	 *
	 * @return null|RootpressModel|RootpressUser
	 */
    public static function getEntityFromWPPost($object) {

        // Handle array and single case
        $WP_Entities = (is_array($object)) ? $object : [$object];
        $builtEntities = [];
        foreach($WP_Entities as $WP_Entity) {

            // Built entity
            $entity = null;

            // Transform the object to one of the declare model if we found his associate class
            if (isset($WP_Entity->post_type) && isset(self::$linkPostTypeToClass[$WP_Entity->post_type])) {
                $entity = new self::$linkPostTypeToClass[$WP_Entity->post_type]();
            }
            elseif (isset($WP_Entity->taxonomy) && isset(self::$linkPostTypeToClass[$WP_Entity->taxonomy])) {
                $entity = new self::$linkPostTypeToClass[$WP_Entity->taxonomy]();
            }
            else if($WP_Entity !== null && get_class($WP_Entity) === 'WP_User' && isset(self::$linkPostTypeToClass['WP_User'])) {
                $entity = new self::$linkPostTypeToClass['WP_User']();
            }

            // Import all the key in the new object model
            if ($entity != null) {
                foreach ($WP_Entity as $key => $value) {
                    $entity->$key = $value;
                }

                // Call the construct method to init ACF Fields
                $entity->construct();
            }
            // No filter has been executed, return the same object we got
            else {
                $entity = $WP_Entity;
            }

            // Keep entity converted
            $builtEntities[] = $entity;
        }

        // Return the entity/ entities
        return (is_array($object)) ? $builtEntities : $builtEntities[0];

    }

    /**
     * Load Hooks declare in options file and start theme
     */
    public static function loadHooks() {

        // Get list of hook folders
        $hooksDefaultFolderPaths = [
            plugin_dir_path(__FILE__) . 'hooks' => 'Rootpress\hooks',
            get_stylesheet_directory() . '/hooks' => self::getCurrentThemeNamespace() . '\hooks'
        ];

        //Load all hooks class and then start them
        self::loadFilesFromPaths('hook', $hooksDefaultFolderPaths, function($classPath){

            // Call startService method for each hook
            if(method_exists($classPath, 'hooks')) {
                $classPath::hooks();
            }
            else {
                throw new \Exception('Error during Rootpress hooks loading. The hook class ' . $classPath . ' has been founded but the method "hooks" cannot be found inside that class.', 1);
            }

        });
        
    }

	/**
	 * Load migrations declare in options file and theme
	 */
	public static function loadMigrations() {

		// Get list of migration folders
		$migrationsDefaultFolderPaths = [
			get_stylesheet_directory() . '/migrations' => self::getCurrentThemeNamespace() . '\migrations'
		];

		//Load all migrations class and then allow them to declare them self
		self::loadFilesFromPaths('controller', $migrationsDefaultFolderPaths, function($classPath){
			// Call declareMigration method for each migration class
			if(method_exists($classPath, 'declareMigration')) {
				$classPath::declareMigration();
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
     * @param string $filterKeyword keyword use in apply_filters of this function
     * @param array $foldersPaths associative array [folder path => associate namespace, ...]
     * @param callable $callback callback to launch on each class
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

    /**
     * Plugin Activation hook function to check for Minimum PHP and WordPress versions
     * @param string $wp Minimum version of WordPress required for this plugin
     * @param string $php Minimum version of PHP required for this plugin
     */
    public static function rootpressActivation($wp = '4.0', $php = '5.2.4') {
        global $wp_version;

        // Compare version
        if(version_compare(PHP_VERSION, $php, '<')) {
            $flag = 'PHP';
        }
        elseif(version_compare($wp_version, $wp, '<' )) {
            $flag = 'WordPress';
        }
        else { return; }

        // Disable plugin and display error message 
        deactivate_plugins( basename( __FILE__ ) );
        $version = 'PHP' == $flag ? $php : $wp;
        wp_die('<p><strong>Rootpress</strong> plugin requires'. $flag .' version '. $version .' or greater.</p>','Plugin Activation Error',  ['response' => 200, 'back_link' => TRUE]);
    }

}

// Register activation hook
register_activation_hook( __FILE__,  ['Rootpress\Rootpress', 'rootpressActivation']);