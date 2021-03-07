<?php

declare(strict_types=1);

namespace App\UseCase;

use App\Entity\Key\Transfer;

interface TransferPointsInterface
{
    public function execute(Transfer $transfer): void;
}
