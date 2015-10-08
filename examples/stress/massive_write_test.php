<?php
require_once __DIR__.'/../../vendor/autoload.php';

if (!getenv("PHP_FLUENT_TEST")) {
    echo 'this test case runs through current server resource. please do with `PHP_FLUENT_TEST=1 php massive_write_test.php`' . PHP_EOL;
    exit;
}

use Fluent\Logger\FluentLogger;

$start_time = microtime(true)+3;
$pids = array();
for ($i = 0; $i<256; $i++) {
    $pid = pcntl_fork();

    if ($pid == -1) {
        exit('opps, could not fork');
    } else if ($pid) {
        $pids[] = $pid;
        // skip
    } else {
        $current = microtime(true);
        $wait = $start_time - $current;
        if ($wait < 0) {
            echo "too long to wait fork." . PHP_EOL;
            exit;
        }

        sleep($wait);
        execute();
        exit;
    }
}

// never leach this block. hehe.
foreach($pids as $_pid) {
    pcntl_waitpid($_pid, $status);
}
echo PHP_EOL;

function execute(){
    //$logger = new FluentLogger("tcp://0.0.0.0:24224");
    $logger = new FluentLogger("unix:///tmp/fluent");

    for(;;){
      $logger->post("debug.test", $_SERVER);
      echo '.';
    }
}