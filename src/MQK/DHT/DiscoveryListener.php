<?php
namespace MQK\DHT;

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

    public function __construct(NodeDiscoveryEvent $event)
    {
        $this->loop = EventLoopFactory::create();
        $factory = new DatagramFactory($this->loop);
        $redis = RedisFactory::shared()->createRedis();

        $id = base64_decode($event->id);
        $targetId = base64_decode($event->targetId);

        $discovery = new Discovery(
            $this,
            $factory,
            $redis,
            $id,
            new Node($targetId, $event->host, $event->port)
        );
        $this->loop->addPeriodicTimer(1, function () {
            $this->loop->stop();
        });

        $this->loop->run();
    }

    public function completed()
    {
        $this->loop->stop();
    }
}