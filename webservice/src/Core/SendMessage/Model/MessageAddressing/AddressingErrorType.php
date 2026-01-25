<?php

declare(strict_types=1);

namespace App\Core\SendMessage\Model\MessageAddressing;

enum AddressingErrorType: string
{
    case NO_TRANSPORT_CONFIGURATIONS = 'no_transport_configurations';
    case NO_MATCHING_CONFIGURATIONS = 'no_matching_configurations';
    case EXPRESSION_EVALUATION_FAILED = 'expression_evaluation_failed';
    case EMPTY_GROUP_NO_CONFIGURATIONS = 'empty_group_no_configurations';
    case TRANSPORT_NOT_FOUND = 'transport_not_found';
}
