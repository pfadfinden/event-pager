<?php

namespace App\Tests\Architecture;

use PHPat\Selector\Selector;
use PHPat\Test\Builder\Rule;
use PHPat\Test\PHPat;

/**
 * PHPAT Test Suite called by PHPSTAN
 *
 * This test suite defines naming conventions for this project
 */
final class NamingConventions
{
    public function testControllersHaveSuffix(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::appliesAttribute('Symfony\Component\Routing\Attribute\Route'))
            ->shouldBeNamed('/\w+Controller$/', regex: true)
            ->because('controllers must use suffix.');
    }

    public function testFormsHaveSuffix(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::extends('Symfony\Component\Form\AbstractType'))
            ->shouldBeNamed('/\w+Form$/', regex: true)
            ->because('form types must use `Form` suffix.');
    }

    public function testEventsHaveNoSuffix(): Rule
    {
        return PHPat::rule()
            ->classes(Selector::inNamespace('App\Core\**\Event\**'))
            ->shouldBeNamed('/.*(?<!Event)$/', regex: true)
            ->because('events must not use suffix.');
    }
}
