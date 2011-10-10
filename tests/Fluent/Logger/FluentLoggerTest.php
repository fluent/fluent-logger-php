<?php

namespace FluentTests\FluentLogger;

use Fluent\Logger;
use Fluent\Logger\FluentLogger;

class FluentLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * check sending format is valid.
     */
    public function testPackImplMethod()
    {
        // expected format.
        // [<Tag>, 
        //   [
        //     [<Unixtime>,<object>]
        //   ]]
        $string = FluentLogger::pack_impl("debug.test",array("hello"=>"world"));
        $result = json_decode($string,true);
        $this->assertEquals("debug.test",$result[0]);
        $this->assertEquals("world",$result[1][0][1]['hello']);
    }
}
