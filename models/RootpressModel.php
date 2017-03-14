<?php

namespace Rootpress\models;
use Rootpress\exception\CRUD\BuildEntityException;

/**
 * Rootpress Model for transversal function to models
 * This abstract model can be use as template for your own abstract parent model
 */
abstract class RootpressModel implements RootpressModelInterface {

    /** @var $ID int */
	public $ID = 0;
	public $post_type = '';
	public static $linked_post_type = '';

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
            	$calledClass = get_called_class();
            	if(!property_exists($calledClass, $field)){
		            $explodedClass = explode('\\', $calledClass);
		            $class = end($explodedClass);
		            throw new BuildEntityException('Build entity ' . $class . ' failed. The mandatory field [' . $field . '] does not exists.');
	            }
                if(is_null($this->get($field))) {
                    throw new BuildEntityException('Build entity ' . $this->post_title . ' failed. The mandatory field ' . $field . ' was not set.');
                }
            }
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
	public function get($paramName) {
		$getter = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $paramName)));
		if(method_exists($this, $getter)) {
			return $this->$getter();
		}
		if(array_key_exists($paramName, $this->getAttributeMapping())){
			return $this->get(end($this->getAttributeMapping()[$paramName]));
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
	public function set($paramName, $value) {
		$setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $paramName)));
		if(method_exists($this, $setter)) {
			return $this->$setter($value);
		}
		if(array_key_exists($paramName, $this->getAttributeMapping())){
			return $this->set(end($this->getAttributeMapping()[$paramName]), $value);
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
	 * @param string $name
	 */
	public function __get($name){ $this->get($name); }

	/**
	 * Magic setter
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value){ $this->set($name, $value); }

	/**
	 * Get ACF field key from field name
	 * @param $fieldName
	 * @return string
	 * @throws \Exception
	 */
	public function getAcfFieldKeyFromName($fieldName) {

		// Method exist ?
		if(!method_exists($this, 'getAttributeMapping')) {
			throw new \Exception('You must implement getAttributeMapping in your model class before using this function.');
		}

		// Find field and return it
		$fields = $this->getAttributeMapping();
		if(!isset($fields[$fieldName])) {
			throw new \Exception('Field not found in getAttributeMapping. Cannot retrieve associate field key.');
		}
		return key($fields[$fieldName]);

	}

}
