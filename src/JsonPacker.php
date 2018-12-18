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

class JsonPacker implements PackerInterface
{
    public function __construct()
    {
    }

    /**
     * pack entity as a json string.
     *
     * @param Entity $entity
     *
     * @return string
     * @throws \UnexpectedValueException
     */
    public function pack(Entity $entity)
    {
        $json = json_encode(array($entity->getTag(), $entity->getTime(), $entity->getData()));

        if (!$json and json_last_error() !== JSON_ERROR_NONE) {
            throw new \UnexpectedValueException('Failed to encode data to json: ' . json_last_error_msg());
        }

        return $json;
    }
}