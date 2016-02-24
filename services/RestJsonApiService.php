<?php

namespace Rootpress\services;

/**
 * RestJsonApiService class help you to create custom web service on your wordpress
 */
class RestJsonApiService {

	// API keyword
	public static $prefixApiKeyword;

	/**
	 * Start the service
	 */
    public static function startService() {

        // Add action parameter
        add_action('init', ['Rootpress\services\RestJsonApiService', 'addRewriteRulesTag']);

    	// Router system for API
    	add_action('template_include', ['Rootpress\services\RestJsonApiService', 'routerApiService'], 11);

    	// Handle 404 for API
    	add_action('template_include', ['Rootpress\services\RestJsonApiService', 'handle404'], 12);
    }

    /**
     * Add Rewrite Tag for custom rewrite URL
     */
    public static function addRewriteRulesTag() {
        add_rewrite_tag('%action%', '([^&]+)');
    }

    /**
     * Rooter api service
     */
    public static function routerApiService($template) {
    	global $wp_query;


    	// Template call by wordpress corresponding to the API template
    	if( basename($template, '.php') === 'page' && ( isset($wp_query->query) && isset($wp_query->query['pagename']) && ($wp_query->query['pagename'] == self::getApiKeyword()) )) {

    		// Then get the action
    		$action = (isset($wp_query->query_vars['action'])) ? $wp_query->query_vars['action'] : null;

    		// Error case, action null
    		if(is_null($action)) {
    			http_response_code(404);
    			$actionMissing = [
    				'success' => false,
					'errors'  => [
						'code'    => false,
						'message' => 'Error: You reach the api but it seam you forgot the action parameter'
					]
				];
				$actionMissing = apply_filters('rootpress_RestJsonApiService_output_action_missing', $actionMissing);
    			echo json_encode($actionMissing);
    			exit();
    		}

    		// Launch a wordpress action which allow controller to plug their logic here
    		do_action('controller_action_api_' . $action);
    	}
    	return $template;
    }

    /**
     * Handle 404 error case for REST Json Api
     */
    public static function handle404($template) {

    	// If wordpress try to print a 404 page and we are on an api URL, display as json rather than html
    	if(basename($template, '.php') === '404' && strpos($_SERVER['REQUEST_URI'], '/' . self::getApiKeyword() . '/') === 0) {

    		//Print the header 
	        header('Content-Type: application/json');
    		
    		// Test if page corresponding to the api endpoint page exist
    		$apiEndpointPage = get_page_by_title(self::getApiKeyword());
    		if(is_null($apiEndpointPage)) {

    			// Else create it
    			wp_insert_post([
    				'post_type'     => 'page',
    				'post_title'    => self::getApiKeyword(),
    				'post_status'   => 'publish'
    			]);
    			echo json_encode([
    				'success' => false,
					'errors'  => [
						'code'    => false,
						'message' => 'Error: Api Endpoint Page not found. We create it for you. Just refresh the page to access your web service.'
					]
				]);
    		}
    		else {

    			// Print 404 error
    			http_response_code(404);
    			$error404 = [
					'success' => false,
					'errors'  => [
						'code'    => 0001,
						'message' => 'Error 404: Web Service Not Found'
					]
				];
				$error404 = apply_filters('rootpress_RestJsonApiService_output_error_404', $error404);
				echo json_encode($error404);

    		}

    		// Exit to finish request
			exit();
    	}

        return $template;
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
