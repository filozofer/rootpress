<?php

namespace Rootpress\services;

use \WP_CLI_Command;
use \WP_CLI;
use \Timber;

// Only load this service when WP_CLI is loaded
if(!class_exists('WP_CLI')) {
    return;
}

/**
 * WPCLIService class allow you to use Rootpress with WP CLI
 * @documentation http://wp-cli.org/docs/commands-cookbook/ && http://wp-cli.org/docs/internal-api/
 */
class WPCLIService extends WP_CLI_Command {

    /**
     * Start the service
     */
    public static function startService() {

        // Declare this class as a WP CLI Command class
        if (defined( 'WP_CLI' ) && WP_CLI) {
            WP_CLI::add_command('rootpress', 'Rootpress\services\WPCLIService');
        }
        else {
            throw new \Exception('Rootpress WPCLIService is enable but it seem you have not install WP_CLI. Please install WP_CLI or disable this service inside your rootpress-config.json.');
        }

        // Add Rootpress template folder to list of folders template using by Timber
        add_action('plugins_loaded', ['Rootpress\services\WPCLIService', 'declareTemplateFolderToTimber']);

    }

    /**
     * Generate files and folders for your theme
     *
     * ## OPTIONS
     *
     * <what>
     * : What do you want to generate ?
     * ---
     * options:
     *   - theme
     *   - sass
     *   - timber-twig
     *   - model
     *   - repository
     *   - controller
     *   - service
     * ---
     *
     * [--type=<type>]
     * : Allow to choose the model type you want to generate
     * ---
     * options:
     *   - custom-type
     *   - taxonomy
     * ---
     *
     * [--name=<name>]
     * : Allow to choose the class name of the genereated file
     *
     * [--custom-type=<custom-type>]
     * : Allow to choose the linked custom type to the generated file
     *
     * [--example]
     * : Generate example code. Use --no-example for opposite.
     *
     * ## EXAMPLES
     *
     *     wp rootpress generate theme
     *
     */
    public function generate($args, $assoc_args) {

        /**
         * This is the enter point for generate command
         * So, what do we need to generate ?
         */
        switch($args[0]) {

            // Generate theme
            case 'theme': $this->generateTheme($args, $assoc_args); break;

            // Generate sass
            case 'sass': $this->generateSass($args, $assoc_args); break;

            // Generate timber-twig
            case 'timber-twig': $this->generateTimberLayout($args, $assoc_args); break;

            // Generate model
            case 'model': $this->generateModel($args, $assoc_args); break;

            // Generate service
            case 'service': $this->generateService($args, $assoc_args); break;

            // Generate controller
            case 'controller': $this->generateController($args, $assoc_args); break;

            // Generate repository
            case 'repository': $this->generateRepository($args, $assoc_args); break;

            // Cannot happen
            default:
        }

    }

	/**
	 * Migration system
	 *
	 * ## OPTIONS
	 *
	 * <command>
	 * : Which sub-command to launch
	 * ---
	 * options:
	 *   - generate
	 *   - status
	 *   - version
	 *   - migrate
	 *   - execute
	 * ---
	 *
	 * [--set=<version>]
	 * : Allow to choose the version to set
	 *
	 *   :diff     Generate a migration by comparing your current database to your mapping information.
	 *   :execute  Execute a single migration version up or down manually.
	 *   :generate Generate a blank migration class.
	 *   :migrate  Execute a migration to a specified version or the latest available version.
	 *   :status   View the status of a set of migrations.
	 *   :version  Manually add and delete migration versions from the version table.
	 *
	 * ## EXAMPLES
	 *
	 *     wp rootpress migrations generate
	 *     wp rootpress migrations status
	 *     wp rootpress migrations version
	 *     wp rootpress migrations migrate
	 *     wp rootpress migrations execute 1000
	 *     wp rootpress migrations execute 1000 --down
	 *
	 */
    public function migrations($args, $assoc_args) {

	    /**
	     * This is the enter point for migrations command
	     */
	    switch($args[0]) {

		    // Generate theme
		    case 'migrate': $this->migrationsMigrate($args, $assoc_args); break;

		    // Version manager
		    case 'version': $this->migrationsVersion($args, $assoc_args); break;

		    // Not implemented yet
			case 'generate':
			case 'status':
			case 'execute':
			    throw new \Exception('Not implemented yet');

		    // Cannot happen
		    default:
	    }

    }

