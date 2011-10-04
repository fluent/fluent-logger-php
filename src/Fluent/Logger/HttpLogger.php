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

//Todo: ちゃんとつくる
class HttpLogger extends BaseLogger
{
    const DEFAULT_HTTP_PORT = 8888;

    protected $prefix;
    protected $host;
    protected $port;
    
    public function __construct($prefix, $host, $port = HttpLogger::DEFAULT_HTTP_PORT)
    {
        $this->prefix = $prefix;
        $this->host = $host;
        $this->port = $port;
    }
    
    public static function open($prefix, $host, $port = HttpLogger::DEFAULT_HTTP_PORT)
    {
        $logger = new self($prefix,$host,$port);
        return $logger;
    }
    
    public function post($data, $additional = null)
    {
        $packed  = json_encode($data);
        file_get_contents("http://{$this->host}:{$this->port}/{$this->prefix}?json={$packed}");
    }
}
