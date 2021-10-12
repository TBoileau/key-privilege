<?php

declare(strict_types=1);

namespace App\Form\Key;

use App\Entity\Company\Member;
use App\Entity\Key\Account;
use App\Entity\User\Manager;
use App\Repository\Key\AccountRepository;

class GiveType extends TransferType
{
    /**
     * @param AccountRepository<Account> $accountRepository
     */
    public function __construct(private AccountRepository $accountRepository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFromOptions(Manager $manager): array
    {
        return [
            'choices' => array_merge(
                [$manager->getAccount()],
                $manager->getMembers()->map(fn (Member $member) => $member->getAccount())->toArray()
            )
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getToOptions(Manager $manager): array
    {
        return [
            'query_builder' => $this->accountRepository->createQueryBuilderAccountByManagerForTransfer($manager)
        ];
    }
}
