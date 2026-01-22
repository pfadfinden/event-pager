<?php

declare(strict_types=1);

namespace App\View\Web\RecipientManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\MessageRecipient\Command\AddTransportConfiguration;
use App\Core\MessageRecipient\Command\RemoveTransportConfiguration;
use App\Core\MessageRecipient\Command\SwapTransportConfigurationRank;
use App\Core\MessageRecipient\Command\UpdateTransportConfiguration;
use App\Core\MessageRecipient\Query\MessageRecipientById;
use App\Core\TransportManager\Query\AllTransports;
use App\View\Web\RecipientManagement\Request\TransportConfigurationRequest;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\Translation\t;
use const JSON_PRETTY_PRINT;

final class RecipientTransportConfigController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/recipients/{recipientType}/{id}/transport/add', name: 'web_recipient_management_transport_add', methods: ['GET', 'POST'])]
    public function add(Request $request, string $recipientType, string $id): Response
    {
        $this->denyAccessUnlessGranted($this->getManageRole($recipientType));

        $recipient = $this->queryBus->get(MessageRecipientById::withId($id));
        if (null === $recipient || !$this->isValidRecipientType($recipient->type, $recipientType)) {
            throw new NotFoundHttpException('Recipient not found');
        }

        $transports = $this->queryBus->get(AllTransports::withoutFilter());
        $transportChoices = [];
        foreach ($transports as $transport) {
            // Allow multiple configurations per transport type
            $transportChoices[$transport->getTitle()] = $transport->getKey();
        }

        $configRequest = new TransportConfigurationRequest();
        $formBuilder = $this->createFormBuilder($configRequest)
            ->add('transportKey', ChoiceType::class, [
                'choices' => $transportChoices,
                'label' => 'Transport',
                'placeholder' => 'Select transport...',
            ])
            ->add('isEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Enabled',
            ])
            ->add('vendorSpecificConfig', TextareaType::class, [
                'required' => false,
                'label' => 'Configuration (JSON)',
                'attr' => ['rows' => 6, 'class' => 'font-monospace'],
            ])
            ->add('selectionExpression', TextType::class, [
                'label' => 'Selection Expression',
                'required' => true,
                'attr' => ['class' => 'font-monospace'],
            ])
            ->add('evaluateOtherTransportConfigurations', CheckboxType::class, [
                'required' => false,
                'label' => 'Evaluate Other Configurations',
            ]);

        // Add continueInHierarchy only for groups
        if ($recipient->isGroup()) {
            $formBuilder->add('continueInHierarchy', CheckboxType::class, [
                'required' => false,
                'label' => 'Continue in Hierarchy',
            ]);
        }

        $form = $formBuilder
            ->add('save', SubmitType::class, ['label' => 'Add Configuration'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TransportConfigurationRequest $configRequest */
            $configRequest = $form->getData();

            try {
                if (null === $configRequest->transportKey) {
                    throw new RuntimeException('Transport key is required');
                }

                $this->commandBus->do(new AddTransportConfiguration(
                    $id,
                    $configRequest->transportKey,
                    $configRequest->getVendorSpecificConfigArray(),
                    $configRequest->isEnabled,
                    $configRequest->rank,
                    $configRequest->selectionExpression,
                    $recipient->isGroup() ? $configRequest->continueInHierarchy : null,
                    $configRequest->evaluateOtherTransportConfigurations,
                ));

                $this->addFlash('success', t('Transport configuration added successfully'));

                return $this->redirectToRoute($this->getDetailsRoute($recipientType), ['id' => $id]);
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to add transport configuration: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('recipient-management/_transport/add.html.twig', [
            'form' => $form,
            'recipient' => $recipient,
            'recipientType' => $recipientType,
        ]);
    }

    #[Route('/recipients/{recipientType}/{id}/transport/{configId}/edit', name: 'web_recipient_management_transport_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $recipientType, string $id, string $configId): Response
    {
        $this->denyAccessUnlessGranted($this->getManageRole($recipientType));

        $recipient = $this->queryBus->get(MessageRecipientById::withId($id));
        if (null === $recipient || !$this->isValidRecipientType($recipient->type, $recipientType)) {
            throw new NotFoundHttpException('Recipient not found');
        }

        $config = $recipient->transportConfigurations[$configId] ?? null;
        if (null === $config) {
            throw new NotFoundHttpException('Transport configuration not found');
        }

        $configRequest = new TransportConfigurationRequest();
        $configRequest->transportKey = $config->key;
        $configRequest->isEnabled = $config->isEnabled;
        $jsonEncoded = null !== $config->vendorSpecificConfig
            ? json_encode($config->vendorSpecificConfig, JSON_PRETTY_PRINT)
            : false;
        $configRequest->vendorSpecificConfig = false !== $jsonEncoded ? $jsonEncoded : '';
        $configRequest->rank = $config->rank;
        $configRequest->selectionExpression = $config->selectionExpression;
        $configRequest->continueInHierarchy = $config->continueInHierarchy;
        $configRequest->evaluateOtherTransportConfigurations = $config->evaluateOtherTransportConfigurations;

        $formBuilder = $this->createFormBuilder($configRequest)
            ->add('isEnabled', CheckboxType::class, [
                'required' => false,
                'label' => 'Enabled',
            ])
            ->add('vendorSpecificConfig', TextareaType::class, [
                'required' => false,
                'label' => 'Configuration (JSON)',
                'attr' => ['rows' => 6, 'class' => 'font-monospace'],
            ])
            ->add('selectionExpression', TextType::class, [
                'label' => 'Selection Expression',
                'required' => true,
                'attr' => ['class' => 'font-monospace'],
            ])
            ->add('evaluateOtherTransportConfigurations', CheckboxType::class, [
                'required' => false,
                'label' => 'Evaluate Other Configurations',
            ]);

        // Add continueInHierarchy only for groups
        if ($recipient->isGroup()) {
            $formBuilder->add('continueInHierarchy', CheckboxType::class, [
                'required' => false,
                'label' => 'Continue in Hierarchy',
            ]);
        }

        $form = $formBuilder
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TransportConfigurationRequest $configRequest */
            $configRequest = $form->getData();

            try {
                $this->commandBus->do(new UpdateTransportConfiguration(
                    $id,
                    $configId,
                    $configRequest->getVendorSpecificConfigArray(),
                    $configRequest->isEnabled,
                    $configRequest->rank,
                    $configRequest->selectionExpression,
                    $recipient->isGroup() ? $configRequest->continueInHierarchy : null,
                    $configRequest->evaluateOtherTransportConfigurations,
                ));

                $this->addFlash('success', t('Transport configuration updated successfully'));

                return $this->redirectToRoute($this->getDetailsRoute($recipientType), ['id' => $id]);
            } catch (RuntimeException $e) {
                $this->addFlash('error', t('Failed to update transport configuration: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('recipient-management/_transport/edit.html.twig', [
            'form' => $form,
            'recipient' => $recipient,
            'recipientType' => $recipientType,
            'configId' => $configId,
            'transportKey' => $config->key,
        ]);
    }

    #[Route('/recipients/{recipientType}/{id}/transport/{configId}/remove', name: 'web_recipient_management_transport_remove', methods: ['POST'])]
    public function remove(string $recipientType, string $id, string $configId): RedirectResponse
    {
        $this->denyAccessUnlessGranted($this->getManageRole($recipientType));

        $recipient = $this->queryBus->get(MessageRecipientById::withId($id));
        if (null === $recipient || !$this->isValidRecipientType($recipient->type, $recipientType)) {
            throw new NotFoundHttpException('Recipient not found');
        }

        try {
            $this->commandBus->do(new RemoveTransportConfiguration($id, $configId));
            $this->addFlash('success', t('Transport configuration removed successfully'));
        } catch (RuntimeException $e) {
            $this->addFlash('error', t('Failed to remove transport configuration: {message}', ['message' => $e->getMessage()]));
        }

        return $this->redirectToRoute($this->getDetailsRoute($recipientType), ['id' => $id]);
    }

    #[Route('/recipients/{recipientType}/{id}/transport/{configId}/move-up', name: 'web_recipient_management_transport_move_up', methods: ['POST'])]
    public function moveUp(string $recipientType, string $id, string $configId): RedirectResponse
    {
        $this->denyAccessUnlessGranted($this->getManageRole($recipientType));

        try {
            $this->commandBus->do(new SwapTransportConfigurationRank($id, $configId, true));
        } catch (RuntimeException $e) {
            $this->addFlash('error', t('Failed to move transport configuration: {message}', ['message' => $e->getMessage()]));
        }

        return $this->redirectToRoute($this->getDetailsRoute($recipientType), ['id' => $id]);
    }

    #[Route('/recipients/{recipientType}/{id}/transport/{configId}/move-down', name: 'web_recipient_management_transport_move_down', methods: ['POST'])]
    public function moveDown(string $recipientType, string $id, string $configId): RedirectResponse
    {
        $this->denyAccessUnlessGranted($this->getManageRole($recipientType));

        try {
            $this->commandBus->do(new SwapTransportConfigurationRank($id, $configId, false));
        } catch (RuntimeException $e) {
            $this->addFlash('error', t('Failed to move transport configuration: {message}', ['message' => $e->getMessage()]));
        }

        return $this->redirectToRoute($this->getDetailsRoute($recipientType), ['id' => $id]);
    }

    private function getManageRole(string $recipientType): string
    {
        return match ($recipientType) {
            'person' => 'ROLE_MANAGE_RECIPIENT_INDIVIDUALS',
            'group' => 'ROLE_MANAGE_RECIPIENT_GROUPS',
            'role' => 'ROLE_MANAGE_RECIPIENT_ROLES',
            default => throw new NotFoundHttpException('Invalid recipient type'),
        };
    }

    private function getDetailsRoute(string $recipientType): string
    {
        return match ($recipientType) {
            'person' => 'web_recipient_management_person_details',
            'group' => 'web_recipient_management_group_details',
            'role' => 'web_recipient_management_role_details',
            default => throw new NotFoundHttpException('Invalid recipient type'),
        };
    }

    private function isValidRecipientType(string $actualType, string $expectedType): bool
    {
        return match ($expectedType) {
            'person' => 'PERSON' === $actualType,
            'group' => 'GROUP' === $actualType,
            'role' => 'ROLE' === $actualType,
            default => false,
        };
    }
}
