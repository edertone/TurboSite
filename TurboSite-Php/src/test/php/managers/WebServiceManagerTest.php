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
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbosite\src\test\resources\model\webservice\ServiceWithoutParams;
use org\turbosite\src\test\resources\model\webservice\ServiceWithUrlAndPostParams;
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
use org\turbosite\src\test\resources\model\webservice\ServiceWithInvalidPostParameter;
use org\turbosite\src\test\resources\model\webservice\ServiceWithInvalidUrlParameter;
use org\turbosite\src\test\resources\model\webservice\ServiceWithUrlParamsOptionalAndDefaultValues;
use org\turbosite\src\test\resources\model\webservice\ServiceWithInvalidUrlParameterArrayLen;
use org\turbosite\src\test\resources\model\webservice\ServiceWithInvalidUrlParameterType;
use org\turbosite\src\test\resources\model\webservice\ServiceWithInvalidUrlParameterRestrictedValue;
use org\turbosite\src\test\resources\model\webservice\ServiceWithUrlParameterBoolTyped;
use org\turbosite\src\test\resources\model\webservice\ServiceWithUrlParameterNumberTyped;
use org\turbosite\src\test\resources\model\webservice\ServiceWithUrlParameterStringTyped;
use org\turbosite\src\test\resources\model\webservice\ServiceWithUrlParameterArrayTyped;
use org\turbosite\src\test\resources\model\webservice\ServiceWithUrlParameterObjectTyped;
use org\turbosite\src\test\resources\model\webservice\ServiceWithUrlParams3Mandatory;
use org\turbosite\src\test\resources\model\webservice\ServiceWithUrlParams3IncorrectMandatory;
use org\turbosite\src\test\resources\model\webservice\ServiceWithUrlParams5LastNotMandatory;
use org\turbosite\src\test\resources\model\webservice\ServiceWithUrl4ParametersDeclaredViaInt;
use org\turbosite\src\test\resources\model\webservice\ServiceWithPostParameterIntTyped;
use org\turbosite\src\test\resources\model\webservice\ServiceWithUrlParameterIntTyped;
use org\turbosite\src\test\resources\model\webservice\ServiceWithPostParamsAsArrayOfStrings;
use org\turbosite\src\test\resources\model\webservice\ServiceWithInvalidTypeDefaultUrlParamValue;
use org\turbotesting\src\main\php\utils\AssertUtils;


/**
 * WebServiceManagerTest
 *
 * @return void
 */
class WebServiceManagerTest extends TestCase {


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

        AssertUtils::throwsException(function() {(new ServiceWithoutParams('', []))->run();}, '/Argument 1.*must be of the type array or null.*string given/');
        AssertUtils::throwsException(function() {(new ServiceWithoutParams([], ''))->run();}, '/Argument 2.*must be of the type array or null.*string given/');
        AssertUtils::throwsException(function() {(new ServiceWithoutParams(0, []))->run();}, '/Argument 1.*must be of the type array or null.*int(eger)? given/');
        AssertUtils::throwsException(function() {(new ServiceWithoutParams([], 0))->run();}, '/Argument 2.*must be of the type array or null.*int(eger)? given/');

        // Test ok values
        $serviceData = (new ServiceWithUrlAndPostParams(['0', '1'], ['a' => 'value0', 'b' => 'value1']))->run();
        $this->assertSame('0', $serviceData['0']);
        $this->assertSame('1', $serviceData['1']);
        $this->assertSame('value0', $serviceData['a']);
        $this->assertSame('value1', $serviceData['b']);

        $serviceData = (new ServiceWithUrlAndPostParams(['0', '1'], ['a' => '0', 'b' => 1]))->run();
        $this->assertSame('0', $serviceData['0']);
        $this->assertSame('1', $serviceData['1']);
        $this->assertSame('0', $serviceData['a']);
        $this->assertSame('1', $serviceData['b']);

        $serviceData = (new ServiceWithUrlAndPostParams(['0', 1], ['a' => '0', 'b' => '1']))->run();
        $this->assertSame('0', $serviceData['0']);
        $this->assertSame('1', $serviceData['1']);
        $this->assertSame('0', $serviceData['a']);
        $this->assertSame('1', $serviceData['b']);

