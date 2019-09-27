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

use Throwable;
use PHPUnit\Framework\TestCase;
use org\turbosite\src\test\resources\model\webservice\ServiceWithoutParams;
use org\turbosite\src\test\resources\model\webservice\ServiceWithGETandPostParams;
use org\turbosite\src\test\resources\model\webservice\ServiceWithPostParams;
use org\turbosite\src\test\resources\model\webservice\ServiceWithPostParamsOptionalAndDefaultValues;
use org\turbosite\src\test\resources\model\webservice\ServiceWithInvalidPostParameterName;
use org\turbosite\src\test\resources\model\webservice\ServiceWithInvalidPostParameterArrayLen;
use org\turbosite\src\test\resources\model\webservice\ServiceWithInvalidPostParameterType;
use org\turbosite\src\test\resources\model\webservice\ServiceWithInvalidPostParameterRequiredValue;
use org\turbosite\src\test\resources\model\webservice\ServiceWithInvalidPostParameterRestrictedValue;
use org\turbosite\src\test\resources\model\webservice\ServiceWithPostParameterNotTyped;
use org\turbosite\src\test\resources\model\webservice\ServiceWithPostParameterBoolTyped;
use org\turbosite\src\test\resources\model\webservice\ServiceWithPostParameterNumberTyped;
use org\turbosite\src\test\resources\model\webservice\ServiceWithPostParameterStringTyped;
use org\turbosite\src\test\resources\model\webservice\ServiceWithPostParameterArrayTyped;
use org\turbosite\src\test\resources\model\webservice\ServiceWithPostParameterObjectTyped;


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

        // Nothing necessary here
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
            $this->assertRegExp('/Argument 1.*must be of the type array or null.*string given/', $e->getMessage());
        }

        try {
            (new ServiceWithoutParams([], ''))->run();
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Argument 2.*must be of the type array or null.*string given/', $e->getMessage());
        }

        try {
            (new ServiceWithoutParams(0, []))->run();
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Argument 1.*must be of the type array or null.*integer given/', $e->getMessage());
        }

        try {
            (new ServiceWithoutParams([], 0))->run();
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Argument 2.*must be of the type array or null.*integer given/', $e->getMessage());
        }

        // Test ok values
        $serviceData = (new ServiceWithGETandPostParams(['0', '1'], ['a' => 'value0', 'b' => 'value1']))->run();
        $this->assertSame('0', $serviceData['0']);
        $this->assertSame('1', $serviceData['1']);
        $this->assertSame('value0', $serviceData['a']);
        $this->assertSame('value1', $serviceData['b']);

        $serviceData = (new ServiceWithGETandPostParams(['0', '1'], ['a' => '0', 'b' => 1]))->run();
        $this->assertSame('0', $serviceData['0']);
        $this->assertSame('1', $serviceData['1']);
        $this->assertSame('0', $serviceData['a']);
        $this->assertSame('1', $serviceData['b']);

        $serviceData = (new ServiceWithPostParams([], ['a' => '0', 'b' => 1]))->run();
        $this->assertSame('0', $serviceData['a']);
        $this->assertSame('1', $serviceData['b']);

        $serviceData = (new ServiceWithPostParamsOptionalAndDefaultValues([], ['a' => '0']))->run();
        $this->assertSame('0', $serviceData['a']);
        $this->assertSame(null, $serviceData['b']);
        $this->assertSame('default', $serviceData['c']);

        // Test wrong values
        // Test exceptions
        try {
            (new ServiceWithoutParams(['0', '1']))->run();
            $this->exceptionMessage = 'array on get did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid number of GET parameters passed to service. Received 2 but expected 0/', $e->getMessage());
        }

        try {
            (new ServiceWithGETandPostParams(['0', 1], ['a' => '0', 'b' => '1']))->run();
            $this->exceptionMessage = 'numeric get param did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/All GET parameters must be strings/', $e->getMessage());
        }

        try {
            (new ServiceWithGETandPostParams(['0'], ['a' => '0', 'b' => '1']))->run();
            $this->exceptionMessage = 'missing 1 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid number of GET parameters passed to service. Received 1 but expected 2/', $e->getMessage());
        }

        try {
            (new ServiceWithGETandPostParams(['0', '1'], ['p0' => 'value0']))->run();
            $this->exceptionMessage = 'p0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Missing mandatory POST parameter: a/', $e->getMessage());
        }

        try {
            (new ServiceWithGETandPostParams(['0', '1'], ['a' => '0', 'b' => '1', 'c' => '2']))->run();
            $this->exceptionMessage = 'extra post param did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Unexpected POST parameter received: c/', $e->getMessage());
        }

        try {
            (new ServiceWithGETandPostParams('string', ['a' => '0', 'b' => '1']))->run();
            $this->exceptionMessage = 'string did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Argument 1.*must be of the type array or null.*string given/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParams([], ['a' => '0']))->run();
            $this->exceptionMessage = 'missing post b did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Missing mandatory POST parameter: b/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParams([], ['a' => '0', 'b' => '1', 'c' => '3']))->run();
            $this->exceptionMessage = 'extra post c did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Unexpected POST parameter received: c/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParams(['0'], ['a' => '0', 'b' => '1']))->run();
            $this->exceptionMessage = 'extra post c did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid number of GET parameters passed to service. Received 1 but expected 0/', $e->getMessage());
        }
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
     * testGetPost
     *
     * @return void
     */
    public function testGetPost(){

        $service = new ServiceWithPostParameterStringTyped([], ['a' => '"string"']);

        // Test empty values
        try {
            $service->getPost(null);
            $this->exceptionMessage = 'null post did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, null given/', $e->getMessage());
        }

        try {
            $service->getPost('');
            $this->exceptionMessage = '"" post did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/POST parameter is not enabled by the service: /', $e->getMessage());
        }

        // Test ok values
        // Already tested at testSetup()

        // Test wrong values
        // Test exceptions


        try {
            (new ServiceWithPostParameterStringTyped([], ['a' => '"string"']))->getPost('nonexistant');
            $this->exceptionMessage = 'nonexistant post did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/POST parameter is not enabled by the service: nonexistant/', $e->getMessage());
        }
    }


    /**
     * testSetup
     *
     * @return void
     */
    public function testSetup(){

        // Test empty values
        // Not necessary

        // Test ok values

        // Non typed post parameter
        $this->assertSame('null', (new ServiceWithPostParameterNotTyped([], ['a' => null]))->run());
        $this->assertSame('false', (new ServiceWithPostParameterNotTyped([], ['a' => false]))->run());
        $this->assertSame('0', (new ServiceWithPostParameterNotTyped([], ['a' => 0]))->run());
        $this->assertSame('0', (new ServiceWithPostParameterNotTyped([], ['a' => '0']))->run());
        $this->assertSame('rawstring', (new ServiceWithPostParameterNotTyped([], ['a' => 'rawstring']))->run());
        $this->assertSame('"jsonencodedstring"', (new ServiceWithPostParameterNotTyped([], ['a' => '"jsonencodedstring"']))->run());
        $this->assertSame('[1,2,3]', (new ServiceWithPostParameterNotTyped([], ['a' => [1,2,3]]))->run());
        $this->assertSame('{"a":1,"b":2}', (new ServiceWithPostParameterNotTyped([], ['a' => ["a" => 1, "b" => 2]]))->run());

        // BOOL typed post parameter
        try {
            (new ServiceWithPostParameterBoolTyped([], ['a' => null]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterBoolTyped null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded boolean but was null/', $e->getMessage());
        }

        $this->assertSame(false, (new ServiceWithPostParameterBoolTyped([], ['a' => false]))->run());
        $this->assertSame(false, (new ServiceWithPostParameterBoolTyped([], ['a' => 'false']))->run());
        $this->assertSame(true, (new ServiceWithPostParameterBoolTyped([], ['a' => true]))->run());
        $this->assertSame(true, (new ServiceWithPostParameterBoolTyped([], ['a' => 'true']))->run());

        try {
            (new ServiceWithPostParameterBoolTyped([], ['a' => 0]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterBoolTyped 0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded boolean but was 0/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterBoolTyped([], ['a' => 'rawstring']))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterBoolTyped 0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded boolean but was rawstring/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterBoolTyped([], ['a' => [1,2,3]]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterBoolTyped array did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded boolean but was .1.2.3./', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterBoolTyped([], ['a' => ["a" => 1, "b" => 2]]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterBoolTyped object did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded boolean but was ..a..1..b..2./', $e->getMessage());
        }

        // NUMBER typed post parameter
        try {
            (new ServiceWithPostParameterNumberTyped([], ['a' => null]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterNumberTyped null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded number but was null/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterNumberTyped([], ['a' => false]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterNumberTyped false did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded number but was false/', $e->getMessage());
        }

        $this->assertSame(0, (new ServiceWithPostParameterNumberTyped([], ['a' => 0]))->run());
        $this->assertSame(0, (new ServiceWithPostParameterNumberTyped([], ['a' => '0']))->run());
        $this->assertSame(1234, (new ServiceWithPostParameterNumberTyped([], ['a' => 1234]))->run());
        $this->assertSame(1234, (new ServiceWithPostParameterNumberTyped([], ['a' => '1234']))->run());
        $this->assertSame(1234.89, (new ServiceWithPostParameterNumberTyped([], ['a' => 1234.890]))->run());
        $this->assertSame(1234.89, (new ServiceWithPostParameterNumberTyped([], ['a' => '1234.890']))->run());
        $this->assertSame(-250, (new ServiceWithPostParameterNumberTyped([], ['a' => -250]))->run());
        $this->assertSame(-25012, (new ServiceWithPostParameterNumberTyped([], ['a' => -'25012']))->run());
        $this->assertSame(-25012.792, (new ServiceWithPostParameterNumberTyped([], ['a' => -25012.792]))->run());
        $this->assertSame(-25012.792, (new ServiceWithPostParameterNumberTyped([], ['a' => -'25012.792']))->run());

        try {
            (new ServiceWithPostParameterNumberTyped([], ['a' => 'rawstring']))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterNumberTyped rawstring did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded number but was rawstring/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterNumberTyped([], ['a' => [1,2,3]]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterNumberTyped [1,2,3] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded number but was .1.2.3./', $e->getMessage());
        }

        // STRING typed post parameter
        try {
            (new ServiceWithPostParameterStringTyped([], ['a' => null]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterStringTyped null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded string but was null/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterStringTyped([], ['a' => false]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterStringTyped false did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded string but was false/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterStringTyped([], ['a' => 0]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterStringTyped 0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded string but was 0/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterStringTyped([], ['a' => '0']))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterStringTyped "0" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded string but was 0/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterStringTyped([], ['a' => 'rawstring']))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterStringTyped "rawstring" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded string but was rawstring/', $e->getMessage());
        }

        $this->assertSame('jsonencodedstring', (new ServiceWithPostParameterStringTyped([], ['a' => '"jsonencodedstring"']))->run());
        $this->assertSame('', (new ServiceWithPostParameterStringTyped([], ['a' => '""']))->run());
        $this->assertSame('1234', (new ServiceWithPostParameterStringTyped([], ['a' => '"1234"']))->run());
        $this->assertSame('[1,2,3,4]', (new ServiceWithPostParameterStringTyped([], ['a' => '"[1,2,3,4]"']))->run());

        try {
            (new ServiceWithPostParameterStringTyped([], ['a' => [1,2,3]]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterStringTyped [1,2,3] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded string but was .1.2.3./', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterStringTyped([], ['a' => ["a" => 1, "b" => 2]]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterStringTyped ["a" => 1, "b" => 2] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded string but was ..a..1..b..2./', $e->getMessage());
        }

        // ARRAY typed post parameter
        try {
            (new ServiceWithPostParameterArrayTyped([], ['a' => null]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterArrayTyped null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded array but was null/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterArrayTyped([], ['a' => false]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterArrayTyped false did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded array but was false/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterArrayTyped([], ['a' => 0]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterArrayTyped 0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded array but was 0/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterArrayTyped([], ['a' => 'rawstring']))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterArrayTyped "rawstring" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded array but was rawstring/', $e->getMessage());
        }

        $this->assertSame([], (new ServiceWithPostParameterArrayTyped([], ['a' => []]))->run());
        $this->assertSame([1,2,3], (new ServiceWithPostParameterArrayTyped([], ['a' => [1,2,3]]))->run());

        try {
            (new ServiceWithPostParameterArrayTyped([], ['a' => ["a" => 1, "b" => 2]]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterArrayTyped ["a" => 1, "b" => 2]] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded array but was ."a".1."b".2./', $e->getMessage());
        }

        // OBJECT typed post parameter
        try {
            (new ServiceWithPostParameterObjectTyped([], ['a' => null]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterObjectTyped null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded object but was null/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterObjectTyped([], ['a' => false]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterObjectTyped false did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded object but was false/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterObjectTyped([], ['a' => 0]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterObjectTyped 0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded object but was 0/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterObjectTyped([], ['a' => 'rawstring']))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterObjectTyped "rawstring" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded object but was rawstring/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterObjectTyped([], ['a' => [1,2,3]]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterObjectTyped [1,2,3] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded object but was .1,2,3./', $e->getMessage());
        }

        $this->assertEquals((object) ['a' => 1], (new ServiceWithPostParameterObjectTyped([], ['a' => ["a" => 1]]))->run());
        $this->assertEquals((object) ['a' => 1, 'b' => 2], (new ServiceWithPostParameterObjectTyped([], ['a' => ["a" => 1, "b" => 2]]))->run());
        $this->assertEquals((object) ['a' => 1, 'b' => 2], (new ServiceWithPostParameterObjectTyped([], ['a' => (object) ['a' => 1, 'b' => 2]]))->run());
        $this->assertEquals((object) ['a' => 1, 'b' => 2], (new ServiceWithPostParameterObjectTyped([], ['a' => '{"a":1,"b":2}']))->run());

        // Test wrong values
        // Test exceptions

        try {
            (new ServiceWithInvalidPostParameterName([], []))->run();
            $this->exceptionMessage = 'ServiceWithInvalidPostParameterName did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Each enabled POST parameter array first value must be a string/', $e->getMessage());
        }

        try {
            (new ServiceWithInvalidPostParameterArrayLen([], []))->run();
            $this->exceptionMessage = 'ServiceWithInvalidPostParameterArrayLen did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Each enabled POST parameter must be an array with min 1 and max 5 elements/', $e->getMessage());
        }

        try {
            (new ServiceWithInvalidPostParameterType([], []))->run();
            $this->exceptionMessage = 'ServiceWithInvalidPostParameterType did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/POST param .a. element.1. .invalid-type-here. must be WebService..NOT_TYPED, WebService..BOOL, WebService..NUMBER, WebService..STRING, WebService..ARRAY or WebService..OBJECT/', $e->getMessage());
        }

        try {
            (new ServiceWithInvalidPostParameterRequiredValue([], []))->run();
            $this->exceptionMessage = 'ServiceWithInvalidPostParameterRequiredValue did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/POST param .a. element.2. .ARRAY. must be WebService..REQUIRED or WebService..NOT_REQUIRED/', $e->getMessage());
        }

        try {
            (new ServiceWithInvalidPostParameterRestrictedValue([], []))->run();
            $this->exceptionMessage = 'ServiceWithInvalidPostParameterRestrictedValue did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Missing mandatory POST parameter: a/', $e->getMessage());
        }

        try {
            (new ServiceWithInvalidPostParameterRestrictedValue([], ['a' => '0']))->run();
            $this->exceptionMessage = 'ServiceWithInvalidPostParameterRestrictedValue did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/POST param .a. element.3. .BOOL. must be WebService..NOT_RESTRICTED or an array of values/', $e->getMessage());
        }
    }


    /**
     * testRun
     *
     * @return void
     */
    public function testRun(){

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


    /**
     * testGenerateError
     *
     * @return void
     */
    public function testGenerateError(){

        $sut = new ServiceWithoutParams();

        // Test empty values
        try {
            $sut->generateError(null, null);
            $this->exceptionMessage = 'null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type integer, null given/', $e->getMessage());
        }

        try {
            $sut->generateError('', '');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type integer, string given/', $e->getMessage());
        }

        try {
            $sut->generateError(0, null);
            $this->exceptionMessage = '0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type string, null given/', $e->getMessage());
        }

        // Test ok values
        $error = $sut->generateError(400, 'title');
        $this->assertSame(400, $error->code);
        $this->assertSame('title', $error->title);
        $this->assertSame('', $error->message);
        $this->assertSame('', $error->trace);

        $error = $sut->generateError(400, 'title', 'message', 'trace');
        $this->assertSame(400, $error->code);
        $this->assertSame('title', $error->title);
        $this->assertSame('message', $error->message);
        $this->assertSame('trace', $error->trace);

        // Test wrong values
        // Test exceptions
        // Not necessary
    }
}

// TODO - Implement tests for restricted post parameter values

?>