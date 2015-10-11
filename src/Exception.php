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

class Exception extends \Exception
{
    protected $entity;

    /**
     * @param                 $tag
     * @param                 $data
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(Entity $entity, $message = "", $code = 0, \Exception $previous = null)
    {
        $this->entity = $entity;
        parent::__construct($message, $code, $previous);
    }

    /**
     * get entity
     *
     * @return string $tag
     */
    public function getEntity()
    {
        return $this->entity;
    }
}