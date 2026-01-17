<?php

declare(strict_types=1);

/**
 * This file is used by phpstan/phpstan-doctrine extension for DQL validation.
 */

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

new Dotenv()->bootEnv(__DIR__.'/../.env');

// @phpstan-ignore-next-line cast.string
$kernel = new Kernel((string) $_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
