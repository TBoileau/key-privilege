<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\Company\Company;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use function Symfony\Component\String\u;

class CompanyListener
{
    public function prePersist(Company $company): void
    {
        $this->generateVatNumber($company);
    }

    public function preUpdate(Company $company, PreUpdateEventArgs $eventArgs): void
    {
        if ($eventArgs->hasChangedField("companyNumber")) {
            $this->generateVatNumber($company);
        }
    }

    private function generateVatNumber(Company $company): void
    {
        $compactCompanyNumber = (int) u($company->getCompanyNumber())->slice(0, 9)->toString();

        $company->setVatNumber(
            sprintf(
                "FR%d%s",
                (( 12 + 3 * ( $compactCompanyNumber % 97 ) ) % 97 ),
                $compactCompanyNumber
            )
        );
    }
}
