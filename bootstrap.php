<?php

use MQK\DHT\Event\NodeDiscoveryEvent;
use MQK\DHT\DiscoveryListener;

K::addListener(NodeDiscoveryEvent::NAME, function($event) {
    $listener = new DiscoveryListener($event);
});

