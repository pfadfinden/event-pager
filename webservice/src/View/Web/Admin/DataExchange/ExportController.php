<?php

declare(strict_types=1);

namespace App\View\Web\Admin\DataExchange;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\DataExchange\Model\ExportFormat;
use App\Core\DataExchange\Port\ExportFormatter;
use App\Core\DataExchange\Port\FormatAdapterFactory;
use App\Core\DataExchange\Query\ExportChannels;
use App\Core\DataExchange\Query\ExportMessageHistory;
use App\Core\DataExchange\Query\ExportPagers;
use App\Core\DataExchange\Query\ExportRecipients;
use App\Core\DataExchange\Query\ExportTransportConfigurations;
use App\Core\DataExchange\ReadModel\ChannelExportRow;
use App\Core\DataExchange\ReadModel\MessageHistoryExportRow;
use App\Core\DataExchange\ReadModel\PagerExportRow;
use App\Core\DataExchange\ReadModel\RecipientExportRow;
use App\Core\DataExchange\ReadModel\TransportConfigurationExportRow;
use Generator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function date;
use function flush;
use function in_array;
use function sprintf;

#[IsGranted('ROLE_ADMIN')]
final class ExportController extends AbstractController
{
    private const array ENTITY_TYPES = [
        'recipients',
        'pagers',
        'channels',
        'transports',
        'message_history',
    ];

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly FormatAdapterFactory $formatFactory,
    ) {
    }

    #[Route('/admin/export', name: 'web_admin_export', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $entityType = $request->query->getString('entity_type');

        if ('' !== $entityType && in_array($entityType, self::ENTITY_TYPES, true)) {
            return $this->processExport($entityType);
        }

        return $this->render('admin/data-exchange/export.html.twig', [
            'entityTypes' => self::ENTITY_TYPES,
        ]);
    }

    /**
     * @param 'recipients'|'pagers'|'channels'|'transports'|'message_history' $entityType
     */
    private function processExport(string $entityType): StreamedResponse
    {
        $formatter = $this->formatFactory->createExporter(ExportFormat::CSV);

        return match ($entityType) {
            'recipients' => $this->createStreamedResponse(
                $formatter->formatStreaming(
                    $this->queryBus->get(ExportRecipients::all()),
                    RecipientExportRow::csvHeaders(),
                ),
                'recipients',
                $formatter,
            ),
            'pagers' => $this->createStreamedResponse(
                $formatter->formatStreaming(
                    $this->queryBus->get(ExportPagers::all()),
                    PagerExportRow::csvHeaders(),
                ),
                'pagers',
                $formatter,
            ),
            'channels' => $this->createStreamedResponse(
                $formatter->formatStreaming(
                    $this->queryBus->get(ExportChannels::all()),
                    ChannelExportRow::csvHeaders(),
                ),
                'channels',
                $formatter,
            ),
            'transports' => $this->createStreamedResponse(
                $formatter->formatStreaming(
                    $this->queryBus->get(ExportTransportConfigurations::all()),
                    TransportConfigurationExportRow::csvHeaders(),
                ),
                'transport_configurations',
                $formatter,
            ),
            'message_history' => $this->createStreamedResponse(
                $formatter->formatStreaming(
                    $this->queryBus->get(ExportMessageHistory::all()),
                    MessageHistoryExportRow::csvHeaders(),
                ),
                'message_history',
                $formatter,
            ),
        };
    }

    /**
     * @param Generator<string> $contentGenerator
     */
    private function createStreamedResponse(
        Generator $contentGenerator,
        string $filename,
        ExportFormatter $formatter,
    ): StreamedResponse {
        $response = new StreamedResponse(function () use ($contentGenerator): void {
            foreach ($contentGenerator as $chunk) {
                echo $chunk;
                flush();
            }
        });

        $response->headers->set('Content-Type', $formatter->getContentType());
        $response->headers->set(
            'Content-Disposition',
            sprintf(
                'attachment; filename="%s_%s%s"',
                $filename,
                date('Y-m-d_His'),
                $formatter->getFileExtension(),
            ),
        );

        return $response;
    }
}
