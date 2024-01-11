<?php

namespace Abau\MessengerAzureQueueTransport\Transport;

use Symfony\Component\Messenger\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Class QueueTransportFactory
 */
class QueueTransportFactory implements TransportFactoryInterface
{
    private const OPTIONS_TYPE_MAPPING = [
        'queue_name' => 'string',
        'visibility_timeout' => 'integer',
        'time_to_live' => 'integer',
        'results_limit' => 'integer',
    ];

    /**
     * @inheritDoc
     */
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        return new QueueTransport(
            $dsn,
            $this->computeOptions($dsn, $options),
            $serializer
        );
    }

    /**
     * @inheritDoc
     */
    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'azurequeue://');
    }

    /**
     * @param string $dsn
     * @param array $options
     *
     * @return array
     */
    private function computeOptions(string $dsn, array $options): array
    {
        $resultOptions = $options;

        if (false === $parsedUrl = parse_url($dsn)) {
            throw new InvalidArgumentException('The given DSN is invalid.');
        }

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $dsnOptions);
            $resultOptions = array_merge($options, $dsnOptions);
        }

        return $this->castOptionsToTheRightType($resultOptions);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function castOptionsToTheRightType(array $options): array
    {
        foreach ($options as $key => $value) {
            if (!isset(self::OPTIONS_TYPE_MAPPING[$key])) {
                continue;
            }

            $options[$key] = match (self::OPTIONS_TYPE_MAPPING[$key]) {
                'integer' => filter_var($value, \FILTER_VALIDATE_INT),
                'string' => (string)$value,
                default => $value,
            };
        }

        return $options;
    }
}