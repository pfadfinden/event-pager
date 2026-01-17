<?php

declare(strict_types=1);

namespace App\Core\DataExchange\Model;

enum ImportConflictStrategy: string
{
    case SKIP = 'skip';
    case UPDATE = 'update';
    case ERROR = 'error';
}
