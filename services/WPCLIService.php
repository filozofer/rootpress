<?php

namespace Rootpress\services;

use \WP_CLI_Command;
use \WP_CLI;

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
     * Generate a new basic theme with skeleton
     * @command wp rootpress generate theme
     */
    private function generateTheme($args, $assoc_args) {

        // Init new theme
        $newTheme = [];

        // Ask user for Theme name
        $newTheme['name'] = WPCLIService::askToUser('What is your Theme name ?', 'ChildTheme');

        // Dump
        var_dump($newTheme);

        // You win ?
        var_dump(WPCLIService::askForUserCommit('Did you win ?'));

        // Success !
        WP_CLI::success('Your new theme has been generated !');
    }

    /**
     * Ask user for a value inside command line
     * @param string $question The text which will be appear in the console
     * @param null $default The default value to return if user just hit "Enter"
     * @return null|sring
     */
    private static function askToUser($question, $default = null) {

        // Preprend question with double arrow
        $question = '>> ' . $question;

        // Tell user what is the default value
        if(!is_null($default)) {
            $question .= ' [' . $default . ']';
        }

        // Ask the question
        fwrite(STDOUT, $question);
        $value = trim(fgets(STDIN));

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


}