        $serviceData = (new ServiceWithPostParams([], ['a' => '0', 'b' => 1]))->run();
        $this->assertSame('0', $serviceData['a']);
        $this->assertSame('1', $serviceData['b']);

        $serviceData = (new ServiceWithPostParamsAsArrayOfStrings([], ['a' => '0', 'b' => 1, 'c' => '2', 'd' => 3]))->run();
        $this->assertSame('0', $serviceData['a']);
        $this->assertSame('1', $serviceData['b']);
        $this->assertSame('2', $serviceData['c']);
        $this->assertSame('3', $serviceData['d']);

        $serviceData = (new ServiceWithPostParamsOptionalAndDefaultValues([], ['a' => '0']))->run();
        $this->assertSame('0', $serviceData['a']);
        $this->assertSame(null, $serviceData['b']);
        $this->assertSame('default', $serviceData['c']);

        $serviceData = (new ServiceWithUrlParamsOptionalAndDefaultValues(['0', '1']))->run();
        $this->assertSame('0', $serviceData['0']);
        $this->assertSame('1', $serviceData['1']);
        $this->assertSame('default', $serviceData['2']);

        $serviceData = (new ServiceWithUrlParamsOptionalAndDefaultValues(['0', '1', 2]))->run();
        $this->assertSame('0', $serviceData['0']);
        $this->assertSame('1', $serviceData['1']);
        $this->assertSame('2', $serviceData['2']);

        $serviceData = (new ServiceWithUrlParamsOptionalAndDefaultValues([10.2, 'rawstring', [1,2,3]]))->run();
        $this->assertSame('10.2', $serviceData['0']);
        $this->assertSame('rawstring', $serviceData['1']);
        $this->assertSame('[1,2,3]', $serviceData['2']);

