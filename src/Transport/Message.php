<?php

namespace Abau\MessengerAzureQueueTransport\Transport;

use MicrosoftAzure\Storage\Queue\Models\QueueMessage;

/**
 * Class Message
 */
class Message
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var QueueMessage
     */
    private $original;

    /**
     * Message constructor.
     *
     * @param string $body
     * @param array $headers
     */
    public function __construct(string $body, array $headers = [])
    {
        $this->body = $body;
        $this->headers = $headers;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @return array|null
     */
    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    /**
     * @return QueueMessage|null
     */
    public function getOriginal(): ?QueueMessage
    {
        return $this->original;
    }

    /**
     * @param QueueMessage|null $original
     * @return Message
     */
    public function setOriginal(?QueueMessage $original): Message
    {
        $this->original = $original;
        return $this;
    }
}