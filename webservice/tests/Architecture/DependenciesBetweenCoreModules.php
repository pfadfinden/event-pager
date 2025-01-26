<?php

namespace App\Tests\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

/**
 * PHPAT Test Suite called by PHPSTAN
 *
 * This test suite defines rules for the relationship between modules in the Core.
 *
 * When creating a new module, add a const with the fully qualified namespace
 * and create a restrictive test method defining which modules it can depend on.
 */
final class DependenciesBetweenCoreModules
{
    const string APP_CORE_CONTRACT = 'App\Core\Contract';
    const string APP_CORE_INTEL_PAGE = 'App\Core\IntelPage';
    const string APP_CORE_MESSAGE_RECIPIENT = 'App\Core\MessageRecipient';
    const string APP_CORE_SEND_MESSAGE = 'App\Core\SendMessage';
    const string APP_CORE_TRANSPORT_CONTRACT = 'App\Core\TransportContract';

    public function testSendMessageOnlyDependsOnLowerLevelContracts(): Rule
    {
        return $this->moduleCanOnlyDependOn(
            self::APP_CORE_SEND_MESSAGE,
            [self::APP_CORE_TRANSPORT_CONTRACT, self::APP_CORE_CONTRACT]
        );
    }

    public function testMessageRecipientOnlyDependsOnLowerLevelContracts(): Rule
    {
        return $this->moduleCanOnlyDependOn(
            self::APP_CORE_MESSAGE_RECIPIENT,
            [self::APP_CORE_TRANSPORT_CONTRACT, self::APP_CORE_CONTRACT]
        );
    }


    public function testIntelPageOnlyDependsOnLowerLevelContracts(): Rule
    {
        return $this->moduleCanOnlyDependOn(
            self::APP_CORE_INTEL_PAGE,
            [self::APP_CORE_TRANSPORT_CONTRACT, self::APP_CORE_CONTRACT]
        );
    }

    public function testTransportContractOnlyDependsOnLowerLevelContracts(): Rule
    {
        return $this->moduleCanOnlyDependOn(
            self::APP_CORE_TRANSPORT_CONTRACT,
            []
        );
    }

    public function testContractOnlyDependsOnItself(): Rule
    {
        return $this->moduleCanOnlyDependOn(
            self::APP_CORE_CONTRACT,
            []
        );
    }

    public function testContractOnlyContainsInterfaces(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace(self::APP_CORE_CONTRACT))
            ->shouldBeInterface()
            ->because('they must not contain implementation');
    }

    /**
     * Within the App\Core namespace, a module can only depend on itself and the allowed dependencies.
     *
     * @param non-empty-string $module
     * @param non-empty-string[] $allowedDependencies
     */
    private function moduleCanOnlyDependOn(string $module, array $allowedDependencies): Rule
    {
        $allowedSelectors = array_map(fn($fqn) => Selector::inNamespace($fqn), $allowedDependencies);
        return PHPat::rule()
            ->classes(Selector::inNamespace($module))
            ->canOnlyDependOn()->classes(
                Selector::NOT(Selector::inNamespace('App\Core')), // everything outside the core is handled by rules in HighLevelArchitecture.php
                Selector::inNamespace($module),
                ...$allowedSelectors,

            )
            ->because('to increase maintainability');
    }
}
