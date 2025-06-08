<?php

declare(strict_types=1);

namespace App\Tests\View\Web\SendMessage;

use App\Tests\TestUtilities\FormValidationTrait;
use App\View\Web\SendMessage\SendMessageFormType;
use App\View\Web\SendMessage\SendMessageRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Form\Test\TypeTestCase;

#[Group('unit')]
final class SendMessageFormTypeTest extends TypeTestCase
{
    use FormValidationTrait;

    /**
     * @param array{message: string, priority: int, to: string[]} $formData
     */
    #[DataProvider('provideValidData')]
    public function testSubmitValidData(array $formData): void
    {
        $model = new SendMessageRequest();
        // $model will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(SendMessageFormType::class, $model);

        $expected = new SendMessageRequest();
        $expected->message = $formData['message'];
        $expected->priority = $formData['priority'];
        $expected->to = $formData['to'];

        // submit the data to the form directly
        $form->submit($formData);

        // This check ensures there are no transformation failures
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid(), 'Failed asserting that form is valid');

        // check that $model was modified as expected when the form was submitted
        self::assertEquals($expected, $model);
    }

    /**
     * @return iterable<string, array{message: string, priority: int, to: string[]}[]>
     */
    public static function provideValidData(): iterable
    {
        yield 'low end' => [[
            'message' => 'L',
            'priority' => 1,
            'to' => ['01J6YVHPG5B9ACNF2JEFRZBCTZ'],
        ]];
        yield 'high end' => [[
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse eu molestie orci. Sed vel est tempus, ultrices enim at, suscipit ligula. Curabitur non sapien vitae lorem commodo consequat id porttitor quam. Maecenas imperdiet congue dolor, vitae ves.',
            'priority' => 5,
            'to' => ['01J6YT42VYK4FWMPSCX2W6EQ2W', '01J6YVHAW9G41R0C33G6CPRY85'],
        ]];
    }

    /**
     * @param array{message: string, priority: int, to: string[]} $formData
     */
    #[DataProvider('provideInvalidData')]
    public function testSubmitInvalidData(array $formData): void
    {
        $model = new SendMessageRequest();
        $form = $this->factory->create(SendMessageFormType::class, $model);
        $form->submit($formData);
        self::assertTrue($form->isSynchronized());

        self::assertFalse($form->isValid(), 'Form validation should fail');
    }

    /**
     * @return iterable<array<array{message: string, priority: int, to: string[]}>>
     */
    public static function provideInvalidData(): iterable
    {
        yield 'no recipient' => [[
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'priority' => 4,
            'to' => [],
        ]];
        yield 'too long message' => [[
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse eu molestie orci. Sed vel est tempus, ultrices enim at, suscipit ligula. Curabitur non sapien vitae lorem commodo consequat id porttitor quam. Maecenas imperdiet congue dolor, vitae vestibulum nulla sollicitudin in. Suspendisse et.',
            'priority' => 4,
            'to' => ['01J6YT42VYK4FWMPSCX2W6EQ2W'],
        ]];
        yield 'too high priority' => [[
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'priority' => 6,
            'to' => ['01J6YT42VYK4FWMPSCX2W6EQ2W'],
        ]];
        yield 'too low priority' => [[
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'priority' => 0,
            'to' => ['01J6YT42VYK4FWMPSCX2W6EQ2W'],
        ]];
        yield 'empty message' => [[
            'message' => '',
            'priority' => 1,
            'to' => ['01J6YT42VYK4FWMPSCX2W6EQ2W'],
        ]];
        yield 'invalid recipient list' => [[
            'message' => 'X',
            'priority' => 1,
            'to' => ['xxx', '01J6YT42VYK4FWMPSCX2W6EQ2W'],
        ]];
        yield 'invalid character' => [[
            'message' => 'ѕ',
            'priority' => 1,
            'to' => ['01J6YT42VYK4FWMPSCX2W6EQ2W'],
        ]];
    }
}
