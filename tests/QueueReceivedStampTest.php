<?php

use Abau\MessengerAzureQueueTransport\Transport\QueueReceivedStamp;
use PHPUnit\Framework\TestCase;

/**
 * Class QueueReceivedStampTest
 */
class QueueReceivedStampTest extends TestCase
{
    public function testCanRetrievePopReceipt()
    {
        $popReceipt = '123abc';

        $stamp = new QueueReceivedStamp($popReceipt);

        $this->assertEquals($popReceipt, $stamp->getPopReceipt());
    }
}