        // Test wrong values
        AssertUtils::throwsException(function() {(new ServiceWithoutParams(['0', '1']))->run();}, '/Unexpected URL parameter received at 0/');
        AssertUtils::throwsException(function() {(new ServiceWithUrlAndPostParams(['0'], ['a' => '0', 'b' => '1']))->run();}, '/Missing mandatory URL parameter at 1/');
        AssertUtils::throwsException(function() {(new ServiceWithUrlAndPostParams(['0', '1'], ['p0' => 'value0']))->run();}, '/Missing mandatory POST parameter: a/');
        AssertUtils::throwsException(function() {(new ServiceWithUrlAndPostParams(['0', '1'], ['a' => '0', 'b' => '1', 'c' => '2']))->run();}, '/Unexpected POST parameter received: c/');
        AssertUtils::throwsException(function() {(new ServiceWithUrlAndPostParams('string', ['a' => '0', 'b' => '1']))->run();}, '/Argument 1.*must be of the type array or null.*string given/');
        AssertUtils::throwsException(function() {(new ServiceWithPostParams([], ['a' => '0']))->run();}, '/Missing mandatory POST parameter: b/');
        AssertUtils::throwsException(function() {(new ServiceWithPostParams([], ['a' => '0', 'b' => '1', 'c' => '3']))->run();}, '/Unexpected POST parameter received: c/');
        AssertUtils::throwsException(function() {(new ServiceWithPostParams(['0'], ['a' => '0', 'b' => '1']))->run();}, '/Unexpected URL parameter received at 0/');
        AssertUtils::throwsException(function() {(new ServiceWithUrlParamsOptionalAndDefaultValues([]))->run();}, '/Missing mandatory URL parameter at 0/');
        AssertUtils::throwsException(function() {(new ServiceWithUrlParamsOptionalAndDefaultValues(['0']))->run();}, '/Missing mandatory URL parameter at 1/');
        AssertUtils::throwsException(function() {$service = new ServiceWithoutParams(); $service->isUrlDataMandatory = false;}, '/property isUrlDataMandatory does not exist/');
    }


    /**
     * testGetUrlParam
     *
     * @return void
     */
    public function testGetUrlParam(){

        // Test empty values
        AssertUtils::throwsException(function() {(new ServiceWithUrlParamsOptionalAndDefaultValues([0, 1]))->getUrlParam(null);}, '/must be of the type int(eger)?, null given/');
        AssertUtils::throwsException(function() {(new ServiceWithUrlParamsOptionalAndDefaultValues([0, 1]))->getUrlParam('');}, '/must be of the type int(eger)?, string given/');

        // Test ok values
        $service = new ServiceWithUrlAndPostParams(['v0', 'v1'], ['a' => '0', 'b' => '1']);
        $this->assertSame('v0', $service->getUrlParam(0));
        $this->assertSame('v1', $service->getUrlParam(1));

        $service = new ServiceWithUrlParamsOptionalAndDefaultValues([0, 1]);
        $this->assertSame('0', $service->getUrlParam(0));
        $this->assertSame('1', $service->getUrlParam(1));

        $service = new ServiceWithUrlParamsOptionalAndDefaultValues(['hello', 1]);
        $this->assertSame('hello', $service->getUrlParam(0));
        $this->assertSame('1', $service->getUrlParam(1));

        $service = new ServiceWithUrlParamsOptionalAndDefaultValues([1, [1,2,3]]);
        $this->assertSame('1', $service->getUrlParam(0));
        $this->assertSame('[1,2,3]', $service->getUrlParam(1));

        // Test wrong values
        AssertUtils::throwsException(function() {(new ServiceWithUrlParamsOptionalAndDefaultValues([0, 1]))->getUrlParam(-3);}, '/Invalid URL parameter index: -3/');
        AssertUtils::throwsException(function() {(new ServiceWithUrlParamsOptionalAndDefaultValues([0, 1]))->getUrlParam(4);}, '/Disabled parameter URL index 4 requested/');

        // Test exceptions
        // Not necessary
    }


    /**
     * testGetPostParam
     *
     * @return void
     */
    public function testGetPostParam(){

        $service = new ServiceWithPostParameterStringTyped([], ['a' => '"string"']);

        $this->assertNotNull($service);

        // Test empty values
        AssertUtils::throwsException(function() use ($service) {$service->getPostParam(null);}, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() use ($service) {$service->getPostParam('');}, '/POST parameter is not enabled by the service: /');

        // Test ok values
        // Already tested at testSetup()

        // Test wrong values
        // Test exceptions

        AssertUtils::throwsException(function() {(new ServiceWithPostParameterStringTyped([], ['a' => '"string"']))->getPostParam('nonexistant');}, '/POST parameter is not enabled by the service: nonexistant/');
    }


    /**
     * testGetPostParamSerialized
     *
     * @return void
     */
    public function testGetPostParamSerialized(){

        $service = new ServiceWithPostParameterNotTyped([], ['a' => '{"a": 1, "b": 2}']);

        $this->assertNotNull($service);

        $someClass = (new class {
            public $a = 0;
            public $b = 0;
        });

        // Test empty values
        AssertUtils::throwsException(function() use ($service, $someClass) {$service->getPostParamSerialized(null, $someClass);}, '/must be of the type string, null given/');
        AssertUtils::throwsException(function() use ($service, $someClass) {$service->getPostParamSerialized('', $someClass);}, '/POST parameter is not enabled by the service: /');

        // Test ok values
        $serialized = $service->getPostParamSerialized('a', $someClass);

        $this->assertSame(1, $serialized->a);
        $this->assertSame(2, $serialized->b);

        // Test wrong values
        // Test exceptions
        // Already tested on testGetPostParam
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

        // URL parameters that are declared with an integer value
        try {
            (new ServiceWithUrl4ParametersDeclaredViaInt([]))->run();
            $this->exceptionMessage = 'ServiceWithUrl4ParametersDeclaredViaInt [] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Missing mandatory URL parameter at 0/', $e->getMessage());
        }

        try {
            (new ServiceWithUrl4ParametersDeclaredViaInt([1,2]))->run();
            $this->exceptionMessage = 'ServiceWithUrl4ParametersDeclaredViaInt [1,2] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Missing mandatory URL parameter at 2/', $e->getMessage());
        }

        try {
            (new ServiceWithUrl4ParametersDeclaredViaInt([1,2,3]))->run();
            $this->exceptionMessage = 'ServiceWithUrl4ParametersDeclaredViaInt [1,2,3] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Missing mandatory URL parameter at 3/', $e->getMessage());
        }

        try {
            (new ServiceWithUrl4ParametersDeclaredViaInt([1,2,3,4,5]))->run();
            $this->exceptionMessage = 'ServiceWithUrl4ParametersDeclaredViaInt [1,2,3,4,5] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Unexpected URL parameter received at 4/', $e->getMessage());
        }

        $this->assertSame(['0', '1', '2', '3'], (new ServiceWithUrl4ParametersDeclaredViaInt([0, 1, 2, 3]))->run());
        $this->assertSame(['a', 'b', 'c', 'd'], (new ServiceWithUrl4ParametersDeclaredViaInt(['a', 'b', 'c', 'd']))->run());
        $this->assertSame(['235', 'hello', '[1,2,"a"]', '{"a":1}'], (new ServiceWithUrl4ParametersDeclaredViaInt([235, 'hello', [1,2,'a'], (object) ['a' => 1]]))->run());

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

        // BOOL typed get parameter
        try {
            (new ServiceWithUrlParameterBoolTyped([null]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterBoolTyped null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded boolean but was null/', $e->getMessage());
        }

        $this->assertSame(false, (new ServiceWithUrlParameterBoolTyped([false]))->run());
        $this->assertSame(false, (new ServiceWithUrlParameterBoolTyped(['false']))->run());
        $this->assertSame(true, (new ServiceWithUrlParameterBoolTyped([true]))->run());
        $this->assertSame(true, (new ServiceWithUrlParameterBoolTyped(['true']))->run());

        try {
            (new ServiceWithUrlParameterBoolTyped([0]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterBoolTyped 0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded boolean but was 0/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterBoolTyped(['rawstring']))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterBoolTyped 0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded boolean but was rawstring/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterBoolTyped([[1,2,3]]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterBoolTyped array did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded boolean but was .1.2.3./', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterBoolTyped([["a" => 1, "b" => 2]]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterBoolTyped object did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded boolean but was ..a..1..b..2./', $e->getMessage());
        }

        // INT typed post parameter
        try {
            (new ServiceWithPostParameterIntTyped([], ['a' => null]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterIntTyped null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded integer but was null/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterIntTyped([], ['a' => false]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterIntTyped false did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded integer but was false/', $e->getMessage());
        }

        $this->assertSame(0, (new ServiceWithPostParameterIntTyped([], ['a' => 0]))->run());
        $this->assertSame(0, (new ServiceWithPostParameterIntTyped([], ['a' => '0']))->run());
        $this->assertSame(1234, (new ServiceWithPostParameterIntTyped([], ['a' => 1234]))->run());
        $this->assertSame(1234, (new ServiceWithPostParameterIntTyped([], ['a' => '1234']))->run());

        try {
            (new ServiceWithPostParameterIntTyped([], ['a' => 1234.890]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterIntTyped 1234.890 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded integer but was 1234.89/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterIntTyped([], ['a' => '1234.890']))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterIntTyped "1234.890" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded integer but was 1234.89/', $e->getMessage());
        }

        $this->assertSame(-250, (new ServiceWithPostParameterIntTyped([], ['a' => -250]))->run());
        $this->assertSame(-25012, (new ServiceWithPostParameterIntTyped([], ['a' => '-25012']))->run());

        try {
            (new ServiceWithPostParameterIntTyped([], ['a' => -25012.792]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterIntTyped -25012.792 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded integer but was -25012.792/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterIntTyped([], ['a' => '-25012.792']))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterIntTyped -25012.792 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded integer but was -25012.792/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterIntTyped([], ['a' => 'rawstring']))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterIntTyped rawstring did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded integer but was rawstring/', $e->getMessage());
        }

        try {
            (new ServiceWithPostParameterIntTyped([], ['a' => [1,2,3]]))->run();
            $this->exceptionMessage = 'ServiceWithPostParameterIntTyped [1,2,3] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected a POST param to be a json encoded integer but was .1.2.3./', $e->getMessage());
        }

        // INT typed URL parameter
        try {
            (new ServiceWithUrlParameterIntTyped([null]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterIntTyped null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded integer but was null/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterIntTyped([false]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterIntTyped false did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded integer but was false/', $e->getMessage());
        }

        $this->assertSame(0, (new ServiceWithUrlParameterIntTyped([0]))->run());
        $this->assertSame(0, (new ServiceWithUrlParameterIntTyped(['0']))->run());
        $this->assertSame(1234, (new ServiceWithUrlParameterIntTyped([1234]))->run());
        $this->assertSame(1234, (new ServiceWithUrlParameterIntTyped(['1234']))->run());

        try {
            (new ServiceWithUrlParameterIntTyped([1234.890]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterIntTyped 1234.890 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded integer but was 1234.89/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterIntTyped(['1234.890']))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterIntTyped "1234.890" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded integer but was 1234.89/', $e->getMessage());
        }

        $this->assertSame(-250, (new ServiceWithUrlParameterIntTyped([-250]))->run());
        $this->assertSame(-25012, (new ServiceWithUrlParameterIntTyped(['-25012']))->run());

        try {
            (new ServiceWithUrlParameterIntTyped([-25012.792]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterIntTyped -25012.792 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded integer but was -25012.792/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterIntTyped(['-25012.792']))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterIntTyped -25012.792 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded integer but was -25012.792/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterIntTyped(['rawstring']))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterIntTyped rawstring did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded integer but was rawstring/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterIntTyped([[1,2,3]]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterIntTyped [1,2,3] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded integer but was .1.2.3./', $e->getMessage());
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
        $this->assertSame(-25012, (new ServiceWithPostParameterNumberTyped([], ['a' => '-25012']))->run());
        $this->assertSame(-25012.792, (new ServiceWithPostParameterNumberTyped([], ['a' => -25012.792]))->run());
        $this->assertSame(-25012.792, (new ServiceWithPostParameterNumberTyped([], ['a' => '-25012.792']))->run());

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

        // NUMBER typed get parameter
        try {
            (new ServiceWithUrlParameterNumberTyped([null]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterNumberTyped null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded number but was null/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterNumberTyped([false]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterNumberTyped false did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded number but was false/', $e->getMessage());
        }

        $this->assertSame(0, (new ServiceWithUrlParameterNumberTyped([0]))->run());
        $this->assertSame(0, (new ServiceWithUrlParameterNumberTyped(['0']))->run());
        $this->assertSame(1234, (new ServiceWithUrlParameterNumberTyped([1234]))->run());
        $this->assertSame(1234, (new ServiceWithUrlParameterNumberTyped(['1234']))->run());
        $this->assertSame(1234.89, (new ServiceWithUrlParameterNumberTyped([1234.890]))->run());
        $this->assertSame(1234.89, (new ServiceWithUrlParameterNumberTyped(['1234.890']))->run());
        $this->assertSame(-250, (new ServiceWithUrlParameterNumberTyped([-250]))->run());
        $this->assertSame(-25012, (new ServiceWithUrlParameterNumberTyped(['-25012']))->run());
        $this->assertSame(-25012.792, (new ServiceWithUrlParameterNumberTyped([-25012.792]))->run());
        $this->assertSame(-25012.792, (new ServiceWithUrlParameterNumberTyped(['-25012.792']))->run());

        try {
            (new ServiceWithUrlParameterNumberTyped(['rawstring']))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterNumberTyped rawstring did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded number but was rawstring/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterNumberTyped([[1,2,3]]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterNumberTyped [1,2,3] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded number but was .1.2.3./', $e->getMessage());
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

        // STRING typed get parameter
        try {
            (new ServiceWithUrlParameterStringTyped([null]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterStringTyped null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded string but was null/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterStringTyped([false]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterStringTyped false did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded string but was false/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterStringTyped([0]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterStringTyped 0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded string but was 0/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterStringTyped(['0']))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterStringTyped "0" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded string but was 0/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterStringTyped(['rawstring']))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterStringTyped "rawstring" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded string but was rawstring/', $e->getMessage());
        }

        $this->assertSame('jsonencodedstring', (new ServiceWithUrlParameterStringTyped(['"jsonencodedstring"']))->run());
        $this->assertSame('', (new ServiceWithUrlParameterStringTyped(['""']))->run());
        $this->assertSame('1234', (new ServiceWithUrlParameterStringTyped(['"1234"']))->run());
        $this->assertSame('[1,2,3,4]', (new ServiceWithUrlParameterStringTyped(['"[1,2,3,4]"']))->run());

        try {
            (new ServiceWithUrlParameterStringTyped([[1,2,3]]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterStringTyped [1,2,3] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded string but was .1.2.3./', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterStringTyped([["a" => 1, "b" => 2]]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterStringTyped ["a" => 1, "b" => 2] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded string but was ..a..1..b..2./', $e->getMessage());
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

        // ARRAY typed get parameter
        try {
            (new ServiceWithUrlParameterArrayTyped([null]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterArrayTyped null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded array but was null/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterArrayTyped([false]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterArrayTyped false did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded array but was false/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterArrayTyped([0]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterArrayTyped 0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded array but was 0/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterArrayTyped(['rawstring']))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterArrayTyped "rawstring" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded array but was rawstring/', $e->getMessage());
        }

        $this->assertSame([], (new ServiceWithUrlParameterArrayTyped([[]]))->run());
        $this->assertSame([1,2,3], (new ServiceWithUrlParameterArrayTyped([[1,2,3]]))->run());

        try {
            (new ServiceWithUrlParameterArrayTyped([["a" => 1, "b" => 2]]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterArrayTyped ["a" => 1, "b" => 2]] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded array but was ."a".1."b".2./', $e->getMessage());
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

        // OBJECT typed post parameter
        try {
            (new ServiceWithUrlParameterObjectTyped([null]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterObjectTyped null did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded object but was null/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterObjectTyped([false]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterObjectTyped false did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded object but was false/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterObjectTyped([0]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterObjectTyped 0 did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded object but was 0/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterObjectTyped(['rawstring']))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterObjectTyped "rawstring" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded object but was rawstring/', $e->getMessage());
        }

        try {
            (new ServiceWithUrlParameterObjectTyped([[1,2,3]]))->run();
            $this->exceptionMessage = 'ServiceWithUrlParameterObjectTyped [1,2,3] did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Expected URL param 0 to be a json encoded object but was .1,2,3./', $e->getMessage());
        }

        $this->assertEquals((object) ['a' => 1], (new ServiceWithUrlParameterObjectTyped([["a" => 1]]))->run());
        $this->assertEquals((object) ['a' => 1, 'b' => 2], (new ServiceWithUrlParameterObjectTyped([["a" => 1, "b" => 2]]))->run());
        $this->assertEquals((object) ['a' => 1, 'b' => 2], (new ServiceWithUrlParameterObjectTyped([(object) ['a' => 1, 'b' => 2]]))->run());
        $this->assertEquals((object) ['a' => 1, 'b' => 2], (new ServiceWithUrlParameterObjectTyped(['{"a":1,"b":2}']))->run());

        // Test wrong values
        // Test exceptions
        try {
            (new ServiceWithInvalidUrlParameter([], []))->run();
            $this->exceptionMessage = 'ServiceWithInvalidUrlParameter did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/enabledUrlParams must be an int or an array of arrays/', $e->getMessage());
        }

        try {
            (new ServiceWithInvalidPostParameter([], []))->run();
            $this->exceptionMessage = 'ServiceWithInvalidPostParameter did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/enabledPostParams must be an array of arrays/', $e->getMessage());
        }

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
            $this->assertRegExp('/Each enabled POST parameter must be a string or an array with min 1 and max 5 elements/', $e->getMessage());
        }

        try {
            (new ServiceWithInvalidUrlParameterArrayLen([], []))->run();
            $this->exceptionMessage = 'ServiceWithInvalidUrlParameterArrayLen did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Each enabled URL parameter must be an array with min 0 and max 3 elements/', $e->getMessage());
        }

        try {
            (new ServiceWithInvalidPostParameterType([], []))->run();
            $this->exceptionMessage = 'ServiceWithInvalidPostParameterType did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/POST param .a. element.1. .invalid-type-here. must be WebServiceManager..NOT_TYPED, WebServiceManager..BOOL, WebServiceManager..INT, WebServiceManager..NUMBER, WebServiceManager..STRING, WebServiceManager..ARRAY or WebServiceManager..OBJECT/', $e->getMessage());
        }

        try {
            (new ServiceWithInvalidUrlParameterType([], []))->run();
            $this->exceptionMessage = 'ServiceWithInvalidUrlParameterType did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/URL param .0. element.0. .invalid-type-here. must be WebServiceManager..NOT_TYPED, WebServiceManager..BOOL, WebServiceManager..INT, WebServiceManager..NUMBER, WebServiceManager..STRING, WebServiceManager..ARRAY or WebServiceManager..OBJECT/', $e->getMessage());
        }

        try {
            (new ServiceWithInvalidPostParameterRequiredValue([], []))->run();
            $this->exceptionMessage = 'ServiceWithInvalidPostParameterRequiredValue did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/POST param .a. element.2. .ARRAY. must be WebServiceManager..REQUIRED or WebServiceManager..NOT_REQUIRED/', $e->getMessage());
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
            $this->assertRegExp('/POST param .a. element.3. .BOOL. must be WebServiceManager..NOT_RESTRICTED or an array of values/', $e->getMessage());
        }

        try {
            (new ServiceWithInvalidUrlParameterRestrictedValue([], []))->run();
            $this->exceptionMessage = 'ServiceWithInvalidUrlParameterRestrictedValue did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/URL param .0. element.1. .BOOL. must be WebServiceManager..NOT_RESTRICTED or an array of values/', $e->getMessage());
        }
    }


    /**
     * testRun
     *
     * @return void
     */
    public function testRun(){

        // Test empty values
        // Not necessary

        // Test ok values
        $this->assertSame(['1',[1,2],'string'], (new ServiceWithUrlParams3Mandatory([1,[1,2],'"string"']))->run());
        $this->assertSame(['1',[1,2],'string'], (new ServiceWithUrlParams3Mandatory([1,'[1,2]','"string"']))->run());
        $this->assertSame(['5000',[1,2],'string'], (new ServiceWithUrlParams3Mandatory([5000,'[1,2]','"string"']))->run());
        $this->assertSame(['raw',[1,2],'string'], (new ServiceWithUrlParams3Mandatory(['raw','[1,2]','"string"']))->run());

        $this->assertSame(['1','2','3','default3',true], (new ServiceWithUrlParams5LastNotMandatory([1,2,3]))->run());
        $this->assertSame(['1','string','3','default3',true], (new ServiceWithUrlParams5LastNotMandatory([1,'string',3]))->run());
        $this->assertSame(['1','2','3','500',true], (new ServiceWithUrlParams5LastNotMandatory([1,2,3,500]))->run());
        $this->assertSame(['1','2','3','500',false], (new ServiceWithUrlParams5LastNotMandatory([1,2,3,500,false]))->run());
        $this->assertSame(['1','2','3','500',true], (new ServiceWithUrlParams5LastNotMandatory([1,2,3,500,'true']))->run());

        // Test wrong values
        AssertUtils::throwsException(function() {(new ServiceWithUrlParams3Mandatory([1,[1,2],'string']))->run();}, '/Expected URL param 2 to be a json encoded string but was string/');
        AssertUtils::throwsException(function() {(new ServiceWithUrlParams3Mandatory([1,[1,2]]))->run();}, '/Missing mandatory URL parameter at 2/');
        AssertUtils::throwsException(function() {(new ServiceWithUrlParams3Mandatory([1]))->run();}, '/Missing mandatory URL parameter at 1/');
        AssertUtils::throwsException(function() {(new ServiceWithUrlParams5LastNotMandatory([1,2,3,500,'1']))->run();}, '/Expected URL param 4 to be a json encoded boolean but was 1/');
        AssertUtils::throwsException(function() {(new ServiceWithUrlParams3IncorrectMandatory([]))->run();}, '/All URL parameters must have a default value after the non mandatory defined at 1/');
        AssertUtils::throwsException(function() {(new ServiceWithInvalidTypeDefaultUrlParamValue(['hello', 'world']))->run();}, '/Expected URL param 2 to be a json encoded integer but was "string"/');
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
            $this->assertRegExp('/must be of the type int(eger)?, null given/', $e->getMessage());
        }

        try {
            $sut->generateError('', '');
            $this->exceptionMessage = '"" did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/must be of the type int(eger)?, string given/', $e->getMessage());
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
        $this->assertFalse(StringUtils::isEmpty($error->trace));

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


// TODO - Implement tests for restricted post and get parameter values: When a parameter is defined with restricted possible values, giving it
// a value that is not on the list must throw exception. Implement also different types of value restrictions: per numeric range, per list of values, etc

?>