<?php

declare(strict_types=1);

namespace App\Tests\TestUtilities;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

trait FormValidationTrait
{
    /**
     * @return ValidatorExtension[]
     */
    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();

        // or if you also need to read constraints from annotations
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }
}
