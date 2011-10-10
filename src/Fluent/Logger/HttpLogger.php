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
 * Fluent HTTP logger class.
 *
 * @author Shuhei Tanuma <stanuma@zynga.com>
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
     * @param string $tag primary tag
     * @param string $host 
     * @param int $port
     * @return HttpLogger
     */
    public function __construct($tag, $host, $port = HttpLogger::DEFAULT_HTTP_PORT)
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
     * @return HttpLogger created http logger object.
     */
    public static function open($tag, $host, $port = HttpLogger::DEFAULT_HTTP_PORT)
    {
        $logger = new self($tag,$host,$port);
        return $logger;
    }

    /**
     * send a message to specified fluentd.
     *
     * @param mixied $data
     */
    public function post($data, $additional = null)
    {
        $packed  = json_encode($data);
        file_get_contents("http://{$this->host}:{$this->port}/{$this->tag}?json={$packed}");
    }
}
