<?php

namespace Rootpress\models;

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
        $this->value = get_field($this->fieldKey, $this->entityID);
        return $this->value;
    }

}