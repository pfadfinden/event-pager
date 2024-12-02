<?php

declare(strict_types=1);

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->append([
        __FILE__,
    ])
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'blank_line_between_import_groups' => false, // differs from Symfony Coding Standard
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'declare_strict_types' => true,
        'global_namespace_import' => [ // differs from Symfony Coding Standard
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'native_constant_invocation' => [
            'scope' => 'namespaced',
        ],
        'native_function_invocation' => [
            'scope' => 'namespaced',
        ],
        'no_break_comment' => [
            'comment_text' => 'Intentional: No break',
        ],
        'non_printable_character' => false, // As of PHP 7, they can be masked in strings (Not included in @Symfony)
        'phpdoc_no_alias_tag' => [ // differs from @Symfony, needed for abstract readonly properties
            'replacements' => [
                'type' => 'var',
                'link' => 'see',
            ],
        ],
        'phpdoc_to_comment' => false, // differs from @Symfony, needed for higher PHPStan / lower PSalm levels
        'pow_to_exponentiation' => true,
    ])
    ->setCacheFile('./var/build-cache/.php-cs-fixer.cache')
    ->setFinder($finder)
;
