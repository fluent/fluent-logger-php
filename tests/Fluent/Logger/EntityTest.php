<?php
//namespace FluentTests\Logger;
//
//use Fluent\Logger\Entity;

class Fluent_Logger_EntityTest extends PHPUnit_Framework_TestCase
{
    const TAG = "debug.test";


    public function testWhole()
    {
        $time = time();
        $expected_data = array("abc"=>"def");

        $entity = new Fluent_Logger_Entity(self::TAG,$expected_data, $time);
        $this->assertEquals(self::TAG, $entity->getTag(),"unexpected tag `{$entity->getTag()}` returns.");
        $this->assertEquals($expected_data, $entity->getData(), "unexpected data returns");
        $this->assertEquals($time, $entity->getTime(), "unexpected time returns");

        $entity = new Fluent_Logger_Entity(self::TAG,$expected_data);
        $this->assertGreaterThanOrEqual($time, $entity->getTime(), "unexpected time returns");

        $entity = new Fluent_Logger_Entity(self::TAG,$expected_data,"not int");
        $this->assertGreaterThanOrEqual($time, $entity->getTime(), "unexpected time returns");

    }
}
