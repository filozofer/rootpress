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
     *   - timber-layout
     *   - model
     *   - repository
     *   - controller
     *   - service
     * ---
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

            // Cannot happen
            default:
        }

    }

    /**
     * Debug function
     *
     * ## EXAMPLES
     *
     *     wp rootpress playground
     *
     */
    public function playground($args, $assoc_args) {


        // Success !
        WP_CLI::success('END OF Playground !');
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
        $newTheme['namespace'] = $newTheme['spaceless_name'] = str_replace(' ', '', $newTheme['name']);

        // Rootpress theme architecture
        $foldersArchitecture = [
            $newTheme['name'] => [
                'acf-json',
                'assets' => [
                    'css',
                    'img',
                    'js'
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

        // Preprend question with double arrow
        $question = '>> ' . $question;

        // Tell user what is the default value
        if(!is_null($default) && !empty($default)) {
            $question .= ' [' . $default . ']';
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
        $neededAnswer = ($mandatory === 'f') ? ' [y/N]' : $neededAnswer;
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

}
