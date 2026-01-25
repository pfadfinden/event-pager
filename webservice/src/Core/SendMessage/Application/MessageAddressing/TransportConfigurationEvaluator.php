<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Application\MessageAddressing;

use App\Core\MessageRecipient\Model\MessageRecipient;
use App\Core\SendMessage\Model\MessageAddressing\AddressingError;
use App\Core\SendMessage\Model\MessageAddressing\EvaluationContext;
use App\Core\SendMessage\Model\MessageAddressing\SelectedTransport;
use App\Core\SendMessage\Port\ExpressionEvaluationException;
use App\Core\SendMessage\Port\SelectionExpressionEvaluator;
use App\Core\TransportContract\Model\Message;
use App\Core\TransportContract\Port\Transport;
use App\Core\TransportContract\Port\TransportManager;

/**
 * Evaluates transport configurations for a recipient in rank order.
 */
readonly class TransportConfigurationEvaluator
{
    public function __construct(
        private SelectionExpressionEvaluator $expressionEvaluator,
        private TransportManager $transportManager,
    ) {
    }

    /**
     * Evaluates transport configurations for a recipient.
     */
    public function evaluate(
        MessageRecipient $recipient,
        EvaluationContext $context,
        Message $message,
    ): TransportConfigurationEvaluationResult {
        $configurations = $recipient->getTransportConfiguration();

        if ([] === $configurations) {
            return new TransportConfigurationEvaluationResult(
                [],
                [AddressingError::noTransportConfigurations($recipient)],
            );
        }

        // Configurations are already sorted by rank descending from the model
        $sortedConfigurations = $configurations;

        $selectedTransports = [];
        $errors = [];

        foreach ($sortedConfigurations as $configuration) {
            if (!$configuration->isEnabled) {
                continue;
            }

            $transport = $this->transportManager->transportWithKey($configuration->getKey());

            if (!$transport instanceof Transport) {
                $errors[] = AddressingError::transportNotFound($recipient, $configuration);
                continue;
            }

            if (!$transport->acceptsNewMessages()) {
                continue;
            }

            try {
                $matches = $this->expressionEvaluator->evaluate(
                    $configuration->getSelectionExpression(),
                    $context,
                );
            } catch (ExpressionEvaluationException $e) {
                $errors[] = AddressingError::expressionEvaluationFailed(
                    $recipient,
                    $configuration,
                    $e->getMessage(),
                );
                $matches = false;
            }

            if ($matches) {
                // Check if transport can actually send to this recipient with this configuration
                $canSend = $transport->canSendTo(
                    $recipient,
                    $message,
                    $configuration->getVendorSpecificConfig(),
                );

                if ($canSend) {
                    $selectedTransports[] = new SelectedTransport($configuration, $transport);

                    if (!$configuration->shouldEvaluateOtherTransportConfigurations()) {
                        break;
                    }
                }
            }
        }

        if ([] === $selectedTransports && [] === $errors) {
            $errors[] = AddressingError::noMatchingConfigurations($recipient);
        }

        return new TransportConfigurationEvaluationResult($selectedTransports, $errors);
    }
}
