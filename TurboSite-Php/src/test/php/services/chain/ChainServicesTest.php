<?php

/**
 * TurboSite is a web site creation framework.
 *
 * Website : -> http://www.turboframework.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2018 Edertone Advanded Solutions. http://www.edertone.com
 */

namespace org\turbosite\src\test\php\services\chain;

use stdClass;
use PHPUnit\Framework\TestCase;
use Throwable;
use org\turbocommons\src\main\php\utils\ArrayUtils;
use org\turbosite\src\main\php\services\chain\ChainServices;


/**
 * WebServiceTest
 *
 * @return void
 */
class ChainServicesTest extends TestCase {


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
     * testConstruct
     *
     * @return void
     */
    public function testConstruct(){

        // Test empty values
        // TODO

        // Test ok values
        // TODO

        // Test wrong values
        // Test exceptions
        try {
            $service = new ChainServices([''], ['services' => '']);
            $this->exceptionMessage = print_r($service, true).' did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid number of GET parameters passed to service. Received 1 but expected 0/', $e->getMessage());
        }

        try {
            $service = new ChainServices();
            $this->exceptionMessage = print_r($service, true).' did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/This service expects POST data/', $e->getMessage());
        }

        try {
            $service = new ChainServices([], []);
            $this->exceptionMessage = print_r($service, true).' did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/This service expects POST data/', $e->getMessage());
        }

        try {
            $service = new ChainServices([], 'string');
            $this->exceptionMessage = print_r($service, true).' did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Argument 2.*must be of the type array or null.*string given/', $e->getMessage());
        }
    }


    /**
     * testRun_no_services_passed
     *
     * @return void
     */
    public function testRun_no_services_passed(){

        // Test empty values
        $servicesResult = (new ChainServices([], ['services' => '']))->run();
        $this->assertTrue(ArrayUtils::isArray($servicesResult));
        $this->assertSame(0, count($servicesResult));

        $servicesResult = (new ChainServices([], ['services' => []]))->run();
        $this->assertTrue(ArrayUtils::isArray($servicesResult));
        $this->assertSame(0, count($servicesResult));

        // Test exceptions
        // Test wrong values
        try {
            $service = new ChainServices(null, null);
            $this->exceptionMessage = print_r($service, true).' did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/This service expects POST data/', $e->getMessage());
        }
    }


    /**
     * testRun_single_service_passed_by_class
     *
     * @return void
     */
    public function testRun_single_service_passed_by_class(){

        // Test empty values
        $service = new stdClass();
        $service->class = '';
        try {
            $servicesResult = (new ChainServices([], ['services' => [$service]]))->run();
            $this->exceptionMessage = '$services did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/A namespace \+ class or an uri is mandatory to locate the service to execute/', $e->getMessage());
        }

        // Test ok values

        // Simple service without parameters
        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\php\resources\model\webservice\ServiceWithoutParams';
        $servicesResult = (new ChainServices([], ['services' => [$service]]))->run();
        $this->assertTrue(ArrayUtils::isArray($servicesResult));
        $this->assertSame(1, count($servicesResult));
        $this->assertSame('no params received', $servicesResult[0]);

        // Service with get and post parameters, where post parameters are passed as an associative array
        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\php\resources\model\webservice\ServiceWithGETandPostParams';
        $service->getParameters = ['1', '2'];
        $service->postParameters = ['a' => 1, 'b' => '2'];
        $servicesResult = (new ChainServices([], ['services' => [$service]]))->run();
        $this->assertTrue(ArrayUtils::isArray($servicesResult));
        $this->assertSame(1, count($servicesResult));
        $this->assertSame(["0" => '1', "1" => '2', "a" => '1', "b" => '2'], $servicesResult[0]);

        // Service with get and post parameters, where post parameters are passed as an stdclass object
        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\php\resources\model\webservice\ServiceWithGETandPostParams';
        $service->getParameters = ['1', '2'];
        $service->postParameters = new stdClass();
        $service->postParameters->a = 1;
        $service->postParameters->b = '2';
        $servicesResult = (new ChainServices([], ['services' => [$service]]))->run();
        $this->assertTrue(ArrayUtils::isArray($servicesResult));
        $this->assertSame(1, count($servicesResult));
        $this->assertSame(["0" => '1', "1" => '2', "a" => '1', "b" => '2'], $servicesResult[0]);

        // Test wrong values
        // Test exceptions
        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\php\resources\model\webservice\ServiceWithGETandPostParams';
        try {
            $servicesResult = (new ChainServices([], ['services' => [$service]]))->run();
            $this->exceptionMessage = '$services did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Invalid number of GET parameters passed to service. Received 0 but expected 2/', $e->getMessage());
        }

        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\php\resources\model\webservice\ServiceWithGETandPostParams';
        $service->getParameters = ['1', '2'];
        $service->postParameters = [];
        try {
            $servicesResult = (new ChainServices([], ['services' => [$service]]))->run();
            $this->exceptionMessage = '$services did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/This service expects POST data/', $e->getMessage());
        }
    }


    /**
     * testRun_multiple_services_passed_by_class
     *
     * @return void
     */
    public function testRun_multiple_services_passed_by_class(){

        // Test empty values
        // Not necessary

        // Test ok values

        // A simple service without parameters, then a service with get and post parameters and the service without params again
        $service1 = new stdClass();
        $service1->class = 'org\turbosite\src\test\php\resources\model\webservice\ServiceWithoutParams';

        $service2 = new stdClass();
        $service2->class = 'org\turbosite\src\test\php\resources\model\webservice\ServiceWithGETandPostParams';
        $service2->getParameters = ['1', '2'];
        $service2->postParameters = ['a' => 1, 'b' => '2'];

        $service3 = new stdClass();
        $service3->class = 'org\turbosite\src\test\php\resources\model\webservice\ServiceWithoutParams';

        $servicesResult = (new ChainServices([], ['services' => [$service1, $service2, $service3]]))->run();
        $this->assertTrue(ArrayUtils::isArray($servicesResult));
        $this->assertSame(3, count($servicesResult));
        $this->assertSame('no params received', $servicesResult[0]);
        $this->assertSame(["0" => '1', "1" => '2', "a" => '1', "b" => '2'], $servicesResult[1]);
        $this->assertSame('no params received', $servicesResult[2]);

        // Test wrong values
        // TODO

        // Test exceptions
        // TODO

        $this->markTestIncomplete('This test has not been implemented yet.');
    }


    /**
     * testRun_must_fail_if_class_and_uri_are_specified
     *
     * @return void
     */
    public function testRun_must_fail_if_class_and_uri_are_specified(){

        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\php\resources\model\webservice\ServiceWithoutParams';
        $service->uri = 'api/site/example/example-service-without-params';
        try {
            $servicesResult = (new ChainServices([], ['services' => [$service]]))->run();
            $this->exceptionMessage = print_r($servicesResult, true).' did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/Services can only be defined by class or uri, not both/', $e->getMessage());
        }
    }


    /**
     * testRun_must_fail_when_service_passed_by_uri_and_not_http_request
     *
     * @return void
     */
    public function testRun_must_fail_when_service_passed_by_uri_and_not_http_request(){

        // Test empty values
        $service = new stdClass();
        $service->uri = '';
        try {
            $servicesResult = (new ChainServices([], ['services' => [$service]]))->run();
            $this->exceptionMessage = print_r($servicesResult, true).' did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/A namespace \+ class or an uri is mandatory to locate the service to execute/', $e->getMessage());
        }

        // Test ok values
        // Ok values can only be tested when calling ChainServices service via http request

        // Test wrong values
        // Test exceptions
        $service = new stdClass();
        $service->uri = 'api/site/example/example-service-without-params';
        try {
            $servicesResult = (new ChainServices([], ['services' => [$service]]))->run();
            $this->exceptionMessage = '$servicesResult did not cause exception';
        } catch (Throwable $e) {
            $this->assertRegExp('/ChainServices can only be executed when called via http request/', $e->getMessage());
        }
    }
}

?>