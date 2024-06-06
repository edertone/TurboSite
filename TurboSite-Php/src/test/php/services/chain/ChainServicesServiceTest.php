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

use PHPUnit\Framework\TestCase;
use stdClass;
use org\turbocommons\src\main\php\utils\ArrayUtils;
use org\turbosite\src\main\php\services\chain\ChainServicesService;
use org\turbotesting\src\main\php\utils\AssertUtils;


/**
 * ChainServicesServiceTest
 *
 * @return void
 */
class ChainServicesServiceTest extends TestCase {


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(){
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){
    }


    /**
     * testConstruct
     *
     * @return void
     */
    public function testConstruct(){

        // Test empty values
        AssertUtils::throwsException(function() { new ChainServicesService(); }, '/Missing mandatory POST parameter: services/');
        AssertUtils::throwsException(function() { new ChainServicesService(null, null); }, '/Missing mandatory POST parameter: services/');
        AssertUtils::throwsException(function() { new ChainServicesService('', ''); }, '/must be of the type array or null, string given/');
        AssertUtils::throwsException(function() { new ChainServicesService([], []); }, '/Missing mandatory POST parameter: services/');

        // Test ok values
        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\resources\model\webservice\ServiceWithoutParams';

        $this->assertSame('application/json', (new ChainServicesService([], ['services' => [$service]]))->contentType);
        $this->assertSame('application/json', (new ChainServicesService([], ['services' => [$service, $service]]))->contentType);

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { new ChainServicesService([''], ['services' => '']); }, '/Unexpected URL parameter received at 0/');
        AssertUtils::throwsException(function() { new ChainServicesService([], 'string'); }, '/Argument 2.*must be of the type array or null.*string given/');

        $service = new stdClass();
        $service->class = '';

        AssertUtils::throwsException(function() use ($service) {new ChainServicesService([], ['services' => [$service]]); },
            '/A namespace \+ class or an uri is mandatory to locate the service to execute/');

        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\resources\model\nonexistantPath\NonExistantClassName';

