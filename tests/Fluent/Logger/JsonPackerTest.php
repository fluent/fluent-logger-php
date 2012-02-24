<?php
namespace FluentTests\Logger;

use Fluent\Logger\Entity;
use Fluent\Logger\JsonPacker;

class JsonPackerTest extends \PHPUnit_Framework_TestCase
{
    const TAG = "debug.test";

    public function testWhole()
    {
        $time = time();
        $expected_data = array("abc"=>"def");

        $entity = new Entity(self::TAG,$expected_data, $time);

        $packer = new JsonPacker();
        $result = $packer->pack($entity);
        $this->assertStringMatchesFormat('["%s",%d,{"%s":"%s"}]', $result, "unexpected format returns");
        $r_array = json_decode($result,true);

        $this->assertEquals($r_array['0'],$entity->getTag());
        $this->assertEquals($r_array['1'],$entity->getTime());
        $this->assertEquals($r_array['2'],$entity->getData());
    }
}
