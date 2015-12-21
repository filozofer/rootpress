<?php

namespace ChangeThisToYourThemeName\repositories;

use Rootpress\utils\Hydratator;
use Rootpress\repositories\CRUDTaxonomyRepository;

/**
 * CRUDTaxonomyRepository
 */
class LabelSampleRepository extends CRUDTaxonomyRepository {

    // Associate taxonomy
    public static $associate_post_type = 'label';

    //Repository parameters
    public static $fields = [];

}
