<?php

namespace Abau\MessengerAzureQueueTransport\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * Class QueueTransport
 */
class QueueTransport implements TransportInterface, MessageCountAwareInterface, ListableReceiverInterface
{
    /**
     * @var string
     */
    private $dsn;

    /**
     * @var array
     */
    private $options;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var QueueReceiver
     */
    private $receiver;

    /**
     * @var QueueSender
     */
    private $sender;

    /**
     * QueueTransport constructor.
     *
     * @param string $dsn
     * @param array $options
     * @param SerializerInterface $serializer
     */
    public function __construct(
        string $dsn,
        array $options,
        SerializerInterface $serializer
    )
    {
        $this->dsn = $dsn;
        $this->options = $options;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function get(): iterable
    {
        return $this->getReceiver()->get();
    }

    /**
     * @inheritDoc
     */
    public function ack(Envelope $envelope): void
    {
        $this->getReceiver()->ack($envelope);
    }

    /**
     * @inheritDoc
     */
    public function reject(Envelope $envelope): void
    {
        $this->getReceiver()->reject($envelope);
    }

    /**
     * @inheritDoc
     */
    public function send(Envelope $envelope): Envelope
    {
        return $this->getSender()->send($envelope);
    }

    /**
     * @inheritDoc
     */
    public function getMessageCount(): int
    {
        return $this->getQueue()->getMessageCount();
    }

    /**
     * Builds queue object.
     *
     * @return Queue
     */
    private function getQueue(): Queue
    {
        return $this->queue ?? $this->queue = new Queue($this->dsn, $this->options);
    }

    /**
     * Builds receiver object.
     *
     * @return QueueReceiver
     */
    private function getReceiver(): QueueReceiver
    {
        return $this->receiver ?? $this->receiver = new QueueReceiver($this->getQueue(), $this->serializer);
    }

    /**
     * Builds sender object.
     *
     * @return QueueSender
     */
    private function getSender(): QueueSender
    {
        return $this->sender ?? $this->sender = new QueueSender($this->getQueue(), $this->serializer);
    }

    /**
     * Returns all the messages (up to the limit) in this receiver.
     *
     * Messages should be given the same stamps as when using ReceiverInterface::get().
     *
     * @return Envelope[]|iterable
     */
    public function all(?int $limit = null): iterable
    {
        return $this->getReceiver()->peekMessages();
    }

    /**
     * Returns the Envelope by id or none.
     *
     * Message should be given the same stamps as when using ReceiverInterface::get().
     */
    public function find(mixed $id): ?Envelope
    {
        throw new \Exception('Azure Storage Queues do not support message lookup by id');
    }
}
