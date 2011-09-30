<?php
namespace Fluent\Logger;

// Todo: ちゃんとつくる
class FluentLogger extends BaseLogger
{
	protected $prefix;
	protected $host;
	protected $port;
	
	public function __construct($prefix,$host,$port = \Fluent::DEFAULT_LISTEN_PORT)
	{
		$this->prefix = $prefix;
		$this->host = $host;
		$this->port = $port;
	}
	
	public static function open($prefix, $host, $port = \Fluent::DEFAULT_LISTEN_PORT)
	{
		$logger = new self($prefix,$host,$port);
		return $logger;
	}
	
	public function post($data)
	{
		$entry = array(time(), $data);
		$array = array($entry);
		$packed  = msgpack_pack(array($this->prefix,$array));
		
		$socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
		socket_connect($socket,$this->host,$this->port);
		socket_write($socket,$packed);
		socket_close($socket);
	}
}
