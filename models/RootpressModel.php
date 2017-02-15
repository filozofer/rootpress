<?php

namespace Rootpress\models;
use rootpress\exception\CRUD\BuildEntityException;

/**
 * Rootpress Model for transversal function to models
 * This abstract model can be use as template for your own abstract parent model
 */
abstract class RootpressModel  {

	public $ID = 0;

	/**
	 * Constructor for this model
	 * Override this function in your child class to do post treatement after hydratation
	 */
	public function construct() {
	}

    /**
     * Build object from array of data
     * @param array $data
     * @throws BuildEntityException
     */
    public function build(array $data){

        // Set all the field from $data
        foreach ($data as $fieldName => $fieldValue) {
            $this->set($fieldName, $fieldValue);
        }

        // Set post title if method buildPostTitle exist
        if(method_exists($this, 'buildPostTitle')) {
            $this->post_title = $this->buildPostTitle();
        }

        // Verify if mandatory field are
        if(method_exists($this, 'getMandatoryFields')) {
            $mandatoryFields = $this->getMandatoryFields();
            foreach ($mandatoryFields as $field) {
                if(is_null($this->get($field))) {
                    throw new BuildEntityException('Build entity ' . $this->post_title . ' failed. The mandatory field ' . $field . ' was not set.');
                }
            }
        }

    }

	/**
	 * Generic getter
	 * Prefer using this to access your attribute
	 */
	public function get($paramName) {
		$getter = 'get' . ucwords($paramName);
		if(method_exists($this, $getter)) {
			return $this->$getter();
		}
		return $this->$paramName;
	}

	/**
	 * Generic setter
	 * Prefer using this to change value of your attribute
	 */
	public function set($paramName, $value) {
		$setter = 'set' . ucwords($paramName);
		if(method_exists($this, $setter)) {
			return $this->$setter($value);
		}
		return $this->$paramName = $value;
	}

	/**
	 * Magic Method to call the generics getter and setter when accessing a private attribute of your class
	 * If you want to respect encapsulation rules you need to declare all your object fields as private attribute inside your child class
	 * When the hydrate process will try to hydrate your field, these magic function will call the generics getter and setter
	 */
	public function __get($name){ $this->get($name); }
	public function __set($name, $value){ $this->set($name, $value); }
}
