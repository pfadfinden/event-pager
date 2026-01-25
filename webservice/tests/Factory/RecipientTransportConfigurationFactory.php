<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Core\MessageRecipient\Model\AbstractMessageRecipient;
use App\Core\MessageRecipient\Model\RecipientTransportConfiguration;

/**
 * Helper for creating RecipientTransportConfiguration objects in tests.
 *
 * Since RecipientTransportConfiguration must be created through its parent recipient,
 * this is a simple builder class rather than a Foundry factory.
 */
final class RecipientTransportConfigurationFactory
{
    private int $rank = 0;
    private string $selectionExpression = 'true';
    private ?bool $continueInHierarchy = null;
    private bool $evaluateOtherTransportConfigurations = true;
    private bool $isEnabled = true;

    private function __construct(
        private readonly AbstractMessageRecipient $recipient,
        private readonly string $key,
    ) {
    }

    public static function new(AbstractMessageRecipient $recipient, string $key): self
    {
        return new self($recipient, $key);
    }

    public function withRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function withSelectionExpression(string $expression): self
    {
        $this->selectionExpression = $expression;

        return $this;
    }

    public function alwaysMatch(): self
    {
        return $this->withSelectionExpression('true');
    }

    public function neverMatch(): self
    {
        return $this->withSelectionExpression('false');
    }

    public function withContinueInHierarchy(?bool $continue): self
    {
        $this->continueInHierarchy = $continue;

        return $this;
    }

    public function stopHierarchy(): self
    {
        return $this->withContinueInHierarchy(false);
    }

    public function continueHierarchy(): self
    {
        return $this->withContinueInHierarchy(true);
    }

    public function withEvaluateOtherTransportConfigurations(bool $evaluate): self
    {
        $this->evaluateOtherTransportConfigurations = $evaluate;

        return $this;
    }

    public function stopAfterMatch(): self
    {
        return $this->withEvaluateOtherTransportConfigurations(false);
    }

    public function enabled(): self
    {
        $this->isEnabled = true;

        return $this;
    }

    public function disabled(): self
    {
        $this->isEnabled = false;

        return $this;
    }

    public function create(): RecipientTransportConfiguration
    {
        $config = $this->recipient->addTransportConfiguration($this->key);
        $config->setRank($this->rank);
        $config->setSelectionExpression($this->selectionExpression);
        $config->setContinueInHierarchy($this->continueInHierarchy);
        $config->setEvaluateOtherTransportConfigurations($this->evaluateOtherTransportConfigurations);
        $config->isEnabled = $this->isEnabled;

        return $config;
    }
}
