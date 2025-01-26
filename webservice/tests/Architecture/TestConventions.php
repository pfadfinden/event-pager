<?php

namespace App\Tests\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Metadata\Covers;
use PHPUnit\Metadata\CoversClass;
use PHPUnit\Metadata\CoversFunction;
use PHPUnit\Metadata\CoversMethod;
use PHPUnit\Metadata\CoversNothing;

/**
 * PHPAT Test Suite called by PHPSTAN
 *
 * This test suite defines naming conventions for this project
 */
final class TestConventions
{
    public function testTestSuitesAreFinal(): Rule
    {
        return $this->allTestSuites()
            ->shouldBeFinal()
            ->because('Test classes will never be extended');
    }

    public function testTestHaveGroupAttribute(): Rule
    {
        return $this->allTestSuites()
            ->shouldApplyAttribute()->classes(Selector::classname(Group::class))
            ->because('Tests must be assigned to at least one group (e.g. unit, integration)');
    }

    public function testTestSuitesShouldHaveTestSuffix(): Rule
    {
        return $this->allTestSuites()
            ->shouldBeNamed('/^App\\Tests\\[\\A-Za-z0-9]+Test$/', true)
            ->because('Tests must be assigned to at least one group (e.g. unit, integration)');
    }

    /* public function testTestSuitesShouldHaveCoversAnnotation(): Rule
    {
        // TODO requires latest phpat, which is only available after phpstan 2.0 update
        return $this->allTestSuites()
            ->shouldApplyAttribute()->classes(
                Selector::classname(Covers::class),
                Selector::classname(CoversClass::class),
                Selector::classname(CoversNothing::class),
                Selector::classname(CoversMethod::class),
                Selector::classname(CoversFunction::class),
            )
            ->because('test suites should cover only explicitly tested code');
    } */

    public function allTestSuites(): \PHPat\Test\Builder\SubjectExcludeOrAssertionStep
    {
        // TODO Selector::ALL
        return PHPat::rule()
            ->classes(
                /*Selector::AND(
                    Selector::classname('/^App\\Tests\\[\\A-Za-z0-9]+Test$/]'),
                    Selector::NOT(Selector::inNamespace('App\Tests\Architecture'))
                ),*/
                Selector::inNamespace('App\Tests'),
                Selector::extends(TestSuite::class),
                Selector::NOT(Selector::isAbstract())
            );
    }
}
