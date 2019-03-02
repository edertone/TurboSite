<?php

/**
 * TurboSite is a web site creation framework.
 *
 * Website : -> http://www.turboframework.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2018 Edertone Advanded Solutions. http://www.edertone.com
 */

namespace org\turbosite\src\test\php\managers;

use Throwable;
use PHPUnit\Framework\TestCase;
use org\turbosite\src\test\php\resources\model\webservice\ServiceWithoutParams;
use org\turbosite\src\test\php\resources\model\webservice\ServiceWithGETandPostParams;


/**
 * WebServiceTest
 *
 * @return void
 */
class WebServiceTest extends TestCase {


    /**
     * @see TestCase::setUpBeforeClass()
     *
     * @return void
     */
    public static function setUpBeforeClass(){

        require_once __DIR__.'/../resources/libs/turbocommons-php-1.0.0.phar';
        require_once __DIR__.'/../resources/libs/turbodepot-php-0.0.1.phar';
    }


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(){

        $this->exceptionMessage = '';
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){

        if($this->exceptionMessage != ''){

            $this->fail($this->exceptionMessage);
        }
    }


    /**
     * @see TestCase::tearDownAfterClass()
     *
     * @return void
     */
    public static function tearDownAfterClass(){

        // Nothing necessary here
    }


    /**
     * testConstruct
     *
     * @return void
     */
    public function testConstruct(){

        // Test empty values
        $this->assertSame('no params received', (new ServiceWithoutParams())->run());
        $this->assertSame('no params received', (new ServiceWithoutParams(null))->run());
        $this->assertSame('no params received', (new ServiceWithoutParams(null, null))->run());
        $this->assertSame('no params received', (new ServiceWithoutParams([], null))->run());
        $this->assertSame('no params received', (new ServiceWithoutParams(null, []))->run());

        try {
            (new ServiceWithoutParams('', []))->run();
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            (new ServiceWithoutParams([], ''))->run();
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            (new ServiceWithoutParams(0, []))->run();
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            (new ServiceWithoutParams([], 0))->run();
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        // Test ok values
        $serviceData = (new ServiceWithGETandPostParams(['0', '1'], ['a' => 'value0', 'b' => 'value1']))->run();
        $this->assertSame('0', $serviceData['0']);
        $this->assertSame('1', $serviceData['1']);
        $this->assertSame('value0', $serviceData['a']);
        $this->assertSame('value1', $serviceData['b']);

        // Test wrong values
        try {
            (new ServiceWithoutParams(['0', '1']))->run();
            $this->exceptionMessage = 'array on get did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            (new ServiceWithGETandPostParams(['0', 1], ['a' => '0', 'b' => '1']))->run();
            $this->exceptionMessage = 'numeric get param did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            (new ServiceWithGETandPostParams(['0', '1'], ['a' => '0', 'b' => 1]))->run();
            $this->exceptionMessage = 'numeric post param did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            (new ServiceWithGETandPostParams(['0'], ['a' => '0', 'b' => '1']))->run();
            $this->exceptionMessage = 'missing 1 did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            (new ServiceWithGETandPostParams(['0', '1'], ['p0' => 'value0']))->run();
            $this->exceptionMessage = 'missing p1 did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            (new ServiceWithGETandPostParams(['0', '1'], ['a' => '0', 'b' => '1', 'c' => '2']))->run();
            $this->exceptionMessage = 'extra post param did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        try {
            (new ServiceWithGETandPostParams('string', ['a' => '0', 'b' => '1']))->run();
            $this->exceptionMessage = 'string did not cause exception';
        } catch (Throwable $e) {
            // We expect an exception to happen
        }

        // Test exceptions
        // Already tested
    }


    /**
     * testGetParam
     *
     * @return void
     */
    public function testGetParam(){

        $this->sut = new ServiceWithGETandPostParams(['v0', 'v1'], ['a' => '0', 'b' => '1']);

        // Test empty values
        // TODO

        // Test ok values
        $this->assertSame('v0', $this->sut->getParam(0));
        $this->assertSame('v1', $this->sut->getParam(1));
        // TODO

        // Test wrong values
        // TODO

        // Test exceptions
        // TODO
    }


    /**
     * testTODO
     *
     * @return void
     */
    public function testTODO(){

        // Test empty values
        // TODO

        // Test ok values
        // TODO

        // Test wrong values
        // TODO

        // Test exceptions
        // TODO

        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    // TODO - all missing tests
}

?>