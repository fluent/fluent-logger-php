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
    const SOCKET_TIMEOUT     = 3;
    const MAX_WRITE_RETRY    = 5;

    /* 1000 means 0.001 sec */
    const USLEEP_WAIT = 1000;

    /* Fluent uses port 24224 as a default port */
    const DEFAULT_LISTEN_PORT = 24224;
    const DEFAULT_ADDRESS     = "127.0.0.1";

    /**
     * backoff strategies: default usleep
     *
     * attempts | wait
     * 1        | 0.003 sec
     * 2        | 0.009 sec
     * 3        | 0.027 sec
     * 4        | 0.081 sec
     * 5        | 0.243 sec
     **/
    const BACKOFF_TYPE_EXPONENTIAL = 0x01;
    const BACKOFF_TYPE_USLEEP      = 0x02;

    /* @var string host name */
    protected $host;

    /* @var int port number. when you wanna use unix domain socket. set port to 0 */
    protected $port;

    /* @var string Various style transport: `tcp://localhost:port` */
    protected $transport;

    /* @var resource */
    protected $socket;

    /* @var PackerInterface */
    protected $packer;

    protected $options = array(
        "socket_timeout"     => self::SOCKET_TIMEOUT,
        "connection_timeout" => self::CONNECTION_TIMEOUT,
        "backoff_mode"       => self::BACKOFF_TYPE_USLEEP,
        "backoff_base"       => 3,
        "usleep_wait"        => self::USLEEP_WAIT,
        "persistent"         => false,
        "retry_socket"       => true,
    );

    protected static $supported_transports = array(
        "tcp",
        "unix",
    );

    protected static $acceptable_options = array(
        "socket_timeout",
        "connection_timeout",
        "backoff_mode",
        "backoff_base",
        "usleep_wait",
        "persistent",
        "retry_socket",
    );

    protected static $instances = array();

    /**
     * create fluent logger object.
     *
     *
     * @param string          $host
     * @param int             $port
     * @param array           $options
     * @param PackerInterface $packer
     * @return FluentLogger
     */
    public function __construct($host = FluentLogger::DEFAULT_ADDRESS,
                                $port = FluentLogger::DEFAULT_LISTEN_PORT,
                                array $options = array(),
                                PackerInterface $packer = null)
    {
        /* keep original host and port */
        $this->host = $host;
        $this->port = $port;

        /* make various URL style socket transports */
        $this->transport = self::getTransportUri($host, $port);

        if (is_null($packer)) {
            /* for backward compatibility */
            $packer = new JsonPacker();
        }

        $this->packer = $packer;

        $this->mergeOptions($options);
    }

    /**
     * make a various style transport uri with specified host and port.
     * currently, in_forward uses tcp transport only.
     *
     * @param $host
     * @param $port
     * @return string
     * @throws \Exception
     */
    public static function getTransportUri($host, $port)
    {
        if (($pos = strpos($host, "://")) !== false) {
            $transport = substr($host, 0, $pos);
            $host      = substr($host, $pos + 3);

            if (!in_array($transport, self::$supported_transports)) {
                throw new \Exception("transport `{$transport}` does not support");
            }

            // Now, unix socket is recommended on PHP client.
            if ($transport == "unix") {
                /* unix domain socket have to ignore port number */
                $result = "unix://" . $host;
            } else {
                if (strpos($host, "::") !== false) {
                    /* ipv6 address should be surrounded brackets */
                    $host = sprintf("[%s]", trim($host, "[]"));
                }

                $result = sprintf("%s://%s:%d", $transport, $host, $port);
            }

        } else {
            if (strpos($host, "::") !== false) {
                /* ipv6 address should be surrounded brackets */
                $host = sprintf("[%s]", trim($host, "[]"));
            }

            $result = sprintf("tcp://%s:%d", $host, $port);
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
        $this->mergeOptions($options);
    }

    /**
     * fluent-logger compatible API.
     *
     * @param string $host
     * @param int    $port
     * @param array  $options
     * @return FluentLogger created logger object.
     */
    public static function open($host = FluentLogger::DEFAULT_ADDRESS, $port = FluentLogger::DEFAULT_LISTEN_PORT, array $options = array())
    {
        $key = sprintf("%s:%s:%s", $host, $port, md5(join(",", $options)));

        if (!isset(self::$instances[$key])) {
            $logger                = new self($host, $port, $options);
            self::$instances[$key] = $logger;
        }

        return self::$instances[$key];
    }

    /**
     * clear fluent-logger instances from static variable.
     *
     * this useful when testing.
     *
     * @return void
     */
    public static function clearInstances()
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
        $connect_options = \STREAM_CLIENT_CONNECT;
        if ($this->getOption("persistent", false)) {
            $connect_options |= \STREAM_CLIENT_PERSISTENT;
        }

        // could not suppress warning without ini setting.
        // for now, we use error control operators. 
        $socket = @stream_socket_client($this->transport, $errno, $errstr,
            $this->getOption("connection_timeout", self::CONNECTION_TIMEOUT),
            $connect_options
        );

        if (!$socket) {
            $errors = error_get_last();
            throw new \Exception($errors['message']);
        }

        // set read / write timeout.
        stream_set_timeout($socket, $this->getOption("socket_timeout", self::SOCKET_TIMEOUT));
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
     * @param array  $data
     * @return bool
     *
     * @api
     */
    public function post($tag, array $data)
    {
        $entity = new Entity($tag, $data);

        return $this->postImpl($entity);
    }

    /**
     * send a message to specified fluentd.
     *
     * @param Entity $entity
     * @return bool
     */
    public function post2(Entity $entity)
    {
        return $this->postImpl($entity);
    }

    /**
     * post implementation
     *
     * @param \Fluent\Logger\Entity $entity
     * @return bool
     * @throws \Exception
     */
    protected function postImpl(Entity $entity)
    {
        $buffer = $packed = $this->packer->pack($entity);
        $length = strlen($packed);
        $retry  = $written = 0;

        try {
            $this->reconnect();
        } catch (\Exception $e) {
            $this->close();
            $this->processError($entity, $e->getMessage());

            return false;
        }

        try {
            // PHP socket looks weired. we have to check the implementation.
            while ($written < $length) {
                $nwrite = $this->write($buffer);

                if ($nwrite === false) {
                    // could not write messages to the socket.
                    // e.g) Resource temporarily unavailable
                    throw new \Exception("could not write message");
                } else if ($nwrite === "") {
                    // sometimes fwrite returns null string.
                    // probably connection aborted.
                    throw new \Exception("connection aborted");
                } else if ($nwrite === 0) {
                    if (!$this->getOption("retry_socket", true)) {
                        $this->processError($entity, "could not send entities");

                        return false;
                    }

                    if ($retry > self::MAX_WRITE_RETRY) {
                        throw new \Exception("failed fwrite retry: retry count exceeds limit.");
                    }

                    $errors = error_get_last();
                    if ($errors) {
                        if (isset($errors['message']) && strpos($errors['message'], 'errno=32 ') !== false) {
                            /* breaking pipes: we have to close socket manually */
                            $this->close();
                            $this->reconnect();
                        } else if (isset($errors['message']) && strpos($errors['message'], 'errno=11 ') !== false) {
                            // we can ignore EAGAIN message. just retry.
                        } else {
                            error_log("unhandled error detected. please report this issue to http://github.com/fluent/fluent-logger-php/issues: " . var_export($errors, true));
                        }
                    }

                    if ($this->getOption('backoff_mode', self::BACKOFF_TYPE_EXPONENTIAL) == self::BACKOFF_TYPE_EXPONENTIAL) {
                        $this->backoffExponential(3, $retry);
                    } else {
                        usleep($this->getOption("usleep_wait", self::USLEEP_WAIT));
                    }
                    $retry++;
                    continue;
                }

                $written += $nwrite;
                $buffer = substr($packed, $written);
            }
        } catch (\Exception $e) {
            $this->close();
            $this->processError($entity, $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * backoff exponential sleep
     *
     * @param $base int
     * @param $attempt int
     */
    public function backoffExponential($base, $attempt)
    {
        usleep(pow($base, $attempt) * 1000);
    }


    /**
     * write data
     *
     * @param string $data
     * @return mixed integer|false
     */
    protected function write($buffer)
    {
        // We handle fwrite error on postImpl block. ignore error message here.
        return @fwrite($this->socket, $buffer);
    }

    /**
     * close the socket
     *
     * @return void
     */
    public function close()
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }

    /**
     * destruct objects and socket.
     *
     * @return void
     */
    public function __destruct()
    {
        if (!$this->getOption("persistent", false) && is_resource($this->socket)) {
            fclose($this->socket);
        }
    }

    /**
     * get specified option's value
     *
     * @param      $key
     * @param null $default
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        $result = $default;
        if (isset($this->options[$key])) {
            $result = $this->options[$key];
        }

        return $result;
    }
}
