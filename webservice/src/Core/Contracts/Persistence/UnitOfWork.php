<?php

declare(strict_types=1);

namespace App\Core\Contracts\Persistence;

interface UnitOfWork
{
    public function commit(): void;
}
