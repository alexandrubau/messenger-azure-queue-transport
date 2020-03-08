<?php

namespace Abau\MessengerAzureQueueTransport\Transport;

use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Class QueueTransportFactory
 */
class QueueTransportFactory implements TransportFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        return new QueueTransport($dsn, $options, $serializer);
    }

    /**
     * @inheritDoc
     */
    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'azurequeue://');
    }
}