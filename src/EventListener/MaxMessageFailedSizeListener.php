<?php

namespace Abau\MessengerAzureQueueTransport\EventListener;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Stamp\StampInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class MaxMessageFailedSizeListener implements EventSubscriberInterface
{
    // Azure allows 64KB message length, we'll use less as the message is base64 encoded when sent to Azure
    private const MAX_MESSAGE_LENGTH = 50000;
    private const MAX_STACK_TRACE_ITEMS = 5;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $encodedEnvelope = $this->serializer->encode($envelope);

        if (strlen($encodedEnvelope['body']) > self::MAX_MESSAGE_LENGTH) {
            $this->cleanupEnvelope($envelope);
        }
    }

    /**
     * @param Envelope $envelope
     * @return void
     */
    private function cleanupEnvelope(Envelope $envelope): void
    {
        $stampTypes = [
            'Symfony\Component\Messenger\Stamp\RedeliveryStamp', // symfony <5.2
            'Symfony\Component\Messenger\Stamp\ErrorDetailsStamp', // symfony >5.2
        ];

        foreach ($stampTypes as $stampType) {
            $stamps = $envelope->all($stampType);
            $stamps = array_map([$this, 'processStamp'], $stamps);

            $envelope->withoutAll($stampType);
            $envelope->with(...$stamps);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must have higher priority than SendFailedMessageToFailureTransportListener
            // and lower priority than AddErrorDetailsStampListener
            WorkerMessageFailedEvent::class => ['onMessageFailed', 150],
        ];
    }

    private function processStamp(StampInterface $stamp): StampInterface
    {
        // `RedeliveryStamp::getFlattenException()` was removed in symfony 6.0
        if (!method_exists($stamp, 'getFlattenException')) {
            return $stamp;
        }

        $flattenException = $stamp->getFlattenException();
        if ($flattenException === null) {
            return $stamp;
        }

        $this->cleanupStackTrace($flattenException);

        $previous = $flattenException->getPrevious();
        if ($previous === null) {
            return $stamp;
        }

        $this->cleanupStackTrace($previous);
        $previous->setPrevious(null);

        return $stamp;
    }

    private function cleanupStackTrace(FlattenException $flattenException): void
    {
        $trace = array_slice($flattenException->getTrace(), 0, self::MAX_STACK_TRACE_ITEMS);
        $flattenException->setTrace($trace, $flattenException->getFile(), $flattenException->getLine());
    }
}
