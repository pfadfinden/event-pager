<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector;
use Rector\DeadCode\Rector\MethodCall\RemoveNullArgOnNullDefaultParamRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;

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
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        rectorPreset: true,
        phpunitCodeQuality: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withConfiguredRule(ClassPropertyAssignToConstructorPromotionRector::class, [
        // This decreases readability for entities where not all properties are part of the constructor
        'allow_model_based_classes' => false,
    ])
    ->withSkip(skip: [
        // Auto Generated
        'config/reference.php',
        'config/bundles.php',
        // Message bus handler should only have public invoke,
        // removing this is impacting discovery of handler negatively.
        RemoveUnusedPublicMethodParameterRector::class => [
            __DIR__.'/src/**/*Handler.php',
        ],
        // If conditions of exceptional cases that may sometimes still
        // be false even if the static analyser does not think so far.
        Rector\DeadCode\Rector\If_\RemoveTypedPropertyDeadInstanceOfRector::class => [
            __DIR__.'/src/Core/IntelPage/Model/ChannelCapAssignment.php',
        ],
        // Within tests the string classname is used to identify transports,
        // which should not be replaced to ensure changes in FQDN are identified as BC breaks
        StringClassNameToClassConstantRector::class => [
            __DIR__.'/tests/*',
        ],
        // Usually incomplete tests, keep around until tests completed.
        RemoveUnusedVariableAssignRector::class => [
            __DIR__.'/tests/*',
        ],
        // In tests explicitly pass null in case default changes
        RemoveNullArgOnNullDefaultParamRector::class => [
            __DIR__.'/tests/*',
        ],
        // Bites with phpstan and there is no formal recommendation by authors
        PreferPHPUnitThisCallRector::class => [
            __DIR__.'/tests/*',
        ],
    ])
    ->withImportNames(importDocBlockNames: false, importShortClasses: false, removeUnusedImports: true);
