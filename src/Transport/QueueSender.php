<?php

namespace Abau\MessengerAzureQueueTransport\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * Class QueueSender
 */
class QueueSender implements SenderInterface
{
    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * QueueReceiver constructor.
     *
     * @param Queue $queue
     * @param SerializerInterface $serializer
     */
    public function __construct(Queue $queue, SerializerInterface $serializer)
    {
        $this->queue = $queue;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function send(Envelope $envelope): Envelope
    {
        $encodedEnvelope = $this->serializer->encode($envelope);

        $message = $this->createMessage($encodedEnvelope['body'], $encodedEnvelope['headers'] ?? []);

        try {

            $this->queue->send($message);

        } catch (\Exception $error) {

            throw new TransportException($error->getMessage(), 0, $error);
        }

        return $envelope->with(new TransportMessageIdStamp($message->getOriginal()->getMessageId()));
    }

    /**
     * Create message object.
     *
     * @param string $body
     * @param array $headers
     * @return Message
     */
    private function createMessage(string $body, array $headers = []): Message
    {
        return new Message($body, $headers);
    }
}