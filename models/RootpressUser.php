<?php

namespace Rootpress\models;

use Rootpress\exception\CRUD\BuildEntityException;
use WP_User;

/**
 * Rootpress User for transversal function to User
 * This abstract model can be use as template for your own abstract parent user model
 */
abstract class RootpressUser extends WP_User {

	/** @var $ID int */
	public $ID = 0;
	public static $linked_post_type = 'WP_User';

	/**
	 * Constructor for this model
	 * Override this function in your child class to do post treatement after hydratation
	 */
	public function construct() {
	}

	/**
	 * Build object from array of data
	 *
	 * @param array $data
	 *
	 * @throws BuildEntityException
	 */
	public function build( array $data ) {
		// Set all the field from $data
		foreach ( $data as $fieldName => $fieldValue ) {
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

		// Verify if there is a mapping name for this field, If yes, use this name
		if ( array_key_exists( $paramName, $this->getAttributeMapping() ) ) {
			$paramName = reset( $this->getAttributeMapping()[ $paramName ] );
		}

		// Is there a getter for this param ? If yes, use it !
		$getter = 'get' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $paramName ) ) );
		if ( method_exists( $this, $getter ) ) {
			return $this->$getter();
		}

		// Is param exist in user data attribute, return this value
		if(isset($this->data) && isset($this->data->$paramName)) {
			return $this->data->$paramName;
		}

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
		if ( array_key_exists( $paramName, $this->getAttributeMapping() ) ) {
			$paramName = reset( $this->getAttributeMapping()[ $paramName ]);
		}

		$setter = 'set' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $paramName ) ) );
		if ( method_exists( $this, $setter ) ) {
			return $this->$setter( $value );
		}

		return $this->$paramName = $value;
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
	 */
	public function __get( $name ) {
		return $this->get( $name );
	}

	public function __set( $name, $value ) {
		$this->set( $name, $value );
	}

	/**
	 * Get ACF field key from field name
	 *
	 * @param $fieldName
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getAcfFieldKeyFromName( $fieldName ) {

		// Method exist ?
		if ( ! method_exists( $this, 'getAttributeMapping' ) ) {
			throw new \Exception( 'You must implement getAttributeMapping in your model class before using this function.' );
		}

		// Find field and return it
		$fields = $this->getAttributeMapping();
		if ( ! isset( $fields[ $fieldName ] ) ) {
			throw new \Exception( 'Field not found in getAttributeMapping. Cannot retrieve associate field key.' );
		}

		return key( $fields[ $fieldName ] );

	}

}
