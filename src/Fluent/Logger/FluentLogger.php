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

class FluentLogger extends BaseLogger
{
    const TIMEOUT = 3;
    const DEFAULT_LISTEN_PORT = 24224;
    
    protected $tag;
    protected $host;
    protected $port;
    protected $socket;

    //protected $msgpacker = 'msgpack_pack';
    public $msgpacker = 'msgpack_pack';
    
    public function __construct($tag, $host, $port = FluentLogger::DEFAULT_LISTEN_PORT)
    {
        $this->tag = $tag;
        $this->host = $host;
        $this->port = $port;
    }
    
    public static function open($tag, $host, $port = FluentLogger::DEFAULT_LISTEN_PORT)
    {
        $logger = new self($tag,$host,$port);
        return $logger;
    }
    
    protected function connect()
    {
        $socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,array('sec'=>self::TIMEOUT,'usec'=>0));
        $retval = socket_connect($socket,$this->host,$this->port);
        if (!$retval) {
            throw new \Exception("could not connect to {$this->host}");
        }
        $this->socket = $socket;
    }
    
    protected function reconnect()
    {
        if(!is_resource($this->socket)) {
            $this->connect();
        }
    }
    
    public function post($data, $additional = null)
    {
        $retval = false;
        
        $entry = array(time(), $data);
        $array = array($entry);
        
        $tag = $this->tag;
        if (!empty($additional)) {
            $tag .= "." . $additional;
        }
        $packed  = $this->msgpackPack(array($tag,$array));
        $this->reconnect();
        
        $length = strlen($packed);
        while ($length > 0) {
            $sent = socket_write($this->socket, $packed, $length);
            if ($sent < 0) {
                throw new \Exception("connection aborted");
            }
            
            if ($sent < $length) {
                $packed = substr($packed, $sent);
            }
            $length -= $sent;
        }
    }
    
    public function __destruct()
    {
        if(is_resource($this->socket)) {
            socket_close($this->socket);
        }
    }

    protected function msgpackPack($message)
    {
        return call_user_func($this->msgpacker, $message);
    }
}
