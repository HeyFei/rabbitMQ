<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$host = 'salamander.rmq.cloudamqp.com';
$port = 5672;
$vhost = 'qajfnipc';
$user = 'qajfnipc';
$password = 'Z0gMA5RIXaBBYokufVaUQm4eOBTZy4ur';

$exchange = 'subscribers';
$queue = 'gurucoder_subscribers';

$connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);
$channel->exchange_declare($exchange, 'direct', false, true, false);
$channel->queue_bind($queue, $exchange);

function process_message(AMQPMessage $message){
    $messageBody = json_decode($message->body);
    $email = $messageBody->email;
    file_put_contents(dirname(__DIR__) . '/data/' . $email . '.json' ,$message->body);
    echo " [x] Received ", $email, "\n";
    sleep(substr_count($message->body, '.'));
    echo " [x] Done", "\n";


    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);

//    if ($message->body === 'quit') {
//        $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
//    }
}
$consumerTag = 'local.imac.consumer';


$channel->basic_consume($queue, $consumerTag, false, false, false, false, 'process_message');


function shutdown($channel, $connection)
{
    $channel->close();
    $connection->close();
}

register_shutdown_function('shutdown', $channel, $connection);

while (count($channel->callbacks)) {
    $channel->wait();
}
