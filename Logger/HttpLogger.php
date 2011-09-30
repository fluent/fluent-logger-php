<?php
namespace Fluent\Logger;

//Todo: ちゃんとつくる
class HttpLogger extends BaseLogger
{
	protected $prefix;
	protected $host;
	protected $port;
	
	public function __construct($prefix,$host,$port = "24224")
	{
		$this->prefix = $prefix;
		$this->host = $host;
		$this->port = $port;
	}
	
	public static function open($prefix, $host, $port = "24224")
	{
		$logger = new self($prefix,$host,$port);
		return $logger;
	}
	
	public function post($data)
	{
		$packed  = json_encode($data);
		file_get_contents("http://{$this->host}:{$this->port}/{$this->prefix}?json={$packed}");
	}
}
