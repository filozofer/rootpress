<?php

namespace Rootpress\services;

/**
 * RestJsonApiService class help you to create custom web service on your wordpress
 */
class RestJsonApiService {

	// API keyword


	/**
	 * Start the service
	 */
    public static function startService() {

    	// Handle 404 for API
    	add_action('template_include', ['Rootpress\services\RestJsonApiService', 'handle404']);
    }

    /**
     *
     */
    public static function handle404($template) {

    	// YOU ARE HERE, trying to handle 404 for Rest API
    	// IF 404 > check if api file exist, if not create it and display mesage
    	// If yes just display 404 error with filter please !
    	// Then look inside rootpress.php for what's next

    	// If we are on
    	if(basename($template, '.php') === '404' && strpos($_SERVER['REQUEST_URI'], '/' . self::getApiKeyword() . '/') === 0) {

    	}

    	//If it's a request for the api response must be in json
		if(strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {

			//Print the header 
	        header('Content-Type: application/json');
	        http_response_code(404);
			echo json_encode([
				'success' => 'false',
				'errors'  => [
					'code'    => 0003,
					'message' => 'Error 404: Web Service Not Found'
				]
			]);
			exit();
		}

	    if( is_page( 'goodies' ) && ! is_user_logged_in() )
	    {
	        wp_redirect( home_url( '/signup/' ) );
	        exit();
	    }
	}

	/**
	 * Get the API keyword use as prefix inside the URL
	 */
	private static function getApiKeyword() {

	   	// Get API Keyword if is null
		if(is_null(self::$prefixApiKeyword)) {
	    	$defaultPrefixApiKeyword = 'api';
	    	self::$prefixApiKeyword = apply_filters('rootpress_RestJsonApiService_api_keyword', $defaultPrefixApiKeyword);

	    }

	    // Return it
	    return self::$prefixApiKeyword;
	}

}
