<?php

declare(strict_types=1);

namespace App\Tests\E2E\Home;

use App\Tests\E2E\AbstractPantherTestCase;
use App\Tests\Factory\RecipientPersonFactory;
use App\Tests\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('webgui')]
final class MyChannelsE2ETest extends AbstractPantherTestCase
{
    public function testShowsNoRecipientMessageWhenUnlinked(): void
    {
        UserFactory::new()
            ->asActiveUser()
            ->withUsername('nochannels')
            ->create();

        $this->login('nochannels', 'password');
        $this->client->request('GET', '/');
        $this->waitForElement('main');

        self::assertSelectorTextContains('main', 'No recipient is linked to your account');
    }

    public function testShowsLinkedRecipientTransportConfiguration(): void
    {
        $person = RecipientPersonFactory::new()
            ->with(['name' => 'E2E Channel Person'])
            ->create();

        $person->addTransportConfiguration('ntfy');

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->persist($person);
        $em->flush();

        UserFactory::new()
            ->asActiveUser()
            ->withUsername('haschannels')
            ->withRecipient($person)
            ->create();

        $this->login('haschannels', 'password');
        $this->client->request('GET', '/');
        $this->waitForElement('main');

        self::assertSelectorTextContains('main', 'Transport Configurations');
        self::assertSelectorTextContains('main', 'ntfy');
    }
}
