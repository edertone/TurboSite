<?php

/**
 * TurboSite is a web site creation framework.
 *
 * Website : -> http://www.turboframework.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2018 Edertone Advanded Solutions. http://www.edertone.com
 */

namespace org\turbosite\src\test\php\services\users;

use PHPUnit\Framework\TestCase;
use stdClass;
use org\turbotesting\src\main\php\utils\AssertUtils;
use org\turbosite\src\main\php\services\users\Login;
use org\turbosite\src\main\php\managers\WebSiteManager;
use org\turbosite\src\test\php\managers\WebSiteManagerTest;
use org\turbosite\src\main\php\services\users\LoginService;


/**
 * LoginServiceTest
 *
 * @return void
 */
class LoginServiceTest extends TestCase {


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
     * test
     * @return void
     */
    public function testConstruct(){

        // Test empty values
        AssertUtils::throwsException(function() { new LoginService(); }, '/Missing mandatory POST parameter: data/');
        AssertUtils::throwsException(function() { new LoginService(null, null); }, '/Missing mandatory POST parameter: data/');
        AssertUtils::throwsException(function() { new LoginService('', ''); }, '/must be of the type array or null, string given/');
        AssertUtils::throwsException(function() { new LoginService([], []); }, '/Missing mandatory POST parameter: data/');

        // Test ok values
        $this->assertSame('application/json', (new LoginService([], ['data' => '']))->contentType);
        $this->assertSame('application/json', (new LoginService([], ['data' => 'somestring']))->contentType);

        // Test wrong values
        // Test exceptions
        AssertUtils::throwsException(function() { new LoginService([''], ['data' => '']); }, '/Unexpected URL parameter received at 0/');
        AssertUtils::throwsException(function() { new LoginService([], 'string'); }, '/Argument 2.*must be of the type array or null.*string given/');
    }


    /**
     * test
     * @return void
     */
    public function testRun_no_db_connection(){

        WebSiteManagerTest::mockDepotManager(WebSiteManager::getInstance());

        $this->assertSame('', (new LoginService([], ['data' => '']))->run());
    }
}

?>