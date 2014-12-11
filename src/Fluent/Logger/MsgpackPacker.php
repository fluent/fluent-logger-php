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

class MsgpackPacker implements PackerInterface
{
    public function __construct()
    {
    }

    /**
     * pack entity with msgpack protocol.
     * {@link https://github.com/msgpack/msgpack-php}
     * @param Entity $entity
     * @return string
     */
    public function pack(Entity $entity)
    {
        if (function_exists('msgpack_pack')) {
            return msgpack_pack(array($entity->getTag(), $entity->getTime(), $entity->getData()));
        } else {
            throw new \Exception("msgpack_pack not found. Have you loaded the module? see https://github.com/msgpack/msgpack-php", 1);
        }
    }
}