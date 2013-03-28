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

class Fluent_Logger_JsonPacker implements Fluent_Logger_PackerInterface
{
    public function __construct()
    {
    }

    /**
     * pack entity as a json string.
     *
     * @param Fluent_Logger_Entity $entity
     * @return string
     */
    public function pack(Fluent_Logger_Entity $entity)
    {
        return json_encode(array($entity->getTag(), $entity->getTime(), $entity->getData()));
    }
}