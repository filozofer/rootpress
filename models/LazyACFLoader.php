<?php

namespace Rootpress\models;

use Rootpress\Rootpress;

/**
 * Class LazyACFLoader
 * Allow to load ACF fields only on demand when retrieving a WP_Entity
 */
class LazyACFLoader {

    /**
     * The entity id to use when we call get_field from ACF
     * @documentation https://www.advancedcustomfields.com/resources/get_field/
     * @var string
     */
    public $entityID;

    /**
     * The field key of the ACF
     * @var string
     */
    public $fieldKey;

    /**
     * The field name of the ACF
     * @var string
     */
    public $fieldName;

    /**
     * The field value of the ACF
     * @var mixed
     */
    public $value;

    /**
     * LazyACFLoader constructor.
     *
     * @param string $fieldKey
     * @param string $fieldName
     */
    public function __construct($fieldKey, $fieldName, $entityID) {
        $this->fieldKey = $fieldKey;
        $this->fieldName = $fieldName;
        $this->entityID = $entityID;
    }

    /**
     * Load the ACF value
     */
    public function getValue() {

         // Get value from ACF
        $this->value = get_field($this->fieldKey, $this->entityID);

        // Convert WP_Entity to real models
        $this->value = (is_object($this->value) || (is_array($this->value) && !empty($this->value) && isset($this->value[0]) && is_object($this->value[0]))) ? Rootpress::getEntityFromWPPost($this->value) : $this->value;

        // Return value
        return $this->value;

    }

}