<?php

declare(strict_types=1);

namespace App\View\Web\Admin;

use App\Core\Contracts\Bus\QueryBus;
use App\Core\TransportManager\Model\TransportConfiguration;
use App\Core\TransportManager\Query\AllTransports;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('TransportList', template: 'admin/_component/transport-list.html.twig')]
#[IsGranted('ROLE_TRANSPORT_CONFIGURATION_VIEWER')]
class TransportListComponent
{
    use DefaultActionTrait;

    public function __construct(private QueryBus $queryBus)
    {
    }

    /**
     * @return iterable<TransportConfiguration>
     */
    public function getTransports(): iterable
    {
        return $this->queryBus->get(AllTransports::withoutFilter());
    }
}
