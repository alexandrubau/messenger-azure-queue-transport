<?php

use Abau\MessengerAzureQueueTransport\Transport\QueueTransport;
use Abau\MessengerAzureQueueTransport\Transport\QueueTransportFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

/**
 * Class QueueTransportFactoryTest
 */
class QueueTransportFactoryTest extends TestCase
{
    /**
     * @var QueueTransportFactory
     */
    private static $factory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public static function setUpBeforeClass(): void
    {
        self::$factory = new QueueTransportFactory();
    }

    public function setUp(): void
    {
        $this->serializer = $this->createStub(SerializerInterface::class);
    }

    public function testCanHandleDsn()
    {
        $this->assertNotTrue(self::$factory->supports('abc://', []));
        $this->assertTrue(self::$factory->supports('azurequeue://', []));
    }

    public function testCanCreateTransport()
    {
        $this->assertInstanceOf(QueueTransport::class, self::$factory->createTransport('', [], $this->serializer));
    }

    public function testCanDefineOptionsInDsn(): void
    {
        $options = [
            'queue_name' => 'queue_from_options',
            'visibility_timeout' => 0,
            'time_to_live' => 0,
            'results_limit' => 0,
        ];

        $dsn = 'azurequeue://username:password@default?queue_name=queue_from_dsn&visibility_timeout=1&time_to_live=1&results_limit=1';

        $transport = self::$factory->createTransport($dsn, $options, $this->serializer);

        $reflectionClass = new ReflectionClass($transport);
        $reflectionProperty = $reflectionClass->getProperty('options');
        $reflectionProperty->setAccessible(true);
        $transportOptions = $reflectionProperty->getValue($transport);

        $this->assertSame('queue_from_dsn', $transportOptions['queue_name']);
        $this->assertSame(1, $transportOptions['visibility_timeout']);
        $this->assertSame(1, $transportOptions['time_to_live']);
        $this->assertSame(1, $transportOptions['results_limit']);
    }
}