    /**
     * Generate a new basic theme with skeleton
     * @command wp rootpress generate theme
     */
    private function generateTheme($args, $assoc_args) {

        // Get list of themes
        $themes = wp_get_themes();
        array_walk($themes, function(&$item){
            $item = $item->get('Name');
        });

        // Ask to user all the needed informations
        $newTheme = [
            'name'          => WPCLIService::askToUser('What is your Theme name ?', 'ChildTheme'),
            'description'   => WPCLIService::askToUser('Theme description ?', ''),
            'author'        => WPCLIService::askToUser('Theme author ?', ''),
            'author_uri'    => WPCLIService::askToUser('Theme author URI ?', ''),
            'template'      => WPCLIService::askToUser('What is your parent theme ?', null, $themes),
            'version'       => WPCLIService::askToUser('What is your Theme version number ?', '1.0.0'),
        ];
        $newTheme['text_domain'] = WPCLIService::askToUser('What is your Theme text-domain ?', strtolower(str_replace(' ', '-', $newTheme['name'])));
        $newTheme['namespace'] = $newTheme['spaceless_name'] = str_replace(' ', '', $newTheme['name']);

        // Rootpress theme architecture
        $foldersArchitecture = [
            $newTheme['name'] => [
                'acf-json',
                'assets' => [
                    'css',
                    'img',
                    'js',
                    'fonts'
                ],
                'controllers',
                'models' => [
                    'customtypes',
                    'taxonomies'
                ],
                'repositories',
                'views'
            ]
        ];
        $foldersArchitecture = apply_filters('rootpress_generate_theme_architecture', $foldersArchitecture);

        // Verify if theme folder already exist
        $newThemePath = WP_CONTENT_DIR . '/themes/' . $newTheme['name'];
        if(is_dir($newThemePath)) {
            WP_CLI::warning('Theme folder already exist !');
            WPCLIService::askForUserCommit('Are you sure to execute this command ?', true, true);
        }

        // Create the new theme folders !
        $this->createFoldersArchitecture($foldersArchitecture, WP_CONTENT_DIR . '/themes');

        // Create style.css
        WPCLIService::generateFile($newThemePath . '/style.css', 'style.css.twig', $newTheme);

        // Create functions.php
        WPCLIService::generateFile($newThemePath . '/functions.php', 'functions.php.twig', $newTheme);

        // Put basic screenshot file
        copy(dirname(plugin_dir_path(__FILE__)) . '/templates/screenshot.png', WP_CONTENT_DIR . '/themes/' . $newTheme['name'] . '/screenshot.png');

        // Success !
        WP_CLI::success('Your new theme has been generated !');
    }


    /**
     * Generate SASS basic architecture
     * @command wp rootpress generate sass
     */
    private function generateSass($args, $assoc_args) {

        // Get current theme path
        $path = get_stylesheet_directory() . '/assets';

        // Generate folders for sass architecture
        $this->createFoldersArchitecture([
            'scss' => [
                'base',
                'components',
                'helpers',
                'layout',
                'pages'
            ]
        ], $path);

        // Generate all the needed files for basic sass architecture
        $this->generateFile($path . '/scss/base/_colors.scss', 'assets/scss/base/_colors.scss.twig');
        $this->generateFile($path . '/scss/base/_fonts.scss', 'assets/scss/base/_fonts.scss.twig');
        $this->generateFile($path . '/scss/base/_reset.scss', 'assets/scss/base/_reset.scss.twig');
        $this->generateFile($path . '/scss/helpers/_variables.scss', 'assets/scss/helpers/_variables.scss.twig');
        $this->generateFile($path . '/scss/helpers/_functions.scss', 'assets/scss/helpers/_functions.scss.twig');
        $this->generateFile($path . '/scss/helpers/_mediaqueries.scss', 'assets/scss/helpers/_mediaqueries.scss.twig');
        $this->generateFile($path . '/scss/layout/_layout.scss', 'assets/scss/layout/_layout.scss.twig');
        $this->generateFile($path . '/scss/layout/_header.scss', 'assets/scss/layout/_header.scss.twig');
        $this->generateFile($path . '/scss/layout/_footer.scss', 'assets/scss/layout/_footer.scss.twig');
        $this->generateFile($path . '/scss/main.scss', 'assets/scss/main.scss.twig');

        // Success !
        WP_CLI::success('Your basic Sass architecture has been generated !');
    }

