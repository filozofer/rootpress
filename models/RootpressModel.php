<?php

namespace Rootpress\models;

/**
 * Rootpress Model for transversal function to models
 * This abstract model can be use as template for your own abstract parent model
 */
abstract class RootpressModel {

	/** @var $ID int */
	public $ID = 0;
	public $post_type = '';
	public static $linked_post_type = '';

    /**
     * Associative array of the entity advanced custom fields
     * Must follow : field_name => field_key
     * @var array
     */
    public static $acf_mapping = [
    ];

    /**
     * Associative array of the entity advanced custom fields define dynamically
     * Must follow : field_name => field_key
     * @var array
     */
    public $acf_mapping_dynamic = [
    ];

	/**
	 * RootpressModel constructor
	 * Set the post_type of the model
	 */
	public function __construct() {
		$this->post_type = static::$linked_post_type;
	}

	/**
	 * Constructor for this model
	 * Override this function in your child class to do post treatement after hydratation
	 */
	public function construct() {

	    // Declare each ACF fields on the entity
	    foreach (static::$acf_mapping as $fieldName => $fieldKey) {
	        $this->set($fieldName, new LazyACFLoader($fieldKey, $fieldName, (isset($this->taxonomy)) ? $this->taxonomy . '_' . $this->term_id : $this->ID));
        }

	}

	/**
	 * Hydrate object from array
	 *
	 * @param array $attributes
	 */
	public function hydrate( array $attributes ) {

		// Set all the field from $data
		foreach ( $attributes as $fieldName => $fieldValue ) {
			$this->set( $fieldName, $fieldValue );
		}

	}

	/**
	 * Generic getter
	 * Prefer using this to access your attribute
	 *
	 * @param string $paramName
	 *
	 * @return mixed
	 */
	public function get( $paramName ) {

	    // Lazy load the ACF field value
		if (isset($this->$paramName) && is_a($this->$paramName, LazyACFLoader::class)) {
			$this->$paramName = $this->$paramName->getValue();
		}

		// Verify if getter method exist for this attribute and avoid calling it again if it's already the getter which have call this method
		$getter = 'get' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $paramName ) ) );
		if ( method_exists( $this, $getter ) && $getter != debug_backtrace()[1]['function']) {
			return $this->$getter();
		}

		// Return attribute value default behaviour
		return $this->$paramName;

	}

	/**
	 * Generic setter
	 * Prefer using this to change value of your attribute
	 *
	 * @param string $paramName
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function set( $paramName, $value ) {

	    // Verify if setter method exist for this attribute
		$setter = 'set' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $paramName ) ) );
		if ( method_exists( $this, $setter ) ) {
			return $this->$setter( $value );
		}

		// Set value default method
		$this->$paramName = $value;

		// Return $this to allow chain set call if needed
		return $this;

	}

	/**
	 * Magic Method to call the generics getter and setter when accessing a private attribute of your class
	 * If you want to respect encapsulation rules you need to declare all your object fields as private attribute inside your child class
	 * When the hydrate process will try to hydrate your field, these magic function will call the generics getter and setter
	 */

	/**
	 * Magic getter
	 *
	 * @param $name
     * @return mixed
	 */
	public function __get( $name ) {
		return $this->get( $name );
	}

    /**
     * Magic setter
     *
     * @param $name
     * @return $this
     */
	public function __set( $name, $value ) {
        return $this->set( $name, $value );
	}

    /**
     * Magic isset
     *
     * @param $name
     * @return boolean
     */
	public function __isset( $name ) {

	    // Verify if attribut exist
	    if (isset($this->$name)) {
            return TRUE;
        }

        // Or if getter exist
        $getter = 'get' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $name ) ) );
        if ( method_exists( $this, $getter )) {
            return TRUE;
        }

        // Not exist
        return FALSE;

    }

    /**
     * Load all the ACF fields values
     * (mostly for debug purpose ! Lazy process is here for performance reason)
     *
     * @return RootpressModel
     */
    public function loadACF() {

        // Declare each ACF fields on the entity
        foreach (static::$acf_mapping as $fieldName => $fieldKey) {
            $this->get($fieldName);
        }
        return $this;

    }

}
