<?php
/*
 * The MIT License
 *
 * Copyright (c) 2011 Shuhei Tanuma
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Fluent;

require __DIR__ . "/Event.php";
require __DIR__ . "/Logger/BaseLogger.php";
require __DIR__ . "/Logger/FluentLogger.php";
require __DIR__ . "/Logger/HttpLogger.php";

define("DEFAULT_CONFIG_PATH", (isset($_ENV['FLUENT_CONF'])) ? $_ENV['FLUENT_CONF'] : '/etc/fluent/fluent.conf');
define("DEFAULT_PLUGIN_DIR",  (isset($_ENV['FLUENT_PLUGIN_DIR'])) ? $_ENV['FLUENT_PLUGIN_DIR'] : '/etc/fluent/plugin');
define("DEFAULT_SOCKET_PATH", (isset($_ENV['FLUENT_SOCKET'])) ? $_ENV['FLUENT_SOCKET'] : '/etc/fluent/plugin');
define("DEFAULT_LISTEN_PORT", 24224);
define("DEFAULT_HTTP_PORT",   8888);

class Logger
{
	public static $current;
	
}