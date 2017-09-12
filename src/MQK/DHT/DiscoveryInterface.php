<?php
namespace MQK\DHT;


interface DiscoveryInterface
{
    /**
     * 当节点扫描完成
     *
     * @param Node $node
     * @return void
     */
    function completed(Node $node);
}