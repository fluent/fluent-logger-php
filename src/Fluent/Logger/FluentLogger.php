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

/**
 * Fluent Logger
 *
 * Fluenet Logger client communicates to Fluentd with message pack formated message.
 *
 * @author Shuhei Tanuma <stanuma@zynga.com>
 */
class FluentLogger extends BaseLogger
{
    const TIMEOUT = 3;
    
    /* Fluent uses port 24224 as a default port */
    const DEFAULT_LISTEN_PORT = 24224;
    
    const DEFAULT_ADDRESS = "127.0.0.1";
    
    protected $tag;
    protected $host;
    protected $port;
    protected $socket;
    
    /**
     * create fluent logger object.
     *
     * @param string $tag primary tag
     * @param string $host 
     * @param int $port
     * @return FluentLogger
     */
    public function __construct($tag, $host = FluentLogger::DEFAULT_ADDRESS, $port = FluentLogger::DEFAULT_LISTEN_PORT)
    {
        $this->tag = $tag;
        $this->host = $host;
        $this->port = $port;
    }
    
    /**
     * Fluent singleton API.
     *
     * @todo fixed singleton api.
     * @param string $tag primary tag
     * @param string $host 
     * @param int $port
     * @return FluentLogger created logger object.
     */
    public static function open($tag, $host = FluentLogger::DEFAULT_ADDRESS, $port = FluentLogger::DEFAULT_LISTEN_PORT)
    {
        $logger = new self($tag,$host,$port);
        //\Fluent::$logger = $logger;
        return $logger;
    }

    /**
     * create a connection to specified fluentd
     *
     * @return void 
     */
    protected function connect()
    {
        $socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        if (!is_resource($socket)) {
            throw new \Exception("could not create a socket: " . socket_strerror(socket_last_error()));
        }
        
        if (!socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,array('sec'=>self::TIMEOUT,'usec'=>0))) {
            throw new \Exception("could not set scoket option: ". socket_strerror(socket_last_error()));
        }

        $retval = socket_connect($socket,$this->host,$this->port);
        if (!$retval) {
            throw new \Exception(socket_strerror(socket_last_error()) . " - could not connect to {$this->host}");
        }
        $this->socket = $socket;
    }
    
    /**
     * create a connection if Fluent Logger hasn't a socket connection.
     *
     * @return void
     */
    protected function reconnect()
    {
        if(!is_resource($this->socket)) {
            $this->connect();
        }
    }
    
    /**
     * send a message to specified fluentd.
     *
     * @param mixied $data
     */
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
    
    /**
     * remove socket resource.
     *
     * @return void
     */
    public function __destruct()
    {
        if(is_resource($this->socket)) {
            socket_close($this->socket);
        }
    }

    /**
     * convert php object to message packed format string.
     *
     * @param array $message pseudo fluentd message struct array.
     * @return string message packed binary string
     */
    protected function msgpackPack($message)
    {
        if (function_exists('msgpack_pack')) {
            return msgpack_pack($message);
        }

        if (!class_exists('MsgPack_Coder')) {
            throw new \RuntimeException('MsgPack_Coder class not loaded');
        }

        return \MsgPack_Coder::encode($message);
    }
}
