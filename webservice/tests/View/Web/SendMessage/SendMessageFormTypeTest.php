<?php

declare(strict_types=1);

namespace App\Tests\View\Web\SendMessage;

use App\Tests\TestUtilities\FormValidationTrait;
use App\View\Web\SendMessage\Form\RecipientsChoiceLoader;
use App\View\Web\SendMessage\Form\SendMessageFormType;
use App\View\Web\SendMessage\SendMessageRecipientRequest;
use App\View\Web\SendMessage\SendMessageRequest;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Test\TypeTestCase;

#[Group('unit')]
#[AllowMockObjectsWithoutExpectations]
final class SendMessageFormTypeTest extends TypeTestCase
{
    use FormValidationTrait;

    /**
     * @param array{message: string, priority: int, to: array{id: string, label: string, type: string|null}[]} $formData
     */
    #[DataProvider('provideValidData')]
    public function testSubmitValidData(array $formData): void
    {
        /** @var Form $form */
        [$form, $model] = $this->init();

        $expected = new SendMessageRequest();
        $expected->message = $formData['message'];
        $expected->priority = $formData['priority'];
        $expected->to = array_map(function (array $r): SendMessageRecipientRequest {
            $recipientA = new SendMessageRecipientRequest();
            $recipientA->id = $r['id'];
            $recipientA->label = $r['label'];
            $recipientA->type = $r['type'];

            return $recipientA;
        }, $formData['to']);

        // submit the data to the form directly
        $form->submit($formData);

        // This check ensures there are no transformation failures
        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid(), 'Failed asserting that form is valid');

        // check that $model was modified as expected when the form was submitted
        self::assertEquals($expected, $model);
    }

    /**
     * @return iterable<string, array{message: string, priority: int, to: array{id: string, label: string, type: string|null}[]}[]>
     */
    public static function provideValidData(): iterable
    {
        $recipientA = [];
        $recipientA['id'] = '01J6YVHPG5B9ACNF2JEFRZBCTZ';
        $recipientA['label'] = 'A';
        $recipientA['type'] = 'GROUP';
        $recipientB = [];
        $recipientB['id'] = '01J6YVHAW9G41R0C33G6CPRY85';
        $recipientB['label'] = 'B';
        $recipientB['type'] = 'GROUP';

        yield 'low end' => [[
            'message' => 'L',
            'priority' => 1,
            'to' => [$recipientA],
        ]];
        yield 'high end' => [[
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse eu molestie orci. Sed vel est tempus, ultrices enim at, suscipit ligula. Curabitur non sapien vitae lorem commodo consequat id porttitor quam. Maecenas imperdiet congue dolor, vitae ves.',
            'priority' => 5,
            'to' => [$recipientA, $recipientB],
        ]];
    }

    /**
     * @param array{message: string, priority: int, to: array<string|array{id: string, label: string, type: string|null}>} $formData
     */
    #[DataProvider('provideInvalidData')]
    public function testSubmitInvalidData(array $formData): void
    {
        [$form] = $this->init();
        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid(), 'Form validation should fail');
    }

    /**
     * @return iterable<array<array{message: string, priority: int, to: array<string|array{id: string, label: string, type: string|null}>}>>
     */
    public static function provideInvalidData(): iterable
    {
        $recipient = ['id' => '01J6YT42VYK4FWMPSCX2W6EQ2W', 'label' => 'Sample', 'type' => 'GROUP'];
        yield 'no recipient' => [[
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'priority' => 4,
            'to' => [],
        ]];
        yield 'too long message' => [[
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse eu molestie orci. Sed vel est tempus, ultrices enim at, suscipit ligula. Curabitur non sapien vitae lorem commodo consequat id porttitor quam. Maecenas imperdiet congue dolor, vitae vestibulum nulla sollicitudin in. Suspendisse et.',
            'priority' => 4,
            'to' => [$recipient],
        ]];
        yield 'too high priority' => [[
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'priority' => 6,
            'to' => [$recipient],
        ]];
        yield 'too low priority' => [[
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'priority' => 0,
            'to' => [$recipient],
        ]];
        yield 'empty message' => [[
            'message' => '',
            'priority' => 1,
            'to' => [$recipient],
        ]];
        yield 'invalid recipient list' => [[
            'message' => 'X',
            'priority' => 1,
            'to' => ['xxx', $recipient],
        ]];
        yield 'invalid character' => [[
            'message' => 'ѕ',
            'priority' => 1,
            'to' => [$recipient],
        ]];
    }

    // @phpstan-ignore missingType.iterableValue (no array literal type available)
    public function init(): array
    {
        $mockChoiceLoader = self::createStub(RecipientsChoiceLoader::class);

        $model = new SendMessageRequest();
        // $model will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(SendMessageFormType::class, $model, ['choice_loader' => $mockChoiceLoader]);

        return [
            $form,
            $model,
        ];
    }
}
