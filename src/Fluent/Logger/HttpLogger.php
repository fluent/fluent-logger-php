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
 * Fluent HTTP logger class.
 */
class HttpLogger extends BaseLogger
{
    const DEFAULT_HTTP_PORT = 8888;

    protected $tag;
    protected $host;
    protected $port;

    /**
     * create fluent http logger object.
     *
     * @param string $host
     * @param int    $port
     * @return HttpLogger
     */
    public function __construct($host, $port = HttpLogger::DEFAULT_HTTP_PORT)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * fluent-logger compatible API.
     *
     * @param string $host
     * @param int    $port
     * @return HttpLogger created http logger object.
     */
    public static function open($host, $port = HttpLogger::DEFAULT_HTTP_PORT)
    {
        $logger = new self($host, $port);

        return $logger;
    }

    /**
     * send a message to specified fluentd.
     *
     * @todo use HTTP1.1 protocol and persistent socket.
     * @param string $tag
     * @param array  $data
     */
    public function post($tag, array $data)
    {
        $packed  = json_encode($data);
        $request = sprintf('http://%s:%d/%s?json=%s', $this->host, $this->port, $tag, urlencode($packed));

        $ret = file_get_contents($request);

        return ($ret !== false);
    }

    /**
     * send a message to specified fluentd.
     *
     * @todo use HTTP1.1 protocol and persistent socket.
     * @param string $tag
     * @param array  $data
     */
    public function post2(Entity $entity)
    {
        $packed  = json_encode($entity->getData());
        $request = sprintf('http://%s:%d/%s?json=%s', $this->host, $this->port, $entity->getTag(), urlencode($packed));

        $ret = file_get_contents($request);

        return ($ret !== false);
    }

}