    /**
     * Generate twig basic architecture for using Timber
     * @command wp rootpress generate timber-twig
     */
    private function generateTimberLayout($args, $assoc_args) {

        // Get current theme path
        $path = get_stylesheet_directory();

        // Basic twig layout
        $this->generateFile($path . '/views/layout.twig', 'views/layout.twig.twig');

        // Override default page template ?
        if(WPCLIService::askForUserCommit('Do you want to override default page template ?', 'y')) {
            $this->generateFile($path . '/page.php', 'page.php.twig');
            $this->generateFile($path . '/views/page.twig', 'views/page.twig.twig');
        }

        // Override header ?
        if(WPCLIService::askForUserCommit('Do you want to override theme header ?', 'y')) {
            $this->generateFile($path . '/header.php', 'header.php.twig');
            $this->generateFile($path . '/views/header.twig', 'views/header.twig.twig');
        }

        // Override footer ?
        if(WPCLIService::askForUserCommit('Do you want to override theme footer ?', 'y')) {
            $this->generateFile($path . '/footer.php', 'footer.php.twig');
            $this->generateFile($path . '/views/footer.twig', 'views/footer.twig.twig');
        }

        // Success !
        WP_CLI::success('Your basic twig architecture has been generated !');
    }

    /**
     * Generate a new model
     * @command wp rootpress generate model
     */
    private function generateModel($args, $assoc_args)
    {
        // Extract type from command param or ask user if not exist
        $which = (isset($assoc_args['type'])) ? $assoc_args['type'] : WPCLIService::askToUser('Generate model for custom type or taxonomy ?', 0, [
            'custom-type' => 'Custom Type',
            'taxonomy'  => 'Taxonomy'
        ]);

        // Generate the proper model (custom type or taxonomy)
        if($which === 'custom-type') {
            $config = $this->generateModelCT($args, $assoc_args);
        }
        else {
            $config = $this->generateModelT($args, $assoc_args);
        }

        // Ask if we need to generate a controller for that model
        if(WPCLIService::askForUserCommit('Generate a controller class for that model ?', 'y')) {
            WP_CLI::run_command(['rootpress', 'generate', 'controller'], [
                'name'      => $config['class_name'] . 'Controller',
                'example'   => (isset($assoc_args['example'])) ? $assoc_args['example'] : false
            ]);
        }

        // Ask if we need to generate a repository for that model
        if(WPCLIService::askForUserCommit('Generate a repository class for that model ?', 'y')) {
            WP_CLI::run_command(['rootpress', 'generate', 'repository'], [
                'name'          => $config['class_name'] . 'Repository',
                'custom-type'   => isset($config['posttype_name']) ? $config['posttype_name'] : $config['taxonomy_name'],
                'example'       => (isset($assoc_args['example'])) ? $assoc_args['example'] : false
            ]);
        }
    }

