<?php

namespace App\Jobs;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQMessageProducer implements ShouldQueue
{
    public function handle(): void
    {
        // Setup connection
        $connection = new AMQPStreamConnection(
            host: config("rabbitmq.host"),
            port: config("rabbitmq.port"),
            user: config("rabbitmq.user"),
            password: config("rabbitmq.password"),
            vhost: config("rabbitmq.vhost"),
            connection_timeout: config("rabbitmq.options.connection_timeout"),
            read_write_timeout: config("rabbitmq.options.read_write_timeout"),
            heartbeat: config("rabbitmq.options.heartbeat"),
            channel_rpc_timeout: config("rabbitmq.options.channel_rpc_timeout")
        );

        $channel = $connection->channel();

        // Declare a queue
        $channel->queue_declare('your_queue_name', false, false, false, false);

        $messageBody = 'YOUR MESSAGE';
        $message = new AMQPMessage($messageBody);

        // Publish the message to the queue
        $channel->basic_publish($message, '', 'your_queue_name');

        // Close the channel and connection
        $channel->close();
        $connection->close();
    }
}