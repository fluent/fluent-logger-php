<?php
/*
 * The MIT License
 *
 * Copyright (c) 2011 Shuhei Tanuma
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
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
