<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/assets',
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets()
    ->withAttributesSets()
    ->withComposerBased(twig: true, doctrine: true, phpunit: true, symfony: true)
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0)
    ->withConfiguredRule(ClassPropertyAssignToConstructorPromotionRector::class, [
        // This decreases readability for entities where not all properties are part of the constructor
        'allow_model_based_classes' => false,
    ])
    ->withSkip([
        // Within tests the string classname is used to identify transports,
        // which should not be replaced to ensure changes in FQDN are identified as BC breaks
        StringClassNameToClassConstantRector::class => [
            __DIR__.'/tests/*',
        ],
    ]);
