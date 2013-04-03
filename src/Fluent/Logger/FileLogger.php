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
 * Fluent File logger class.
 */
class FileLogger extends BaseLogger
{
    const MAX_WRITE_RETRY = 10;

    protected $tag;
    protected $path;

    /**
     * create fluent file logger object.
     *
     * @param string $path log file path
     * @return FileLogger
     */
    public function __construct($path)
    {
        $this->path = $path;

        // don't raise error here.
        $old_error_handler = set_error_handler(array($this, "ignoreError"));
        $fp = @fopen($path, "c");
        set_error_handler($old_error_handler);

        if (is_resource($fp)) {
            $this->fp = $fp;
        } else {
            throw new \RuntimeException("could not open file {$path}");
        }
    }

    /**
     * fluent-logger compatible API.
     *
     * @param string $path
     */
    public static function open($path)
    {
        $logger = new self($path);

        return $logger;
    }

    /**
     * write a message to specified path.
     *
     * @param string $tag
     * @param array  $data
     */
    public function post($tag, array $data)
    {
        $entity = new Entity($tag, $data);

        return $this->postImpl($entity);
    }

    /**
     * write a message to specified path.
     *
     * @param Entity $entity
     */
    public function post2(Entity $entity)
    {
        return $this->postImpl($entity);
    }

    protected function postImpl(Entity $entity)
    {
        $packed = json_encode($entity->getData());
        $data   = $wbuffer = sprintf("%s\t%s\t%s\n",
            date(\DateTime::ISO8601, $entity->getTime()),
            $entity->getTag(),
            $packed . PHP_EOL
        );

        $length  = strlen($data);
        $written = 0;
        $retry   = 0;

        try {
            if (!flock($this->fp, LOCK_EX)) {
                throw new \Exception('could not obtain LOCK_EX');
            }
            fseek($this->fp, -1, SEEK_END);

            while ($written < $length) {
                $nwrite = fwrite($this->fp, $wbuffer);
                if ($nwrite === false) {
                    throw new \Exception("could not write message");
                } else if ($nwrite === "") {
                    throw new \Exception("connection aborted");
                } else if ($nwrite === 0) {
                    if ($retry > self::MAX_WRITE_RETRY) {
                        throw new \Exception("failed fwrite retry: max retry count");
                    }
                    $retry++;
                }
                $written += $nwrite;
                $wbuffer = substr($wbuffer, $written);
            }

            flock($this->fp, LOCK_UN);
        } catch (\Exception $e) {
            $this->processError($this, $entity, $e->getMessage());

            return false;
        }

        return true;
    }

    public function ignoreError($errno, $errstr, $errfile, $errline)
    {
        return;
    }
}
