<?php

declare(strict_types=1);

namespace App\View\Web\Admin\TransportManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\Contracts\Bus\QueryBus;
use App\Core\IntelPage\Application\IntelPageTransport;
use App\Core\NtfyTransport\Application\NtfyTransport;
use App\Core\TelegramTransport\Application\TelegramTransport;
use App\Core\TransportManager\Command\AddOrUpdateTransportConfiguration;
use App\Core\TransportManager\Query\Transport;
use App\View\Web\Admin\TransportManagement\Request\TransportConfigurationRequest;
use InvalidArgumentException;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function is_array;
use function Symfony\Component\Translation\t;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

#[Route('/admin/transport/{key}/edit', name: 'web_admin_transport_edit')]
#[IsGranted('ROLE_TRANSPORT_ADMINISTRATOR')]
final class EditTransportController extends AbstractController
{
    private const array TRANSPORT_CHOICES = [
        'IntelPageTransport' => IntelPageTransport::class,
        'TelegramTransport' => TelegramTransport::class,
        'NtfyTransport' => NtfyTransport::class,
    ];

    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
    ) {
    }

    public function __invoke(Request $request, string $key): Response
    {
        $transportConfig = $this->queryBus->get(Transport::withKey($key));
        if (null === $transportConfig) {
            throw new NotFoundHttpException('Transport not found');
        }

        $transportRequest = new TransportConfigurationRequest();
        $transportRequest->key = $transportConfig->getKey();
        $transportRequest->title = $transportConfig->getTitle();
        $transportRequest->transport = $transportConfig->getTransport();
        $transportRequest->enabled = $transportConfig->isEnabled();
        $transportRequest->vendorSpecificConfig = null !== $transportConfig->getVendorSpecificConfig()
            ? json_encode($transportConfig->getVendorSpecificConfig(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
            : null;

        $form = $this->createFormBuilder($transportRequest)
            ->add('key', TextType::class, [
                'label' => 'Key',
                'disabled' => true,
            ])
            ->add('title', TextType::class, ['label' => 'Title'])
            ->add('transport', ChoiceType::class, [
                'label' => 'Transport',
                'choices' => self::TRANSPORT_CHOICES,
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'Enabled',
                'required' => false,
            ])
            ->add('vendorSpecificConfig', TextareaType::class, [
                'label' => 'Vendor Configuration (JSON)',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => '{"api_key": "...", "setting": "value"}',
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TransportConfigurationRequest $transportRequest */
            $transportRequest = $form->getData();

            try {
                $vendorConfig = null;
                if (null !== $transportRequest->vendorSpecificConfig && '' !== $transportRequest->vendorSpecificConfig) {
                    $decoded = json_decode((string) $transportRequest->vendorSpecificConfig, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($decoded)) {
                        $vendorConfig = $decoded;
                    }
                }

                $this->commandBus->do(AddOrUpdateTransportConfiguration::with(
                    $key,
                    $transportRequest->transport,
                    $transportRequest->title,
                    $transportRequest->enabled,
                    $vendorConfig,
                ));

                $this->addFlash('success', t('Transport updated successfully'));

                return $this->redirectToRoute('web_admin_transport_details', ['key' => $key]);
            } catch (JsonException) {
                $this->addFlash('error', t('Invalid JSON in vendor configuration'));
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', t('Failed to update transport: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('admin/transport/edit.html.twig', [
            'form' => $form,
            'transport' => $transportConfig,
        ]);
    }
}
