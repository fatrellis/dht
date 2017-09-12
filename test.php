<?php

require "vendor/autoload.php";

ini_set('memory_limit', '1024M');

use MQK\DHT\Coder\Encode;
use MQK\DHT\Coder\Decode;
use MQK\DHT\Node;
use React\EventLoop\Factory as EventLoopFactory;
use React\Datagram\Factory as DatagramFactory;
use React\Datagram\Socket;

$nodes = array(
    array('router.bittorrent.com', 6881),
    array('dht.transmissionbt.com', 6881),
    array('router.utorrent.com', 6881)
);

$foundNodes = [];
$scanedNodes = [];



use \MQK\DHT\Coder\Utils;

$id = sha1(Utils::entropy(), true);
$id2 = sha1(Utils::entropy(), true);

$loop = EventLoopFactory::create();
$datagramFactory = new DatagramFactory($loop);
$datagramFactory->createServer('udp://0.0.0.0:6882')
    ->then(function (Socket $server) {
        $server->on('message', function($message, $address, $server) {
            var_dump($message);

        });
    },
    function (Exception $e) {
        echo $e->getMessage() . PHP_EOL;
    },
    function ($arg) {
        var_dump($arg);
    }
);

class DiscoveryController
{
    private $id;
    private $queue = [];
    private $nodes = [];
    private $count = 0;

    const MAX = 10;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function addNode($node)
    {
        $this->nodes[] = $node;
        $this->queue[] = $node;

        $this->discovery();
    }

    public function discovery()
    {
//        var_dump(count($this->queue));
        if (count($this->queue) < self::MAX) {
            $node = array_shift($this->queue);

            new Discovery($this, $this->id, $node);
        }
    }

    public function discoveryComplete($discovery)
    {
        $this->count += 1;
        echo "Count {$this->count}\n";
    }
}

$controller = new DiscoveryController($id);

use MQK\DHT\Discovery;
$redis = \MQK\RedisFactory::shared()->createRedis();
if ($argc > 2) {
    $ip = $argv[1];
    $port = $argv[2];
} else {
    $ip = '67.215.246.10';
    $port = 6881;
}
$d = new Discovery(null, $datagramFactory, $redis, $id, new Node($id2, $ip, $port));

$loop->run();