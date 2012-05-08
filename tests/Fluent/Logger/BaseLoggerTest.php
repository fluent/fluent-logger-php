<?php
/**
 */

//namespace FluentTests\FluentLogger;
//
//use Fluent\Logger;
//use Fluent\Logger\FluentLogger;

function fluentTests_FluentLogger_DummyFunction () {
}

class Fluent_Logger_BaseLoggerTest extends PHPUnit_Framework_TestCase
{
    /**
     * testing compatible before and after
     *
     * @dataProvider errorHandlerProvider
     */
    public function testRegisterErrorHandler($eh)
    {
        $base = $this->getMockForAbstractClass('Fluent_Logger_BaseLogger');
        $this->assertTrue($base->registerErrorHandler($eh));
    }

    public function errorHandlerProvider()
    {
        return array(
            array(
                'fluentTests_FluentLogger_DummyFunction'
            ),
            array(
                array($this, 'errorHandlerProvider')
            ),
        );
    }

    /**
     * @dataProvider invalidErrorHandlerProvider
     * @expectedException InvalidArgumentException
     */
    public function testRegisterInvalidErrorHandler($eh)
    {
        $base = $this->getMockForAbstractClass('Fluent_Logger_BaseLogger');
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
}
