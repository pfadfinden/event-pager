<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Core\IntelPage\Model\Pager;
use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use Override;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use function sprintf;

/**
 * @extends PersistentObjectFactory<Pager>
 */
final class PagerFactory extends PersistentObjectFactory
{
    /** @phpstan-ignore property.readOnlyByPhpDocDefaultValue */
    private static int $numberCounter = 1;

    public static function class(): string
    {
        return Pager::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        $number = self::$numberCounter++;
        $area = self::faker()->city();

        return [
            'id' => new Ulid(),
            'label' => sprintf('%s %03d', $area, $number),
            'number' => $number,
        ];
    }

    #[Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Pager $pager): void {
                // Optionally activate some pagers
                if (self::faker()->boolean(60)) {
                    $pager->setActivated(true);
                }
            });
    }

    public function withLabel(string $label): static
    {
        return $this->with(['label' => $label]);
    }

    public function withNumber(int $number): static
    {
        return $this->with(['number' => $number]);
    }

    public function activated(): static
    {
        return $this->afterInstantiate(function (Pager $pager): void {
            $pager->setActivated(true);
        });
    }

    public function deactivated(): static
    {
        return $this->afterInstantiate(function (Pager $pager): void {
            $pager->setActivated(false);
        });
    }

    public function carriedBy(AbstractMessageRecipient $recipient): static
    {
        return $this->afterInstantiate(function (Pager $pager) use ($recipient): void {
            $pager->setCarriedBy($recipient);
        });
    }

    public function withComment(string $comment): static
    {
        return $this->afterInstantiate(function (Pager $pager) use ($comment): void {
            $pager->setComment($comment);
        });
    }
}
