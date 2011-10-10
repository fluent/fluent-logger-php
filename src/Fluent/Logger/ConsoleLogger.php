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
 * Console Logger
 *
 * Console Logger client outputs readable log message to specifeid file handle.
 *
 * @author Shuhei Tanuma <stanuma@zynga.com>
 */
class ConsoleLogger extends BaseLogger
{
    protected $tag;
    protected $handle;
    
    /**
     * create Console logger object.
     *
     * @param string $tag primary tag
     * @param resource $handle
     * @return ConsoleLogger
     */
    public function __construct($tag,$handle)
    {
        $this->tag = $tag;
        $this->handle = $handle;
    }
    
    /**
     * Fluent singleton API.
     *
     * @todo fixed singleton api.
     * @param string $tag primary tag
     * @param resource $handle 
     * @return FluentLogger created logger object.
     */
    public static function open($tag, $handle)
    {
        $logger = new self($tag,$handle);
        //\Fluent\Logger::$current = $logger;
        return $logger;
    }
    
    /**
     * send a message to specified fluentd.
     *
     * @param mixied $data
     */
    public function post($data, $additional = null)
    {
        $params = array();
        $tag = $this->tag;
        if (!empty($additional)) {
            $tag .= ".{$additional}";
        }
        
        foreach ($data as $key => $value) {
            $params[$key] = $value;
        }
        
        $time = new \DateTime("@".time(),new \DateTimeZone(date_default_timezone_get()));
        $result = sprintf("%s %s: %s\n",$time->format("Y-m-d H:i:s O"), $tag, json_encode($params));
        fwrite($this->handle,$result);
    }
}
