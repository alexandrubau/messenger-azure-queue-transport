<?php

namespace Abau\MessengerAzureQueueTransport\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * Class QueueReceiver
 */
class QueueReceiver implements ReceiverInterface
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
    public function get(): iterable
    {
        try {

            $messages = $this->queue->get();

        } catch (\Exception $error) {

            throw new TransportException($error->getMessage(), 0 , $error);
        }

        return array_map(function (Message $message) {

            return $this->createEnvelopeFromMessage($message);

        }, $messages);
    }

    /**
     * Returns all the messages (up to the limit) in this receiver.
     *
     * Messages should be given the same stamps as when using ReceiverInterface::get().
     *
     * @return Envelope[]|iterable
     */
    public function peekMessages(): iterable
    {
        try {

            $messages = $this->queue->peekMessages();

        } catch (\Exception $error) {
            throw new TransportException($error->getMessage(), 0 , $error);
        }

        return array_map(function (Message $message) {

            return $this->createEnvelopeFromMessage($message);

        }, $messages);
    }

    /**
     * @inheritDoc
     */
    public function ack(Envelope $envelope): void
    {
        $this->reject($envelope);
    }

    /**
     * @inheritDoc
     */
    public function reject(Envelope $envelope): void
    {
        try {

            $idStamp = $this->findTransportMessageIdStamp($envelope);
            $azureStamp = $this->findAzureStorageQueueReceivedStamp($envelope);

            $this->queue->delete($idStamp->getId(), $azureStamp->getPopReceipt());

        } catch (\Exception $error) {

            throw new TransportException($error->getMessage(), 0, $error);
        }
    }

    /**
     * Create envelope object based on queue message.
     *
     * @param Message $message
     * @return Envelope
     */
    private function createEnvelopeFromMessage(Message $message): Envelope
    {
        try {

            $envelope = $this->serializer->decode([
                'body' => $message->getBody(),
                'headers' => $message->getHeaders(),
            ]);

        } catch (MessageDecodingFailedException $exception) {

            $this->queue->delete($message);

            throw $exception;
        }

        $stamps = [
            new TransportMessageIdStamp($message->getOriginal()->getMessageId()),
            new QueueReceivedStamp($message->getOriginal()->getPopReceipt())
        ];

        if ($message->getOriginal()->getDequeueCount() > 1) {
            $stamps[] = new RedeliveryStamp($message->getOriginal()->getDequeueCount());
        }

        return $envelope->with(...$stamps);
    }

    /**
     * Retrieves stamp from envelope.
     *
     * @param Envelope $envelope
     * @return QueueReceivedStamp
     */
    private function findAzureStorageQueueReceivedStamp(Envelope $envelope): QueueReceivedStamp
    {
        /** @var QueueReceivedStamp|null $stamp */
        $stamp = $envelope->last(QueueReceivedStamp::class);

        if (null === $stamp) {
            throw new LogicException('No QueueReceivedStamp found on the envelope.');
        }

        return $stamp;
    }

    /**
     * Retrieves stamp from envelope.
     *
     * @param Envelope $envelope
     * @return TransportMessageIdStamp
     */
    private function findTransportMessageIdStamp(Envelope $envelope): TransportMessageIdStamp
    {
        /** @var TransportMessageIdStamp|null $stamp */
        $stamp = $envelope->last(TransportMessageIdStamp::class);

        if (null === $stamp) {
            throw new LogicException('No TransportMessageIdStamp found on the envelope.');
        }

        return $stamp;
    }
}
