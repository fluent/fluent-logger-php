<?php
/**
 */

namespace FluentTests\FluentLogger;

use Fluent\Logger;
use Fluent\Logger\FluentLogger;

function fluentTests_FluentLogger_DummyFunction()
{
}

class BaseLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * testing compatible before and after
     *
     * @dataProvider errorHandlerProvider
     */
    public function testRegisterErrorHandler($eh)
    {
        $base = $this->getMockForAbstractClass('Fluent\Logger\FluentLogger');
        $this->assertTrue($base->registerErrorHandler($eh));
    }

    public function errorHandlerProvider()
    {
        return array(
            array(
                'FluentTests\FluentLogger\fluentTests_FluentLogger_DummyFunction'
            ),
            array(
                array($this, 'errorHandlerProvider')
            ),
            array(
                function () {
                }, // closure
            ),
        );
    }

    /**
     * @dataProvider invalidErrorHandlerProvider
     * @expectedException InvalidArgumentException
     */
    public function testRegisterInvalidErrorHandler($eh)
    {
        $base = $this->getMockForAbstractClass('Fluent\Logger\FluentLogger');
        $base->registerErrorHandler($eh);
    }

    public function invalidErrorHandlerProvider()
    {
        return array(
            array(
                null,
            ),
            array(
                array($this, 'errorHandlerProvider_Invalid') // not exists
            ),
            array(
                array($this, 'errorHandlerProvider_Invalid', 'hoge') // invalid
            ),
        );
    }

    public function testUnregisterErrorHandler()
    {
        $base = $this->getMockForAbstractClass('Fluent\Logger\FluentLogger');
        $prop = new \ReflectionProperty($base, 'error_handler');
        $prop->setAccessible(true);
        $base->registerErrorHandler(function() {});
        $this->assertNotNull($prop->getValue($base));
        $base->unregisterErrorHandler();
        $this->assertNull($prop->getValue($base));
    }
}