    /**
     * Generate a new custom type model
     * @command wp rootpress generate model --type=custom-type
     */
    private function generateModelCT($args, $assoc_args) {

        // Get current theme path
        $path = get_stylesheet_directory();

        // Get current theme
        $currentTheme = wp_get_theme();

        // Ask to user all the needed informations
        $newModel = [
            'namespace'     => str_replace(' ', '', $currentTheme->get('Name')),
            'text_domain'   => $currentTheme->get('TextDomain'),
            'class_name'    => WPCLIService::askToUser('Model class name ?'),
            'posttype_name' => WPCLIService::askToUser('Model post_type name ?'),
            'label_name'    => WPCLIService::askToUser('Model label name (generally plural) ?'),
            'description'   => WPCLIService::askToUser('Model description ?', ''),
            'menu_icon'     => WPCLIService::askToUser('Model menu icon class ?', 'dashicons-admin-post'),
            'translate'     => WPCLIService::askForUserCommit('Do you want to translate model wordings ?', 'n')
        ];

        // Labels custom ?
        if(WPCLIService::askForUserCommit('Do you want to customize labels ?', 'y')) {
            $newModel += [
                'labels' => [
                    'name' => WPCLIService::askToUser('name: (Posts)', ''),
                    'singular_name' => WPCLIService::askToUser('singular_name: (Post)', ''),
                    'menu_name' => WPCLIService::askToUser('menu_name: (Posts)', ''),
                    'parent_item_colon' => WPCLIService::askToUser('parent_item_colon: (Parent Post)', ''),
                    'all_items' => WPCLIService::askToUser('all_items: (All Posts)', ''),
                    'view_item' => WPCLIService::askToUser('view_item: (View Post)', ''),
                    'add_new_item' => WPCLIService::askToUser('add_new_item: (Add New Post)', ''),
                    'add_new' => WPCLIService::askToUser('add_new: (Add New)', ''),
                    'edit_item' => WPCLIService::askToUser('edit_item: (Edit Post)', ''),
                    'search_items' => WPCLIService::askToUser('search_items: (Search Posts)', ''),
                    'not_found' => WPCLIService::askToUser('not_found: (No Post found)', ''),
                    'not_found_in_trash' => WPCLIService::askToUser('not_found_in_trash: (No Post found in trash)', ''),
                ]
            ];
        }

        // Create the model
        WPCLIService::generateFile($path . '/models/customtypes/' . $newModel['class_name'] . '.php' , 'models/customtypes/model.twig', $newModel);

        // Success !
        WP_CLI::success('Your new model for ' . $newModel['class_name'] . ' has been generated !');
        return $newModel;
    }

    /**
     * Generate a new taxonomy model
     * @command wp rootpress generate model --type=taxonomy
     */
    private function generateModelT($args, $assoc_args) {

        // Get current theme path
        $path = get_stylesheet_directory();

        // Get current theme
        $currentTheme = wp_get_theme();

        // Ask to user all the needed informations
        $newModel = [
            'namespace'     => str_replace(' ', '', $currentTheme->get('Name')),
            'text_domain'   => $currentTheme->get('TextDomain'),
            'class_name'    => WPCLIService::askToUser('Taxonomy class name ?'),
            'taxonomy_name' => WPCLIService::askToUser('Taxonomy slug name ?'),
            'linked_post_type' => WPCLIService::askToUser('Taxonomy linked post type ?'),
            'translate'     => WPCLIService::askForUserCommit('Do you want to translate model wordings ?', 'n')
        ];

        // Labels custom ?
        if(WPCLIService::askForUserCommit('Do you want to customize labels ?', 'y')) {
            $newModel += [
                'labels' => [
                    'name' => WPCLIService::askToUser('name: (Categories)', ''),
                    'singular_name' => WPCLIService::askToUser('singular_name: (Category)', ''),
                    'all_items' => WPCLIService::askToUser('all_items: (All the categories)', ''),
                    'parent_item' => WPCLIService::askToUser('parent_item: (Parent Category)', ''),
                    'new_item_name' => WPCLIService::askToUser('new_item_name: (New Category Name)', ''),
                    'add_new_item' => WPCLIService::askToUser('add_new_item: (Add New Category)', ''),
                    'edit_item' => WPCLIService::askToUser('edit_item: (Edit Category)', ''),
                    'update_item' => WPCLIService::askToUser('update_item: (Update Category)', ''),
                    'view_item' => WPCLIService::askToUser('view_item: (View Category)', ''),
                    'add_or_remove_items' => WPCLIService::askToUser('add_or_remove_items: (Add or remove Category)', ''),
                    'choose_from_most_used' => WPCLIService::askToUser('choose_from_most_used: (Choose from the most used categories)', ''),
                    'popular_items' => WPCLIService::askToUser('popular_items: (Popular Categories)', ''),
                    'search_items' => WPCLIService::askToUser('search_items: (Search Categories)', ''),
                    'not_found' => WPCLIService::askToUser('not_found: (No categories found)', ''),
                ]
            ];
        }

        // Create the model
        WPCLIService::generateFile($path . '/models/taxonomies/' . $newModel['class_name'] . '.php' , 'models/taxonomies/taxonomy.twig', $newModel);

        // Success !
        WP_CLI::success('Your new model for ' . $newModel['class_name'] . ' has been generated !');
        return $newModel;
    }

