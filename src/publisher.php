<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//$host = '127.0.0.1';
//$port = 5672;
//$vhost = '/';
//$user = 'guest';
//$password = 'guest';

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

$faker = Faker\Factory::create();

$limit = 10;
$iteration = 0;

while ($iteration < $limit){
    $messageBody = json_encode([
        'name'      => $faker->name,
        'email'     => $faker->email,
        'address'   => $faker->address,
        'subscribed'=> true
    ]);
    $message = new AMQPMessage($messageBody, [
        'content_type' => 'application/json',
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
    ]);

    $channel->basic_publish($message, $exchange);

    $iteration++;
}

echo 'Finished publishing to queue: '. $queue . PHP_EOL;

$channel->close();
$connection->close();
