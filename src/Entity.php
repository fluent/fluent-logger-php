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

class Entity
{
    /* @var int unxitime */
    public $time;

    /* @var string Fluentd tag */
    public $tag;

    /* @var array structured log data */
    public $data = array();

    /**
     * create a entity for sending to fluentd server
     *
     * @param     $tag
     * @param     $data
     * @param int $time unixtime
     */
    public function __construct($tag, $data, $time = null)
    {
        if (is_long($time)) {
            $this->time = $time;
        } else {
            if (!is_null($time)) {
                error_log("Entity::__construct(): unexpected time format `{$time}` specified.");
            }

            $this->time = time();
        }

        $this->tag  = $tag;
        $this->data = $data;
    }

    /**
     * get current tag
     *
     * @return string tag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * get current data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * get current unixtime
     *
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }
}