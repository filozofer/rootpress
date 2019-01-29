<?php

namespace Rootpress\models;

/**
 * Rootpress Model Interface to obligate dev to implements some methods in their Rootpress entities
 */
interface RootpressModelInterface  {

    /**
     * Return the ACF => Attribute mapping
     * ACF name => [ACF key => typeAttribute]
     * @return array
     */
    public function getAttributeMapping();

}
