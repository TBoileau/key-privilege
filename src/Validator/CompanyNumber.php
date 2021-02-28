<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CompanyNumber extends Constraint
{
    public string $message = 'Le N° de SIRET "{{ value }}" n\'est pas valide.';
}