    /**
     * Generate a new controller
     * @command wp rootpress generate controller
     */
    private function generateController($args, $assoc_args) {

        // Get current theme path
        $path = get_stylesheet_directory();

        // Get current theme
        $currentTheme = wp_get_theme();

        // Ask to user all the needed informations
        $newController = [
            'namespace'     => str_replace(' ', '', $currentTheme->get('Name')),
            'class_name'    => (isset($assoc_args['name'])) ? $assoc_args['name'] : WPCLIService::askToUser('Controller class name ?'),
            'example'       => (isset($assoc_args['example'])) ? $assoc_args['example'] : WPCLIService::askForUserCommit('Do you want example code inside ?', 'n'),
        ];

        // Create the controller
        WPCLIService::generateFile($path . '/controllers/' . $newController['class_name'] . '.php' , 'controllers/controller.twig', $newController);

        // Success !
        WP_CLI::success('Your new controller has been generated !');
    }

    /**
     * Generate a new repository
     * @command wp rootpress generate repository
     */
    private function generateRepository($args, $assoc_args) {

        // Get current theme path
        $path = get_stylesheet_directory();

        // Get current theme
        $currentTheme = wp_get_theme();

        // Ask to user all the needed informations
        $newRepository = [
            'namespace'     => str_replace(' ', '', $currentTheme->get('Name')),
            'class_name'    => (isset($assoc_args['name'])) ? $assoc_args['name'] : WPCLIService::askToUser('Repository class name ?'),
            'associate_custom_type' => (isset($assoc_args['custom-type'])) ? $assoc_args['custom-type'] : WPCLIService::askToUser('What is the associate post type ?'),
            'example'       => (isset($assoc_args['example'])) ? $assoc_args['example'] : WPCLIService::askForUserCommit('Do you want example code inside ?', 'n')
        ];

        // Create the repository
        WPCLIService::generateFile($path . '/repositories/' . $newRepository['class_name'] . '.php' , 'repositories/repository.twig', $newRepository);

        // Success !
        WP_CLI::success('Your new repository has been generated !');
    }

    /**
     * Generate a new service
     * @command wp rootpress generate service
     */
    private function generateService($args, $assoc_args) {

        // Get current theme path
        $path = get_stylesheet_directory();

        // Get current theme
        $currentTheme = wp_get_theme();
        $namespace = str_replace(' ', '', $currentTheme->get('Name'));

        // Ask to user all the needed informations
        $newService = [
            'namespace'     => $namespace,
            'class_name'    => WPCLIService::askToUser('Service class name ?')
        ];

        // Create services folder if not exist in theme
        if(!is_dir($path . '/services')) {
            mkdir($path . '/services');
        }

        // Create the service class file
        WPCLIService::generateFile($path . '/services/' . $newService['class_name'] . '.php' , 'services/service.twig', $newService);

        // Create or update the config file if user ask for it
        if(WPCLIService::askForUserCommit('Do you want to enable your new service ?', 'y')) {

            // Get actual rootpress config which contains the list of enable services
            $actualConfig = (file_exists($path . '/rootpress-config.json'))  ? json_decode(file_get_contents($path . '/rootpress-config.json'), true) : ['services' => []];

            // Add the new line to config
            $actualConfig['services'][$namespace . '\\services\\' . $newService['class_name']] = true;

            // Write config file updated
            file_put_contents($path . '/rootpress-config.json', json_encode($actualConfig, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0)));

        }

