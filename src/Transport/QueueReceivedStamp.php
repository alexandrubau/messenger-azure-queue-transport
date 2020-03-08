<?php

namespace Abau\MessengerAzureQueueTransport\Transport;

use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

/**
 * Class QueueReceivedStamp
 */
class QueueReceivedStamp implements NonSendableStampInterface
{
    /**
     * @var string
     */
    private $popReceipt;

    /**
     * @param string $popReceipt
     */
    public function __construct($popReceipt)
    {
        $this->popReceipt = $popReceipt;
    }

    /**
     * @return string|null
     */
    public function getPopReceipt(): ?string
    {
        return $this->popReceipt;
    }
}