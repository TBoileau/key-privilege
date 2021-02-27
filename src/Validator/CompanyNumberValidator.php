<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use function Symfony\Component\String\u;

class CompanyNumberValidator extends ConstraintValidator
{
    private const SIRET_LENGTH = 14;

    /**
     * @param string $companyNumber
     * @param CompanyNumber $constraint
     */
    public function validate($companyNumber, Constraint $constraint): void
    {
        if ('' === $companyNumber) {
            return;
        }

        $companyNumber = u($companyNumber)->trim()->toString();

        $addViolation = fn () => $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $companyNumber)
            ->addViolation();

        if (!is_numeric($companyNumber) || u($companyNumber)->length() !== self::SIRET_LENGTH) {
            $addViolation();
        }

        $sum = 0;
        for ($i = 0; $i < self::SIRET_LENGTH; ++$i) {
            if ($i % 2 === 0) {
                $tmp = ((int) u($companyNumber)->slice($i, 1)->toString()) * 2;
                $tmp = $tmp > 9 ? $tmp - 9 : $tmp;
            } else {
                $tmp = (int) u($companyNumber)->slice($i, 1)->toString();
            }
            $sum += $tmp;
        }

        if ($sum % 10 > 0) {
            $addViolation();
        }
    }
}
