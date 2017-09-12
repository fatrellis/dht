<?php
namespace MQK\DHT;


use Monolog\Logger;
use MQK\DHT\Coder\Decode;
use MQK\DHT\Coder\Encode;
use MQK\DHT\Event\NodeDiscoveryEvent;
use MQK\DHT\Coder\Utils;

class Discovery
{
    private $address;
    private $node;
    private $id;
    private $targetId;
    private $client;
    private $controller;
    private $redis;
    private $ifDiscovery;
    private $logger;

    public function __construct($ifDiscovery, $factory, $redis, $id, $node)
    {
        $this->node = $node;
        $this->redis = $redis;
        $this->ifDiscovery = $ifDiscovery;
        $this->id = $id;
        $this->logger = new Logger(__CLASS__);

        $address = "{$node->ip}:{$node->port}";

        $this->address = $address;
        $this->logger->debug("Sending message to udp://{$address}");

        $factory->createClient("udp://" . $address)->then(
            function ($client) {
                $this->client = $client;
                try {
                    $bMessage = $this->buildMessage($this->id, $this->node->nid);
                } catch (\Exception $e) {
                    var_dump($e);
                }

                $client->on("message", function ($message, $serverAddress, $client) {
                    $this->logger->debug("Received message from {$serverAddress}");
                    $this->message($message, $serverAddress, $client);
                });


                $client->send(Encode::encode($this->buildMessage($this->id, $this->node->nid)));
            }, function ($e) {
                $this->logger->error($e->getMessage());
        }, function ($args) {
            var_dump($args);
        });
    }

    public function message($message, $serverAddress, $client)
    {
        list($host, $port) = explode(":", $serverAddress);
        $message = Decode::decode($message);

        if ('r' == $message['y']) {
            echo "r\n";
            $nodes = $this->decode_nodes($message['r']['nodes']);
            $this->client->close();
            $this->logger->debug(count($nodes));

            foreach ($nodes as $node) {
                $idEncoded = base64_encode($this->id);
                $targetIdEncoded = base64_encode($node->nid);

                $this->logger->debug("Queued node {$idEncoded} {$targetIdEncoded} {$node->ip} {$node->port}");

                if ($this->redis->hExists("dht", $idEncoded)) {
                    $this->logger->debug("Node {$targetIdEncoded} has exists");
                    continue;
                }
                $nodeDiscovery = new NodeDiscoveryEvent($idEncoded, $node->ip, $node->port, $targetIdEncoded);
                \K::dispatch($nodeDiscovery);
            }

            if (null != $this->ifDiscovery)
                $this->ifDiscovery->completed($this->node);
        } else if ('q' == $message['y']) {
            if ('ping' == $message['q']) {
                echo "ping\n";
                var_dump($client);
                $send = array(
                    't' => $message['t'],
                    'y' => 'r',
                    'r' => array(
                        'id' => $this->id
                    )
                );
            }
        }
    }

    function buildMessage($id, $targetId)
    {
        $msg = array(
            't' => Utils::entropy(2),
            'y' => 'q',
            'q' => 'find_node',
            'a' => array(
                'id' => $id,
                'target' => $targetId
            )
        );
        return $msg;
    }

    function decode_nodes($msg){
        // 先判断数据长度是否正确
        if((strlen($msg) % 26) != 0)
            return array();

        $n = array();

        // 每次截取26字节进行解码
        foreach(str_split($msg, 26) as $s){
            // 将截取到的字节进行字节序解码
            $r = unpack('a20nid/Nip/np', $s);
            $n[] = new Node($r['nid'], long2ip($r['ip']), $r['p']);
        }

        return $n;
    }
}