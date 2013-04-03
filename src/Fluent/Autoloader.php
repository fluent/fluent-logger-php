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
namespace Fluent;

class Autoloader
{
    const NAME_SPACE = 'Fluent';
    protected static $base_dir;

    /**
     * register Fluent basic autoloader
     *
     * @param string $dirname base directory path.
     * @return void
     */
    public static function register($dirname = __DIR__)
    {
        self::$base_dir = $dirname;
        spl_autoload_register(array(__CLASS__, "autoload"));
    }

    /**
     * unregister Fluent basic autoloader
     *
     * @return void
     */
    public static function unregister()
    {
        spl_autoload_unregister(array(__CLASS__, "autoload"));
    }

    /**
     * autoload basic implementation
     *
     * @param string $name class name
     * @return boolean return true when load successful
     */
    public static function autoload($name)
    {
        $retval = false;
        if (strpos($name, self::NAME_SPACE) === 0) {
            $parts = explode("\\", $name);
            array_shift($parts);

            $expected_path = join(DIRECTORY_SEPARATOR, array(self::$base_dir, join(DIRECTORY_SEPARATOR, $parts) . ".php"));
            if (is_file($expected_path) && is_readable($expected_path)) {
                require $expected_path;
                $retval = true;
            }
        }

        return $retval;
    }
}