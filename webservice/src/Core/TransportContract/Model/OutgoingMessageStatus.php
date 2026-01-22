<?php

declare(strict_types=1);

namespace App\Core\TransportContract\Model;

/**
 * Enum of all status an outgoing message can reach.
 *
 * Transports are not required to provide all events. For example, a broadcast
 * transport with unidirectional communication only will likely nor provide
 * any status beyond "TRANSMITTED".
 */
enum OutgoingMessageStatus: int
{
    /**
     * The outgoing message has been created and is ready to be sent to transports.
     */
    case NOT_INITIATED = -2;
    /**
     * The outgoing message has been created and is ready to be sent to transports.
     */
    case INITIATED = -1;
    /**
     * The transport has accepted the message and queued it for async processing.
     */
    case QUEUED = 0;
    /**
     * The transport has transmitted the message, it is on its way to the recipient.
     */
    case TRANSMITTED = 10;
    /**
     * The message was delivered to the recipients' device.
     */
    case DELIVERED = 20;
    /**
     * The recipient has read the message.
     */
    case READ = 30;
    /**
     * The recipient personally acknowledged the message.
     */
    case ACK = 40;
    /**
     * An unspecified error occurred.
     */
    case ERROR = 100;
    /**
     * The transport was not able to transmit or deliver the message within a time limit.
     */
    case TIMEOUT = 101;
}
