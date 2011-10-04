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

class ConsoleLogger extends BaseLogger
{
    protected $prefix;
    protected $handle;
    
    public function __construct($prefix,$handle)
    {
        $this->prefix = $prefix;
        $this->handle = $handle;
    }
    
    public static function open($prefix, $handle)
    {
        $logger = new self($prefix,$handle);
        //\Fluent\Logger::$current = $logger;
        return $logger;
    }
    
    public function post($data, $additional = null)
    {
        $params = array();
        $prefix = $this->prefix;
        if (!empty($additional)) {
            $prefix .= ".{$additional}";
        }
        
        foreach ($data as $key => $value) {
            $params[$key] = $value;
        }
        
        $time = new \DateTime("@".time(),new \DateTimeZone(date_default_timezone_get()));
        $result = sprintf("%s %s: %s\n",$time->format("Y-m-d H:i:s O"), $prefix, json_encode($params));
        fwrite($this->handle,$result);
    }
}
