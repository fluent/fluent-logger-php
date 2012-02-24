<?php
/**
 */

namespace FluentTests\FluentLogger;

use Fluent\Logger;
use Fluent\Logger\FluentLogger;

function fluentTests_FluentLogger_DummyFunction () {
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
        $base = $this->getMockForAbstractClass('Fluent\Logger\BaseLogger');
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
                function () {}, // closure
            ),
        );
    }

    /**
     * @dataProvider invalidErrorHandlerProvider
     */
    public function testRegisterInvalidErrorHandler($eh)
    {
        $base = $this->getMockForAbstractClass('Fluent\Logger\BaseLogger');
        try {
            $base->registerErrorHandler($eh);
        } catch (\Exception $e) {
            $this->assertContains('could not register error handler', $e->getMessage());
        }
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
