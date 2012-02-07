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
        $fp = fopen($path, "c");

        if (is_resource($fp)) {
            $this->fp = $fp;
        } else {
            throw new \Exception("could not open file {$path}");
        }
    }
    
    /**
     * Fluent singleton API.
     *
     * @todo fixed singleton api.
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
     * @param array $data
     */
    public function post($tag, $data)
    {
        $packed = json_encode($data);
        $wbuffer = $data = sprintf("%s\t%s\t%s\n",date(\DateTime::ISO8601), $tag, $packed);
        $length = strlen($data);
        $written = 0;

        try {
            if (!flock($this->fp, LOCK_EX)) {
                throw new \Exception('could not obtain LOCK_EX');
            }
            fseek($this->fp,-1, SEEK_END);

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
                $wbuffer = substr($wbuffer,$written);
            }

            flock($this->fp, LOCK_UN);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }

    }
}
