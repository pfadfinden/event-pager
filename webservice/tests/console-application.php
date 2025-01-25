<?php

declare(strict_types=1);

/**
 * This file is used by phpstan/phpstan-symfony extension for Symfony console command validation.
 */

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

// @phpstan-ignore-next-line cast.string
$kernel = new Kernel((string) $_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);

return new Application($kernel);