        // Success !
        WP_CLI::success('Your new service has been generated !');
    }


    /**
     * Create a folder architecture base on a array architecture
     * @param array $foldersArchitecture array of folders name
     * @param string $path Path of the folder in which we need to create the folders
     */
    private function createFoldersArchitecture($foldersArchitecture, $path) {

        // Writable condition
        if(!is_writable($path)) {
            WP_CLI::error('The theme folder is not writable: ' . $path);
        }

        // Create the folders recursively and pur .gitkeep file inside empty ones
        foreach($foldersArchitecture as $folderParent => $folder) {
            if(is_string($folder)) {
                if(!is_dir($path . '/' . $folder)) {
                    mkdir($path . '/' . $folder);
                }
                if(!file_exists($path . '/' . $folder . '/.gitkeep')) {
                    file_put_contents($path . '/' . $folder . '/.gitkeep', '');
                }
            }
            else if(is_array($folder)) {
                if(!is_dir($path . '/' . $folderParent)) {
                    mkdir($path . '/' . $folderParent);
                }
                $this->createFoldersArchitecture($folder, $path . '/' . $folderParent);
            }
        }
    }

    /**
     * Ask user for a value inside command line
     * @param string $question The text which will be appear in the console
     * @param mixed $default The default value to return if user just hit "Enter"
     * @param array $options List of options user can choose
     * @return null|sring
     */
    private static function askToUser($question, $default = null, $options = null) {

        // Preprend question with double arrow and append with a space
        $question = '>> ' . $question . ' ';

        // Tell user what is the default value
        if(!is_null($default) && !empty($default)) {
            $question .= '[' . $default . '] ';
        }

        // Ask the question
        fwrite(STDOUT, $question);

        // Print the list of options in which user can choose
        if(!is_null($options) && is_array($options)) {
            $optionsToIterate = array_values($options);
            fwrite(STDOUT, "\n");
            for($i = 0; $i < count($optionsToIterate); $i++) {
                fwrite(STDOUT, '[' . $i . '] ' . $optionsToIterate[$i] . "\n");
            }
        }

        // Get the answer
        do {
            // Warning message if user do not enter valid value
            if(isset($value)) {
                WP_CLI::warning('Please, enter a valid answer.');
            }

            // Get value from user input
            $value = trim(fgets(STDIN));

        } while(
            // Value must be in options array if options specify
            (!is_null($options) && is_array($options) && (!in_array($value, array_keys(array_values($options)))))
            // Or value as no default, user must answer
            || (empty($value) && is_null($default) && $value !== '0')
        );

        // Return option key if user choose from a list of options
        if(!is_null($options) && is_array($options)) {
            $arrayIndex = (!empty($value)) ? $value : $default;
            $value = array_keys($options)[(int) $arrayIndex];
        }

        // Return value
        return (!empty($value)) ? $value : $default;
    }

    /**
     * Ask for user commit before doing anything dangerous
     * @param boolean $mandatory [false|y|f] If false question user has to answer, if y or f user can hit enter and we take default value
     * @param boolean $exit if set to true and value return by user is equal to $expected then exit
     * @return boolean
     */
    private static function askForUserCommit($question, $mandatory = true, $exit = false)
    {
        // Preprend question with double arrow
        $question = '>> ' . $question;

        // Needed answer ?
        $neededAnswer = ' [y/n]';
        $neededAnswer = ($mandatory === 'y') ? ' [Y/n]' : $neededAnswer;
        $neededAnswer = ($mandatory === 'n') ? ' [y/N]' : $neededAnswer;
        $question .= $neededAnswer;

        // Print question and ask for answer
        fwrite(STDOUT, $question);
        do {
            $answer = strtolower(trim(fgets(STDIN)));
        } while($mandatory === true && ($mandatory && !in_array($answer, ['y', 'n'])));

        // Set to default if needed
        $answer = (!in_array($answer, ['y', 'n'])) ? $mandatory : $answer;

        // Deal with result
        if($exit && $answer == 'n') {
            exit;
        }
        else {
            return ($answer == 'y') ? true : false;
        }
    }

    /**
     * Generate a file inside theme folder from a twig template
     * @param string $fullPath the path to generate the file
     * @param string $templateName the twig template name locate inside templates folder
     * @param array $data the data use by Timber to generate the file
     */
    private static function generateFile($fullPath, $templateName, $data = null) {

        // Verify if Timber is present
        if(!class_exists('Timber')) {
            WP_CLI::error('Please install and enable Timber plugin to use this functionnality. https://fr.wordpress.org/plugins/timber-library/');
            exit();
        }

        // Generate the file content
        ob_start();
        Timber::render($templateName, $data);
        $fileContent = ob_get_clean();

        // File already exist ?
        if(file_exists($fullPath)) {
            WP_CLI::warning($fullPath . ' already exist.');
            if(WPCLIService::askForUserCommit('Do you want to erase this file ?') === false) {
                return;
            }
        }

        // Create the file
        if(file_put_contents($fullPath, $fileContent) === false) {
            WP_CLI::error('Cannot generated file: ' . $fullPath);
        }
        else {
            // Debug message
            WP_CLI::debug('File ' . $fullPath . ' generated.');
        }

    }

    /**
     * Declare the Rootpress Template folder
     */
    public static function declareTemplateFolderToTimber(){
        if(class_exists('Timber')) {
            Timber::$locations = (is_null(Timber::$locations)) ? [] : Timber::$locations;
            Timber::$locations = (is_string(Timber::$locations)) ? [Timber::$locations] : Timber::$locations;
            Timber::$locations = (!is_array(Timber::$locations)) ? [] : Timber::$locations;
            Timber::$locations[] = dirname(plugin_dir_path(__FILE__)) . '/templates';
        }
    }

	/**
	 * Launch the migrations process
	 */
    public static function migrationsMigrate(){

	    // Get all declare rootpress migrations
	    global $wp_filter;
	    $migrationsActions = array_keys(array_filter($wp_filter, function($value, $hook_key){
		    return strpos($hook_key, 'rootpress_migrations') !== false;
	    }, ARRAY_FILTER_USE_BOTH));
	    $migrations = [];
	    foreach($migrationsActions as $migration) {
		    $explodeName = explode('_', $migration);
		    $migrationNumber = (int) end($explodeName);
		    $migrations[$migrationNumber] = $migration;
	    }

	    // Find actual migrations number
	    $actualMigrationVersion = get_option('rootpress_migrations', 0);

	    // Search for the migrations we have not pass yet
	    $migrationsToLaunch = [];
	    $allDeclareMigrations = apply_filters('rootpress_migrations_list', []);
	    $migrationsNameToLaunch = [];
	    foreach ($migrations as $migrationNumber => $migration) {
		    if($migrationNumber > $actualMigrationVersion) {
			    $migrationsToLaunch[$migrationNumber] = $migration;
			    $migrationsNameToLaunch[] = '* ' . $allDeclareMigrations[$migrationNumber];
		    }
	    }

	    // Only continue if there is something to update else print the list
	    if(empty($migrationsToLaunch)) {
	    	WP_CLI::success('Nothing to migrate. Exit command');
		    exit();
	    }

	    // Display list
	    WP_CLI::line('The following migrations will be executed:');
	    foreach ($migrationsNameToLaunch as $migrationName) {
		    WP_CLI::line($migrationName);
	    }

	    // User commitment
	    WPCLIService::askForUserCommit('Are you sure to execute these migrations ?', true, true);

		// Launch all the migrations we have not pass yet
	    foreach ($migrationsToLaunch as $migrationNumber => $migration) {
		    do_action($migration, 'up');
		    WP_CLI::success('Migration number ' . $migrationNumber . ' done');
		    update_option('rootpress_migrations', $migrationNumber);
	    }

	    // Success !
	    WP_CLI::success('You are now at the migration version number: ' . get_option('rootpress_migrations', 0));

    }

	/**
	 * Manage Migration Version
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public static function migrationsVersion($args, $assoc_args) {

		// Print actual version
		$actualMigrationVersion = get_option('rootpress_migrations', 0);
		WP_CLI::line('Actual migration version number is ' . $actualMigrationVersion);

		// Ask wanted version
		$versionToSet = (isset($assoc_args['set'])) ? (int) $assoc_args['set'] : WPCLIService::askToUser('Migration version number to set ?');

		// Update migration version number
		update_option('rootpress_migrations', (int) $versionToSet);
		WP_CLI::success('Actual migration version number is now ' . ((int) $versionToSet));

	}

}
