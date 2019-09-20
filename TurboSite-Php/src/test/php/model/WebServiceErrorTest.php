<?php

/**
 * TurboSite is a web site creation framework.
 *
 * Website : -> http://www.turboframework.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2018 Edertone Advanded Solutions. http://www.edertone.com
 */

namespace org\turbosite\src\test\php\model;

use PHPUnit\Framework\TestCase;
use Throwable;
use org\turbosite\src\main\php\model\WebServiceError;


/**
 * WebServiceErrorTest
 *
 * @return void
 */
class WebServiceErrorTest extends TestCase {


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
     * testCreateInstance
     *
     * @return void
     */
    public function testCreateInstance(){

        // Test empty values
        try {
            WebServiceError::createInstance(null, null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type integer, null given/', $e->getMessage());
        }

        try {
            WebServiceError::createInstance('', '');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type integer, string given/', $e->getMessage());
        }

        try {
            WebServiceError::createInstance(0, null);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, null given/', $e->getMessage());
        }

        // Test ok values
        $error = WebServiceError::createInstance(400, 'title');
        $this->assertSame(400, $error->code);
        $this->assertSame('title', $error->title);
        $this->assertSame('', $error->message);
        $this->assertSame('', $error->trace);

        $error = WebServiceError::createInstance(400, 'title', 'message', 'trace');
        $this->assertSame(400, $error->code);
        $this->assertSame('title', $error->title);
        $this->assertSame('message', $error->message);
        $this->assertSame('trace', $error->trace);

        // Test wrong values
        // Test exceptions
        // Not necessary
    }
}

?>