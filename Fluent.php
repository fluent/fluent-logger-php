<?php
namespace Fluent;

require __DIR__ . "/Logger/BaseLogger.php";
require __DIR__ . "/Logger/FluentLogger.php";
require __DIR__ . "/Logger/HttpLogger.php";

define("DEFAULT_CONFIG_PATH", (isset($_ENV['FLUENT_CONF'])) ? $_ENV['FLUENT_CONF'] : '/etc/fluent/fluent.conf');
define("DEFAULT_PLUGIN_DIR",  (isset($_ENV['FLUENT_PLUGIN_DIR'])) ? $_ENV['FLUENT_PLUGIN_DIR'] : '/etc/fluent/plugin');
define("DEFAULT_SOCKET_PATH", (isset($_ENV['FLUENT_SOCKET'])) ? $_ENV['FLUENT_SOCKET'] : '/etc/fluent/plugin');
define("DEFAULT_LISTEN_PORT", 24224);
define("DEFAULT_HTTP_PORT",   8888);
