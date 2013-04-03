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

/**
 * Fluent basic logger class.
 *
 * all fluent logger must extend this class.
 */
abstract class BaseLogger implements \Fluent\Logger\LoggerInterface
{
    protected $error_handler = null;

    /**
     * @param      $entity
     * @param void $error error message
     */
    public function defaultErrorHandler(BaseLogger $logger, Entity $entity, $error)
    {
        error_log(sprintf("%s %s %s: %s", get_class($logger), $error, $entity->getTag(), json_encode($entity->getData())));
    }

    /**
     * @param Entity $entity
     * @param void   $error error message
     */
    protected function processError(Entity $entity, $error)
    {
        if (!is_null($this->error_handler)) {
            call_user_func_array($this->error_handler, array($this, $entity, $error));
        } else {
            $this->defaultErrorHandler($this, $entity, $error);
        }
    }

    /**
     * @param  callable  $callable function name, array or closure
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function registerErrorHandler($callable)
    {
        if (is_callable($callable)) {
            $this->error_handler = $callable;
        } else {
            throw new \InvalidArgumentException("Error handler must be callable.");
        }

        return true;
    }
}
