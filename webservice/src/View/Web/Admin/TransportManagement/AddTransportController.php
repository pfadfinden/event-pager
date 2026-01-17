<?php

declare(strict_types=1);

namespace App\View\Web\Admin\TransportManagement;

use App\Core\Contracts\Bus\CommandBus;
use App\Core\IntelPage\Application\IntelPageTransport;
use App\Core\NtfyTransport\Application\NtfyTransport;
use App\Core\TelegramTransport\Application\TelegramTransport;
use App\Core\TransportManager\Command\AddOrUpdateTransportConfiguration;
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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function is_array;
use function Symfony\Component\Translation\t;
use const JSON_THROW_ON_ERROR;

#[Route('/admin/transport/add', name: 'web_admin_transport_add')]
#[IsGranted('ROLE_TRANSPORT_ADMINISTRATOR')]
final class AddTransportController extends AbstractController
{
    private const array TRANSPORT_CHOICES = [
        'IntelPageTransport' => IntelPageTransport::class,
        'TelegramTransport' => TelegramTransport::class,
        'NtfyTransport' => NtfyTransport::class,
    ];

    public function __construct(private readonly CommandBus $commandBus)
    {
    }

    public function __invoke(Request $request): Response
    {
        $transportRequest = new TransportConfigurationRequest();
        $form = $this->createFormBuilder($transportRequest)
            ->add('key', TextType::class, ['label' => 'Key'])
            ->add('title', TextType::class, ['label' => 'Title'])
            ->add('transport', ChoiceType::class, [
                'label' => 'Transport',
                'choices' => self::TRANSPORT_CHOICES,
                'placeholder' => 'Select transport...',
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
            ->add('save', SubmitType::class, ['label' => 'Add Transport'])
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
                    $transportRequest->key,
                    $transportRequest->transport,
                    $transportRequest->title,
                    $transportRequest->enabled,
                    $vendorConfig,
                ));

                $this->addFlash('success', t('Transport created successfully'));

                return $this->redirectToRoute('web_admin_transport_details', ['key' => $transportRequest->key]);
            } catch (JsonException) {
                $this->addFlash('error', t('Invalid JSON in vendor configuration'));
            } catch (InvalidArgumentException $e) {
                $this->addFlash('error', t('Failed to create transport: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('admin/transport/add.html.twig', [
            'form' => $form,
        ]);
    }
}
