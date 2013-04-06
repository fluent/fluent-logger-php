<?php
namespace FluentTests\Logger;

use Fluent\Logger\Entity;
use Fluent\Logger\JsonPacker;

class JsonPackerTest extends \PHPUnit_Framework_TestCase
{
    const TAG           = "debug.test";
    const EXPECTED_TIME = 123456789;

    protected $time;
    protected $expected_data = array();

    public function setUp()
    {
        $this->expected_data = array("abc" => "def");
    }

    public function testPack()
    {
        $entity = new Entity(self::TAG, $this->expected_data, self::EXPECTED_TIME);

        $packer = new JsonPacker();
        $result = $packer->pack($entity);

        /*
         * expected format.
         * ["<Tag>", <Unixtime>, {object}]
         */
        $this->assertStringMatchesFormat('["%s",%d,{"%s":"%s"}]', $result, "unexpected format returns");

        return json_decode($result, true);
    }

    /**
     * @depends testPack
     */
    public function testPackReturnTag($result)
    {
        $this->assertEquals($result['0'], self::TAG);
    }

    /**
     * @depends testPack
     */
    public function testPackReturnTime($result)
    {
        $this->assertEquals($result['1'], self::EXPECTED_TIME);
    }

    /**
     * @depends testPack
     */
    public function testPackReturnData($result)
    {
        $this->assertEquals($result['2'], $this->expected_data);
    }
}
