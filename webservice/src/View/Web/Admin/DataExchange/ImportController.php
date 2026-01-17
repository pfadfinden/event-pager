<?php

declare(strict_types=1);

namespace App\View\Web\Admin\DataExchange;

use App\Core\DataExchange\Command\ImportChannels;
use App\Core\DataExchange\Command\ImportPagers;
use App\Core\DataExchange\Command\ImportRecipients;
use App\Core\DataExchange\Command\ImportTransportConfigurations;
use App\Core\DataExchange\Handler\ImportChannelsHandler;
use App\Core\DataExchange\Handler\ImportPagersHandler;
use App\Core\DataExchange\Handler\ImportRecipientsHandler;
use App\Core\DataExchange\Handler\ImportTransportConfigurationsHandler;
use App\Core\DataExchange\Model\ExportFormat;
use App\Core\DataExchange\Model\ImportConflictStrategy;
use App\Core\DataExchange\Model\ImportResult;
use App\View\Web\Admin\DataExchange\Request\ImportRequest;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\File;
use Throwable;
use function Symfony\Component\Translation\t;

#[IsGranted('ROLE_ADMIN')]
final class ImportController extends AbstractController
{
    public function __construct(
        private readonly ImportRecipientsHandler $importRecipientsHandler,
        private readonly ImportPagersHandler $importPagersHandler,
        private readonly ImportChannelsHandler $importChannelsHandler,
        private readonly ImportTransportConfigurationsHandler $importTransportConfigurationsHandler,
    ) {
    }

    #[Route('/admin/import', name: 'web_admin_import', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $importRequest = new ImportRequest();
        $form = $this->createFormBuilder($importRequest)
            ->add('entityType', ChoiceType::class, [
                'label' => t('Entity to Import'),
                'choices' => [
                    'recipients',
                    'pagers',
                    'channels',
                    'transports',
                ],
                'choice_label' => fn (mixed $choice): TranslatableMessage => match ($choice) {
                    'recipients' => t('Recipients'),
                    'pagers' => t('Pagers'),
                    'channels' => t('Channels'),
                    'transports' => t('Transport Configurations'),
                    default => t('Unknown'),
                },
            ])
            ->add('conflictStrategy', ChoiceType::class, [
                'label' => t('Conflict Strategy'),
                'choices' => [
                    ImportConflictStrategy::SKIP->value,
                    ImportConflictStrategy::UPDATE->value,
                    ImportConflictStrategy::ERROR->value,
                ],
                'choice_label' => fn (mixed $choice): TranslatableMessage => match ($choice) {
                    'skip' => t('Skip existing', domain: 'messages'),
                    'update' => t('Update existing', domain: 'messages'),
                    'error' => t('Error on conflict', domain: 'messages'),
                    default => t('Unknown'),
                },
            ])
            ->add('file', FileType::class, [
                'label' => t('CSV File'),
                'constraints' => [
                    new File(maxSize: '10M', mimeTypes: ['text/csv', 'text/plain', 'application/csv'], mimeTypesMessage: 'Please upload a valid CSV file'),
                ],
            ])
            ->add('import', SubmitType::class, ['label' => t('Import')])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ImportRequest $importRequest */
            $importRequest = $form->getData();

            /** @var UploadedFile $file */
            $file = $importRequest->file;
            $filePath = $file->getRealPath();
            $strategy = ImportConflictStrategy::from($importRequest->conflictStrategy);

            try {
                $result = $this->processImport(
                    $importRequest->entityType,
                    false !== $filePath ? $filePath : '',
                    $strategy,
                );

                $this->addImportResultFlashes($result);

                return $this->redirectToRoute('web_admin_import');
            } catch (Throwable $e) {
                $this->addFlash('error', t('Import failed: {message}', ['message' => $e->getMessage()]));
            }
        }

        return $this->render('admin/data-exchange/import.html.twig', [
            'form' => $form,
        ]);
    }

    private function processImport(
        string $entityType,
        string $filePath,
        ImportConflictStrategy $strategy,
    ): ImportResult {
        return match ($entityType) {
            'recipients' => $this->importRecipientsHandler->__invoke(
                ImportRecipients::fromFile($filePath, ExportFormat::CSV, $strategy),
            ),
            'pagers' => $this->importPagersHandler->__invoke(
                ImportPagers::fromFile($filePath, ExportFormat::CSV, $strategy),
            ),
            'channels' => $this->importChannelsHandler->__invoke(
                ImportChannels::fromFile($filePath, ExportFormat::CSV, $strategy),
            ),
            'transports' => $this->importTransportConfigurationsHandler->__invoke(
                ImportTransportConfigurations::fromFile($filePath, ExportFormat::CSV, $strategy),
            ),
            default => throw new InvalidArgumentException('Unknown entity type: '.$entityType),
        };
    }

    private function addImportResultFlashes(ImportResult $result): void
    {
        if ($result->imported > 0) {
            $this->addFlash('success', t('{count} records imported successfully', ['count' => $result->imported]));
        }

        if ($result->updated > 0) {
            $this->addFlash('info', t('{count} records updated', ['count' => $result->updated]));
        }

        if ($result->skippedCount > 0) {
            $this->addFlash('warning', t('{count} records skipped (already exist)', ['count' => $result->skippedCount]));
        }

        foreach ($result->errors as $error) {
            $this->addFlash('error', $error);
        }
    }
}
