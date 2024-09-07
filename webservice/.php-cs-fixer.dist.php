<?php

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'], // part of PERCS2.0 included in sf
        'no_null_property_initialization' => true, // part of Sf coding standard
        'nullable_type_declaration_for_default_null_value' => true, // part of Sf coding standard
        'phpdoc_order' => true, // part of Sf coding standard
        'phpdoc_types_order' => [ // part of Sf coding standard
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'none',
        ],
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
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
        'protected_to_private' => false, // Not included in @Symfony
        'phpdoc_no_empty_return' => false, // Not included in@Symfony
        'ordered_class_elements' => false, // Not included in @Symfony
        'ordered_imports' => [ 
            'imports_order' => ['class', 'const', 'function'], // differs from @Symfony
        ],
        'global_namespace_import' => [ // differs from Symfony Coding Standard
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'ordered_traits' => false, // Not included in @Symfony
        'phpdoc_to_comment' => false, // differs from @Symfony
        'pow_to_exponentiation' => true,
        'declare_strict_types' => true,
    ])
    ->setCacheFile('./var/build-cache/.php-cs-fixer.cache')
    ->setFinder($finder)
;

