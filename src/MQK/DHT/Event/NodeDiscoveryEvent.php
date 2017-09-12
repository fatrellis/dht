<?php
namespace MQK\DHT\Event;

use Symfony\Component\EventDispatcher\Event;

class NodeDiscoveryEvent extends Event
{
    const NAME = "node.discovery";

    public $host;
    public $port;
    public $id;
    public $targetId;

    public function __construct($id, $host, $port, $targetId)
    {
        $this->id = $id;
        $this->host = $host;
        $this->port = $port;
        $this->targetId = $targetId;
    }
}