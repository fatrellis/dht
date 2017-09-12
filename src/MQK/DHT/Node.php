<?php
namespace MQK\DHT;

/**
 * Node 模型
 */
class Node implements \JsonSerializable
{
    /**
     * 保存node id
     * @var string
     */
    private $id;
    /**
     * 保存IP地址
     * @var string
     */
    private $ip;
    /**
     * 保存端口号
     * @var integer
     */
    private $port;

    /**
     * 析构方法, 初始化模型
     * @param string $nid  node id
     * @param string $ip   IP地址
     * @param integer $port 端口号
     * @return void
     */
    public function __construct($id, $ip, $port){
        $this->nid = $id;
        $this->ip = $ip;
        $this->port = $port;
    }

    /**
     * 使外部可获取私有属性
     * @param  string $name 属性名称
     * @return mixed       属性值
     */
    public function __get($name){
        // 检查属性是否存在
        if(isset($this->$name))
            return $this->$name;

        return null;
    }

    /**
     * 使外部可直接对私有属性赋值
     * @param string $name  属性名称
     * @param mixed $value 属性值
     * @return void
     */
    public function __set($name, $value){
        $this->$name = $value;
    }

    /**
     * 检查属性是否设置
     * @param  string  $name 属性名称
     * @return boolean       是否设置
     */
    public function __isset($name){
        return isset($this->$name);
    }

    /**
     * 将Node模型转换为数组
     * @return array 转换后的数组
     */
    public function to_array(){
        return array('id' => $this->id, 'ip' => $this->ip, 'port' => $this->port);
    }

    public function jsonSerialize()
    {
        $jsonObject = $this->to_array();
        $jsonObject['id'] = base64_encode($jsonObject['id']);
        return $jsonObject;
    }
}