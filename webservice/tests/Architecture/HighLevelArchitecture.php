<?php

namespace App\Tests\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

/**
 * PHPAT Test Suite called by PHPSTAN
 *
 * This test suite defines rules concerning the general namespace structure.
 */
final class HighLevelArchitecture
{
    public function testCoreOnlyDependsOnItselfAndNamedExceptions(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Core'))
            ->canOnlyDependOn()->classes(
                Selector::inNamespace('App\Core'),
                // ORM Mapping
                Selector::inNamespace('Doctrine\ORM'),
                Selector::classname('Symfony\Bridge\Doctrine\Types\UlidType'),
                // Value Objects & Co
                Selector::inNamespace('Brick\DateTime'),
                Selector::classname('Symfony\Component\Uid\Ulid'),
                Selector::inNamespace('Doctrine\Common\Collections'),
                // PHP
                Selector::classname(\BackedEnum::class),
                Selector::classname(\Traversable::class),
                Selector::classname('/^[A-Za-z]*Exception$/', true), // Global exceptions
            )
            ->because('core should not be polluted by view or dependency specific implementations.');
    }

    public function testInfrastructureDoesNotDependOnView(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Infrastructure'))
            ->canOnlyDependOn()->classes(
                Selector::inNamespace('App\Core'),
                Selector::inNamespace('App\Infrastructure'),
                Selector::NOT(Selector::inNamespace('App\View'))
            )
            ->because('should not be view specific.');
    }

    public function testOutsideCodeDoesNotDependOnApplicationServices(): Rule
    {
        return PHPat::rule()
            ->classes(
                Selector::inNamespace('App\Infrastructure'),
                Selector::inNamespace('App\Core'),
                Selector::inNamespace('App\View'),
            )
            ->canOnlyDependOn()->classes(
                Selector::NOT(Selector::inNamespace('/App\Core\[A-Za-z0-9]+\Application/', true))
            )
            ->because('should depend on Port interfaces');
    }

    public function testModuleCodeIsSeparatedIntoChildNamespaces(): Rule
    {
        return PHPat::rule()
            ->classes(
                Selector::inNamespace('/App\Core\[A-Za-z0-9]+$/', true),
            )
            ->shouldNotExist()
            ->because('should me moved to a child-namespace');
    }
}
