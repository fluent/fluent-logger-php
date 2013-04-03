<?php
namespace FluentTests\Logger;

use Fluent\Logger\Entity;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    const TAG = "debug.test";


    public function testWhole()
    {
        $time          = time();
        $expected_data = array("abc" => "def");

        $entity = new Entity(self::TAG, $expected_data, $time);
        $this->assertEquals(self::TAG, $entity->getTag(), "unexpected tag `{$entity->getTag()}` returns.");
        $this->assertEquals($expected_data, $entity->getData(), "unexpected data returns");
        $this->assertEquals($time, $entity->getTime(), "unexpected time returns");

        $entity = new Entity(self::TAG, $expected_data);
        $this->assertGreaterThanOrEqual($time, $entity->getTime(), "unexpected time returns");

        $entity = new Entity(self::TAG, $expected_data, "not int");
        $this->assertGreaterThanOrEqual($time, $entity->getTime(), "unexpected time returns");

    }
}
