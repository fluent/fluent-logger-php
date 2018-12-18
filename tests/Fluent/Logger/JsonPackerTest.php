<?php
namespace FluentTests\Logger;

use Fluent\Logger\Entity;
use Fluent\Logger\JsonPacker;
use PHPUnit\Framework\TestCase;

class JsonPackerTest extends TestCase
{
    const TAG           = "debug.test";
    const EXPECTED_TIME = 123456789;

    protected $time;
    protected $expected_data;
    protected $unexpected_data;

    public function setUp()
    {
        $this->expected_data = array("abc" => "def");
        $this->unexpected_data = array("data" => random_bytes(100));
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

    public function testUnhandledJsonError()
    {
        $entity = new Entity(self::TAG, $this->unexpected_data, self::EXPECTED_TIME);

        $this->expectException(\UnexpectedValueException::class);

        (new JsonPacker)->pack($entity);
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
