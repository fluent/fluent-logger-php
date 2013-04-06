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
 * Chain Logger
 *
 * experimental chain logger
 *
 */
class ChainLogger extends BaseLogger
{
    protected $chain = array();
    protected $errors = array();

    public function __construct()
    {
    }

    public function addLogger(\Fluent\Logger\BaseLogger $logger)
    {
        $logger->registerErrorHandler(array($this, "defaultErrorHandler"));

        $this->chain[] = $logger;
    }

    public function defaultErrorHandler(BaseLogger $logger, Entity $entity, $error)
    {
        error_log(sprintf("ChainLogger: %s %s %s: %s", get_class($logger), $error, $entity->getTag(), json_encode($entity->getData())));
    }

    /**
     * @param string $tag
     * @param array  $data
     *
     * @api
     */
    public function post($tag, array $data)
    {
        $result = false;
        $entity = new Entity($tag, $data);

        if (!count($this->getAvailableLoggers())) {
            throw new \Exception("ChainLogger have to call addLogger before post method. or all logger failed...");
        }

        foreach ($this->getAvailableLoggers() as $offset => $logger) {
            /* @var $logger \Fluent\Logger */
            $result = $logger->post2($entity);

            if ($result) {
                break;
            } else {
                /* next time, chain logger does not use this logger */
                $this->errors[$offset] = true;
            }
        }

        return $result;
    }

    /**
     * get available loggers.
     *
     * @return array
     */
    public function getAvailableLoggers()
    {
        $result = array();
        foreach ($this->chain as $offset => $logger) {
            /* @var $logger \Fluent\Logger\LoggerInterface */

            if (!isset($this->errors[$offset])) {
                $result[] = $logger;
            } else {
                continue;
            }
        }

        return $result;
    }

    public function post2(Entity $entity)
    {
        throw new \Exception("ChainLogger does not support post2 method");
    }


}