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
}