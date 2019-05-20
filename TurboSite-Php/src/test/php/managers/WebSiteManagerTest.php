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

use PHPUnit\Framework\TestCase;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbosite\src\main\php\managers\WebSiteManager;


/**
 * WebSiteManagerTest
 *
 * @return void
 */
class WebSiteManagerTest extends TestCase {


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

        $this->filesManager = new FilesManager();
        $this->tempFolder = $this->filesManager->createTempDirectory('TurboSitePhp-WebSiteManagerTest');
        $this->sut = WebSiteManager::getInstance();
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){

        $this->filesManager->deleteDirectory($this->tempFolder);
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
     * testGetInstance
     *
     * @return void
     */
    public function testGetInstance(){

        $this->assertTrue($this->sut instanceof WebSiteManager);
    }


    /**
     * testGetPrimaryLanguage
     *
     * @return void
     */
    public function testGetPrimaryLanguage(){

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
}

?>