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
//namespace Fluent\Logger;

/**
 * Console Logger
 *
 * Console Logger client outputs readable log message to stderr.
 *
 */
class Fluent_Logger_ConsoleLogger extends Fluent_Logger_BaseLogger
{
    /* @var resource handle */
    protected $handle;
    
    /**
     * create Console logger object.
     *
     * @return Fluent_Logger_ConsoleLogger
     */
    public function __construct()
    {
        $this->handle = fopen("php://stderr","w");
    }

    /**
     * fluent-logger compatible API.
     *
     * @return Fluent_Logger_BaseLogger created logger object.
     */
    public static function open()
    {
        $logger = new self();
        return $logger;
    }

    /**
     * send a message to specified fluentd.
     *
     * @param string $tag
     * @param array $data
     */
    public function post($tag ,array $data)
    {
        $entity = new Fluent_Logger_Entity($tag,$data);
        return $this->postImpl($entity);
    }

    /**
     * @param Fluent_Logger_Entity $entity
     * @return bool
     */
    public function post2(Fluent_Logger_Entity $entity)
    {
        return $this->postImpl($entity);
    }

    /**
     * @param Entity $entity
     * @return int
     */
    protected function postImpl(Fluent_Logger_Entity $entity)
    {
        /*
         * example ouputs:
         *   2012-02-26T01:26:20+0900        debug.test      {"hello":"world"}
         */
        $format = "%s\t%s\t%s\n";
        return $this->write(sprintf($format,
            date(DateTime::ISO8601,$entity->getTime()),
            $entity->getTag(),
            json_encode($entity->getData())
        ));
    }

    /**
     * fwrite proxy method
     *
     * @param string $buffer
     * @return int
     */
    protected function write($buffer)
    {
        return fwrite($this->handle, $buffer);
    }
}
