<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 */
#[\Attribute] class contraintsCheckUrl extends Constraint {

    public $message = 'Le champ contient des liens non valide';

    public function validatedBy(): string
    {
        return 'validatorCheckUrl';
    }

}