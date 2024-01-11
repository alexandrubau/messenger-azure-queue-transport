# messenger-azure-queue-transport
Azure Queue transport for Symfony's Messenger component.

[![Travis (.org)](https://img.shields.io/travis/alexandrubau/messenger-azure-queue-transport?style=flat-square)](https://travis-ci.org/alexandrubau/messenger-azure-queue-transport)
[![Packagist Version](https://img.shields.io/packagist/v/alexandrubau/messenger-azure-queue-transport?style=flat-square)](https://packagist.org/packages/alexandrubau/messenger-azure-queue-transport)
[![Software License](https://img.shields.io/github/license/alexandrubau/messenger-azure-queue-transport?style=flat-square)](https://github.com/alexandrubau/messenger-azure-queue-transport/blob/master/LICENSE)

## Installation

The messenger-azure-queue-transport component requires PHP 7.3+ and Symfony 4.3+.

You can install this component using Symfony Flex:

```
composer require alexandrubau/messenger-azure-queue-transport
```

## Basic usage

Set environment variable:

```
MESSENGER_TRANSPORT_DSN=azurequeue://<account_name>:<account_key>@default
```

In case your Account Name or Account Key contain special characters, you can use PHP's `rawurlencode()` function to encode them.

Set messenger transport config:

```yaml
framework:
    messenger:
        transports:
            azure_queues:
                dsn: '%env(MESSENGER_TRANSPORT_DSN)%'
                options:
                    queue_name: <your_queue_name>
                    visibility_timeout: <visibility_timeout_in_seconds>
                    time_to_live: <time_to_live_in_seconds>
                    results_limit: <how_many_messages_to_read>
```
Options can be configured via the DSN or via the options key under the transport in ```messenger.yaml```

Example: ```MESSENGER_TRANSPORT_DSN=azurequeue://<account_name>:<account_key>@default?queue_name=<your_queue_name>&visibility_timeout=<visibility_timeout_in_seconds>&time_to_live=<time_to_live_in_seconds>&results_limit=<how_many_messages_to_read>```

Don't forget to create the queue with the supplied name in Azure Queue Storage.

## Further reading

1. [The Messenger Component](https://symfony.com/doc/current/components/messenger.html)
2. [Messenger: Sync & Queued Message Handling](https://symfony.com/doc/current/messenger.html)
3. [Azure Storage Queue](https://docs.microsoft.com/en-gb/azure/storage/queues/?toc=%2fazure%2fstorage%2fqueues%2ftoc.json)
4. [Azure Storage Queue REST API](https://docs.microsoft.com/en-gb/rest/api/storageservices/queue-service-rest-api)
