<?php

use Abau\MessengerAzureQueueTransport\Transport\Message;
use MicrosoftAzure\Storage\Queue\Models\QueueMessage;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageTest
 */
class MessageTest extends TestCase
{
    public function testCanRetrieveBody()
    {
        $body = 'test';

        $message = new Message($body);

        $this->assertEquals($body, $message->getBody());
    }

    public function testCanRetrieveHeaders()
    {
        $body = 'test';
        $headers = [
            'key' => 'value'
        ];

        $message = new Message($body, $headers);

        $this->assertEquals($headers, $message->getHeaders());
    }

    public function testCanRetrieveOriginal()
    {
        $body = 'test';
        $headers = [
            'key' => 'value'
        ];

        $message = new Message($body, $headers);

        $original = $this->createStub(QueueMessage::class);

        $this->assertInstanceOf(Message::class, $message->setOriginal($original));
        $this->assertEquals($original, $message->getOriginal());
    }
}