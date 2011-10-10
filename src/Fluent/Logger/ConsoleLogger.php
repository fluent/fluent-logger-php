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
