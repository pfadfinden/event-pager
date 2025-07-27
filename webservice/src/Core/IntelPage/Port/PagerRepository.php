<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Port;

use App\Core\IntelPage\Model\Pager;
use Symfony\Component\Uid\Ulid;

interface PagerRepository
{
    public function getById(Ulid $id): ?Pager;

    public function persist(Pager $pager): void;

    public function remove(Pager $pager): void;
}