        AssertUtils::throwsException(function() use ($service) { new ChainServicesService([], ['services' => [$service]]); }, '/Provided class does not exist: org.*NonExistantClassName/');
    }


    /**
     * testRun_no_services_passed
     *
     * @return void
     */
    public function testRun_no_services_passed(){

        // Test empty values
        AssertUtils::throwsException(function() { new ChainServicesService([], ['services' => '']); }, '/Expected services POST param to be a json encoded array but was/');

        $servicesResult = (new ChainServicesService([], ['services' => []]))->run();
        $this->assertTrue(ArrayUtils::isArray($servicesResult));
        $this->assertSame(0, count($servicesResult));

        // Test exceptions
        // Test wrong values
        AssertUtils::throwsException(function() { new ChainServicesService(null, null); }, '/Missing mandatory POST parameter: services/');
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
        AssertUtils::throwsException(function() use ($service) { (new ChainServicesService([], ['services' => [$service]]))->run(); },
            '/A namespace \+ class or an uri is mandatory to locate the service to execute/');

        // Test ok values

        // Simple service without parameters
        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\resources\model\webservice\ServiceWithoutParams';
        $servicesResult = (new ChainServicesService([], ['services' => [$service]]))->run();
        $this->assertTrue(ArrayUtils::isArray($servicesResult));
        $this->assertSame(1, count($servicesResult));
        $this->assertSame('no params received', $servicesResult[0]);

        // Service with get and post parameters, where post parameters are passed as an associative array
        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\resources\model\webservice\ServiceWithUrlandPostParams';
        $service->urlParameters = ['1', '2'];
        $service->postParameters = ['a' => 1, 'b' => '2'];
        $servicesResult = (new ChainServicesService([], ['services' => [$service]]))->run();
        $this->assertTrue(ArrayUtils::isArray($servicesResult));
        $this->assertSame(1, count($servicesResult));
        $this->assertSame(["0" => '1', "1" => '2', "a" => '1', "b" => '2'], $servicesResult[0]);

        // Service with get and post parameters, where post parameters are passed as an stdclass object
        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\resources\model\webservice\ServiceWithUrlandPostParams';
        $service->urlParameters = ['1', '2'];
        $service->postParameters = new stdClass();
        $service->postParameters->a = 1;
        $service->postParameters->b = '2';
        $servicesResult = (new ChainServicesService([], ['services' => [$service]]))->run();
        $this->assertTrue(ArrayUtils::isArray($servicesResult));
        $this->assertSame(1, count($servicesResult));
        $this->assertSame(["0" => '1', "1" => '2', "a" => '1', "b" => '2'], $servicesResult[0]);

        // Test wrong values
        // Test exceptions
        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\resources\model\webservice\ServiceWithUrlandPostParams';

        AssertUtils::throwsException(function() use ($service) { (new ChainServicesService([], ['services' => [$service]]))->run(); }, '/Missing mandatory URL parameter at 0/');

        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\resources\model\webservice\ServiceWithUrlandPostParams';
        $service->urlParameters = ['1', '2'];
        $service->postParameters = [];

        AssertUtils::throwsException(function() use ($service) { (new ChainServicesService([], ['services' => [$service]]))->run(); }, '/Missing mandatory POST parameter: a/');
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
        $service1->class = 'org\turbosite\src\test\resources\model\webservice\ServiceWithoutParams';

        $service2 = new stdClass();
        $service2->class = 'org\turbosite\src\test\resources\model\webservice\ServiceWithUrlandPostParams';
        $service2->urlParameters = ['1', '2'];
        $service2->postParameters = ['a' => 1, 'b' => '2'];

        $service3 = new stdClass();
        $service3->class = 'org\turbosite\src\test\resources\model\webservice\ServiceWithoutParams';

        $servicesResult = (new ChainServicesService([], ['services' => [$service1, $service2, $service3]]))->run();
        $this->assertTrue(ArrayUtils::isArray($servicesResult));
        $this->assertSame(3, count($servicesResult));
        $this->assertSame('no params received', $servicesResult[0]);
        $this->assertSame(["0" => '1', "1" => '2', "a" => '1', "b" => '2'], $servicesResult[1]);
        $this->assertSame('no params received', $servicesResult[2]);

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() use ($service1, $service3) { (new ChainServicesService([], ['services' => [$service1, '', $service3]])); },
            '/Each service must be defined as a php stdClass.*but was /');

        AssertUtils::throwsException(function() use ($service1, $service3) { (new ChainServicesService([], ['services' => [$service1, 123, $service3]])); },
            '/Each service must be defined as a php stdClass.*but was 123/');

        AssertUtils::throwsException(function() use ($service1, $service3) { (new ChainServicesService([], ['services' => [$service1, 'string', $service3]])); },
            '/Each service must be defined as a php stdClass.*but was string/');
    }


    /**
     * testRun_must_fail_if_class_and_uri_are_specified
     *
     * @return void
     */
    public function testRun_must_fail_if_class_and_uri_are_specified(){

        $service = new stdClass();
        $service->class = 'org\turbosite\src\test\resources\model\webservice\ServiceWithoutParams';
        $service->uri = 'api/site/example/example-service-without-params';

        $this->assertNull(AssertUtils::throwsException(function() use ($service) { (new ChainServicesService([], ['services' => [$service]]))->run(); },
            '/Services can only be defined by class or uri, not both/'));
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

        AssertUtils::throwsException(function() use ($service) { (new ChainServicesService([], ['services' => [$service]]))->run(); },
            '/A namespace \+ class or an uri is mandatory to locate the service to execute/');

        // Test ok values
        // Ok values can only be tested when calling ChainServicesService service via http request

        // Test wrong values
        // Test exceptions
        $service = new stdClass();
        $service->uri = 'api/site/example/example-service-without-params';

        $this->assertNull(AssertUtils::throwsException(function() use ($service) { (new ChainServicesService([], ['services' => [$service]]))->run(); },
            '/ChainServicesService uri can only be defined when called via http request/'));
    }
}

?>