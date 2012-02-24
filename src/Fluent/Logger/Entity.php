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
    public $time;
    public $tag;
    public $data = array();

    /**
     * create a entity for sending to fluentd server
     *
     * @param $tag
     * @param $data
     * @param int $time unixtime
     */
    public function __construct($tag, $data, $time = null)
    {
        if (is_null($time) || !is_long($time)) {
            $this->time = time();
        } else {
            $this->time = $time;
        }

        $this->tag = $tag;
        $this->data = $data;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getTime()
    {
        return $this->time;
    }
}