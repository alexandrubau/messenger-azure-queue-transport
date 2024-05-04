<?php

namespace Abau\MessengerAzureQueueTransport\Transport;

use MicrosoftAzure\Storage\Queue\Models\CreateMessageOptions;
use MicrosoftAzure\Storage\Queue\Models\ListMessagesOptions;
use MicrosoftAzure\Storage\Queue\Models\QueueMessage;
use MicrosoftAzure\Storage\Queue\QueueRestProxy;

/**
 * Class Queue
 */
class Queue
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
     * @var QueueRestProxy
     */
    private $client;

    /**
     * Queue constructor.
     *
     * @param string $dsn
     * @param array $options
     */
    public function __construct(string $dsn, array $options)
    {
        $this->dsn = $dsn;
        $this->options = $options;

        $this->client = $this->createClient();
    }

    /**
     * Reads messages from the queue.
     *
     * @return Message[]
     */
    public function get(): array
    {
        $options = new ListMessagesOptions();
        $options->setVisibilityTimeoutInSeconds($this->getOption('visibility_timeout'));
        $options->setNumberOfMessages($this->getOption('results_limit', 1));

        $list = $this->client->listMessages($this->getOption('queue_name'), $options);

        $list = $list->getQueueMessages();

        return array_map(function (QueueMessage $queueMessage) {

            $message = $this->decodeMessage($queueMessage->getMessageText());

            $message->setOriginal($queueMessage);

            return $message;

        }, $list);
    }

    /**
     * Sends message to queue.
     *
     * @param Message $message
     * @return Message
     */
    public function send(Message $message): Message
    {
        $options = new CreateMessageOptions();
        $options->setTimeToLiveInSeconds($this->getOption('time_to_live'));

        $content = $this->encodeMessage($message);

        $result = $this->client->createMessage($this->getOption('queue_name'), $content, $options);

        $message->setOriginal($result->getQueueMessage());

        return $message;
    }

    /**
     * Deletes message from queue.
     *
     * @param Message|string $messageId The message object or the messageId
     * @param string|null $popReceipt
     */
    public function delete($messageId, string $popReceipt = null): void
    {
        if ($messageId instanceof Message) {

            $original = $messageId->getOriginal();

            if (!$original) {

                throw new \InvalidArgumentException('Cannot delete, missing original queue message attribute.');
            }

            $messageId = $original->getMessageId();
            $popReceipt = $original->getPopReceipt();
        }

        if (!$popReceipt) {

            throw new \InvalidArgumentException('Cannot delete, missing pop receipt.');
        }

        $this->client->deleteMessage($this->getOption('queue_name'), $messageId, $popReceipt);
    }

    /**
     * Retrieves the message count.
     *
     * @return int
     */
    public function getMessageCount(): int
    {
        return $this->client->getQueueMetadata($this->getOption('queue_name'))->getApproximateMessageCount();
    }

    /**
     * Creates Azure Storage Queue client.
     *
     * @return QueueRestProxy
     */
    private function createClient(): QueueRestProxy
    {
        $endpoint = $this->getConnectionString($this->dsn);

        return $this->client = QueueRestProxy::createQueueService($endpoint);
    }

    /**
     * Retrieves the endpoint connection string.
     *
     * @param string $dsn
     * @return string
     */
    private function getConnectionString(string $dsn): string
    {
        $connection = 'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s';

        $name = rawurldecode(parse_url($dsn, PHP_URL_USER));
        $key = rawurldecode(parse_url($dsn, PHP_URL_PASS));

        return sprintf($connection, $name, $key);
    }

    /**
     * Retrieves option.
     *
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    private function getOption($name, $default = null)
    {
        if (!array_key_exists($name, $this->options)) {
            return $default;
        }

        return $this->options[$name];
    }

    /**
     * Encodes the message.
     *
     * @param Message $message
     * @return string
     */
    private function encodeMessage(Message $message): string
    {
        return base64_encode(serialize($message));
    }

    /**
     * Decodes the message.
     *
     * @param string $message
     * @return Message
     */
    private function decodeMessage(string $message): Message
    {
        return unserialize(base64_decode($message));
    }
}
