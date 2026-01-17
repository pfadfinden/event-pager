<?php

declare(strict_types=1);

namespace App\View\Web\Admin\DataExchange\Request;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ImportRequest
{
    public string $entityType = 'recipients';
    public string $conflictStrategy = 'skip';
    public ?UploadedFile $file = null;
}
