<?php

namespace Rootpress\utils;

/**
 * TwigTimberUtils
 * Use this class as template for create your own TwigTimberUtils class
 */
class TwigTimberUtils extends \Twig_Extension {

    /**
     * Call this function from your main class to start extend Twig
     */
    public static function extendTwig() {
        
        // Hook to extend Twig
        add_filter('get_twig', ['Rootpress\utils\TwigTimberUtils', 'addToTwig']);
    }

    /**
     * Add filters and functions to Twig
     */
    public static function addToTwig($twig) {
        
        // Add this class as a twig extension
    	$twig->addExtension(new TwigTimberUtils());

		// Return Twig now we finish to extend it
		return $twig;
    }

    public function getName() {
        return 'TwigTimberUtils';
    }

    /**
     * Get list of functions to add to twig
     */
    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('helloworld', ['Rootpress\utils\TwigTimberUtils', 'helloworld'])
        ];
    }

    /**
     * Get list of filters to add to twig
     */
    public function getFilters() {
        return [
            new \Twig_SimpleFilter('foo', ['Rootpress\utils\TwigTimberUtils', 'foo'])
        ];
    }

    /**
     * Example function helloworld
     */
    public static function helloworld($name = 'World') {
        return 'Hello ' . $name . '!'; 
    }

    /**
     * Example filter foo
     */
    public static function foo($text) {
    	return $text . 'foo';
    }
}
