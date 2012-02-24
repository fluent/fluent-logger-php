<?php
/**
 *  Fluent-Logger-PHP
 * 
 *  Copyright (C) 2011 - 2012 Fluent-Logger-PHP Contributors
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
 * Fluent Logger client communicates to Fluentd with json formatted messages.
 */
class FluentLogger extends BaseLogger
{
    const CONNECTION_TIMEOUT = 3;
    const SOCKET_TIMEOUT = 3;
    const MAX_WRITE_RETRY = 10;

    /* Fluent uses port 24224 as a default port */
    const DEFAULT_LISTEN_PORT = 24224;
    const DEFAULT_ADDRESS = "127.0.0.1";

    /* @var string host name */
    protected $host;

    /* @var int port number. when you wanna use unix domain socket. set port to 0 */
    protected $port;

    /* @var string Various style transport: `tcp://localhost:port` */
    protected $transport;

    protected $socket;

    /* @var PackerInterface */
    protected $packer;

    protected $options = array(
        "socket_timeout"     => self::SOCKET_TIMEOUT,
        "connection_timeout" => self::CONNECTION_TIMEOUT,
    );

    protected static $supported_transports = array(
        "tcp","unix",
    );

    protected static $acceptable_options = array(
        "socket_timeout", "connection_timeout",
    );

    protected static $instances = array();
    
    /**
     * create fluent logger object.
     *
     *
     * @param string $host
     * @param int $port
     * @param array $options
     * @return FluentLogger
     */
    public function __construct($host = FluentLogger::DEFAULT_ADDRESS, $port = FluentLogger::DEFAULT_LISTEN_PORT, array $options = array())
    {
        /* keep original host and port */
        $this->host = $host;
        $this->port = $port;

        /* make various URL style socket transports */
        $this->transport = $this->getTransportUri($host, $port);

        $this->packer = new JsonPacker();

        $this->mergeOptions($options);
    }

    /**
     * make a various style transport uri with specified host and port.
     * currently, in_foward uses tcp transport only.
     *
     * @param $host
     * @param $port
     * @return string
     * @throws \Exception
     */
    public function getTransportUri($host, $port)
    {
        if (($pos = strpos($host,"://")) !== false) {
            $transport = substr($host,0,$pos);
            $host = substr($host, $pos + 3);

            if (!in_array($transport, self::$supported_transports)) {
                throw new \Exception("transport `{$transport}` does not support");
            }

            /* unix socket is deprecated. */
            if ($transport == "unix") {
                $result = "unix://" . $host;
            } else {
                $result = sprintf("%s://%s:%d",$transport, $host, $port);
            }

        } else {
            $result = sprintf("tcp://%s:%d",$host, $port);
        }

        return $result;
    }

    /**
     * set packer
     *
     * @param PackerInterface $packer
     * @return PackerInterface
     */
    public function setPacker(PackerInterface $packer)
    {
        return $this->packer = $packer;
    }

    /**
     * get current packer
     *
     * @return JsonPacker|PackerInterface
     */
    public function getPacker()
    {
        return $this->packer;
    }

    /**
     * merge options
     *
     * @param array $options
     * @throws \Exception
     */
    public function mergeOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (!in_array($key, self::$acceptable_options)) {
                throw new \Exception("option {$key} does not support");
            }

            $this->options[$key] = $value;
        }
    }

    /**
     * set options
     *
     * @param array $options
     * @throws \Exception
     */
    public function setOptions(array $options)
    {
        $this->options = array();

        foreach ($options as $key => $value) {
            if (!in_array($key, self::$acceptable_options)) {
                throw new \Exception("option {$key} does not support");
            }

            $this->options[$key] = $value;
        }
    }
    
    /**
     * fluent-logger compatible API.
     *
     * @param string $host
     * @param int $port
     * @param array $options
     * @return FluentLogger created logger object.
     */
    public static function open($host = FluentLogger::DEFAULT_ADDRESS, $port = FluentLogger::DEFAULT_LISTEN_PORT,  array $options = array())
    {
        $key = sprintf("%s:%s:%s",$host, $port, md5(join(",",$options)));

        if (!isset(self::$instances[$key])) {
            $logger = new self($host,$port, $options);
            self::$instances[$key] = $logger;
        }

        return self::$instances[$key];
    }

    /**
     * crear fluent-loogger instances from static variable.
     *
     * @return void
     */
    public static function clearIntances()
    {
        foreach (self::$instances as $object) {
            unset($object);
        }
        self::$instances = array();
    }

    /**
     * create a connection to specified fluentd
     *
     * @throws \Exception
     */
    protected function connect()
    {
        // could not suppress warning without ini setting.
        // for now, we use error control operators. 
        $socket = @stream_socket_client($this->transport,$errno,$errstr,
                    $this->options["connection_timeout"], \STREAM_CLIENT_CONNECT | \STREAM_CLIENT_PERSISTENT);

        if (!$socket) {
            $errors = error_get_last();
            throw new \Exception($errors['message']);
        }

        // set read / write timeout.
        stream_set_timeout($socket,$this->options["socket_timeout"]);
        $this->socket = $socket;
    }
    
    /**
     * create a connection if Fluent Logger hasn't a socket connection.
     *
     * @return void
     */
    protected function reconnect()
    {
        if (!is_resource($this->socket)) {
            $this->connect();
        }
    }
    
    /**
     * send a message to specified fluentd.
     *
     * @param string $tag
     * @param array $data
     * @return bool
     *
     * @api
     */
    public function post($tag, array $data)
    {
        $entity = new Entity($tag, $data);

        $buffer = $packed = $this->packer->pack($entity);
        $length = strlen($packed);
        $retry  = $written = 0;

        try {
            $this->reconnect();
        } catch (\Exception $e) {
            $this->processError($entity, $e->getMessage());
            return false;
        }

        try {
            // PHP socket looks weired. we have to check the implementation.
            while ($written < $length) {
                $nwrite = $this->write($buffer);

                if ($nwrite === false) {
                    // could not write messages to the socket.
                    // Todo: check fwrite implementation.
                    throw new \Exception("could not write message");
                } else if ($nwrite === "") {
                    // sometimes fwrite returns null string.
                    // probably connection aborted.
                    throw new \Exception("connection aborted");
                } else if ($nwrite === 0) {
                    if ($retry > self::MAX_WRITE_RETRY) {
                        throw new \Exception("failed fwrite retry: max retry count");
                    }
                    $retry++;
                }

                $written += $nwrite;
                $buffer   = substr($packed,$written);
            }
        } catch (\Exception $e) {
            $this->processError($entity, $e->getMessage());
            return false;
        }
        
        return true;
    }
    
    /**
     * pack php object to fluentd message format.
     * fluentd v0.9.20 can read json message format directly.
     * for now, this method send message to fluentd as json object.
     *
     * @param string $tag fluentd tag.
     * @param mixed $data
     * @return string message data.
     * @obsolete
     */
    public static function pack_impl($tag, $data)
    {
        return json_encode(array($tag, time(), $data));
    }

    /**
     * write data
     *
     * @param string $data
     * @return mixed integer|false
     */
    protected function write($buffer)
    {
        return fwrite($this->socket, $buffer);
    }

    public function __destruct()
    {
        /* do not close socket as we use persistent connection */
    }
}
