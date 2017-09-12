<?php
namespace MQK\DHT;

use Monolog\Logger;
use MQK\DHT\Event\NodeDiscoveryEvent;
use MQK\DHT\Node;
use MQK\RedisFactory;
use React\EventLoop\Factory as EventLoopFactory;
use React\Datagram\Factory as DatagramFactory;
use React\Datagram\Socket;
use React\EventLoop\LoopInterface;

class DiscoveryListener implements DiscoveryInterface
{
    /**
     * @var LoopInterface
     */
    private $loop;

    private $discovery;

    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var Logger
     */
    private $logger;

    private $node;

    public function __construct(NodeDiscoveryEvent $event)
    {
        $this->loop = EventLoopFactory::create();
        $factory = new DatagramFactory($this->loop);
        $redis = RedisFactory::shared()->createRedis();
        $this->logger = new Logger(__CLASS__);
        $this->redis = $redis;

        $id = base64_decode($event->id);
        $targetId = base64_decode($event->targetId);
        $this->node = new Node($targetId, $event->host, $event->port);

        $discovery = new Discovery(
            $this,
            $factory,
            $redis,
            $id,
            $this->node
        );
        $this->loop->addPeriodicTimer(2, function () {
            $this->completed($this->node);
            $this->loop->stop();
        });

        $this->loop->run();
    }

    public function completed(Node $node)
    {
        $this->redis->hSet("dht", base64_encode($node->nid), json_encode($node));
        $this->loop->stop();
    }
}