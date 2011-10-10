<?php
/**
 *  Fluent-PHP-Logger
 * 
 *  Copyright (C) 2011 Shuhei Tanuma
 * 
 *     Licensed under the Apache License, Version 2.0 (the "License");
 *     you may not use this file except in compliance with the License.
 *     You may obtain a copy of the License at
 * 
 *         http://www.apache.org/licenses/LICENSE-2.0
 * 
 *     Unless required by applicable law or agreed to in writing, software
 *     distributed under the License is distributed on an "AS IS" BASIS,
 *     WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *     See the License for the specific language governing permissions and
 *     limitations under the License.
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
        // could not suprress warning without ini setting.
        // for now, we use error control operators. 
        $socket = @pfsockopen($this->host,$this->port,$errno,$errstr,self::TIMEOUT);
        if (!$socket) {
            $errors = \error_get_last();
            throw new \Exception($errors['message']);
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
        $packed  = json_encode(array($tag,$array));
        $this->reconnect();
        
        //$length = strlen($packed);
        return fwrite($this->socket, $packed);
    }
    
    /**
     * remove socket resource.
     *
     * @return void
     */
    public function __destruct()
    {
    }

    /**
     * convert php object to message packed format string.
     *
     * actually, we don't use this method right now.
     * please wait our work.
     *
     * @todo adjsut json_encode / message pack formatter.
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
