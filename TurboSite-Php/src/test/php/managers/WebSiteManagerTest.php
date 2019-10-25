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
use org\turbosite\src\main\php\managers\GlobalErrorManager;
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

        // Disable error manager warnings to prevent test errors
        GlobalErrorManager::getInstance()->tooMuchMemoryWarning = 0;
        GlobalErrorManager::getInstance()->tooMuchTimeWarning = 0;

        // Clone the $_GET object so it can be restored after the test
        // TODO - use ObjectUtils::clone() to do this instead of assigning it directly (note that arrays in php are copied by assignation)
        $this->_GETBackup = $_GET;
    }


    /**
     * @see TestCase::tearDown()
     *
     * @return void
     */
    protected function tearDown(){

        $this->filesManager->deleteDirectory($this->tempFolder);

        $_GET = $this->_GETBackup;
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
     * testGetDepotManager
     *
     * @return void
     */
    public function testGetDepotManager(){

        $this->assertSame(null, $this->sut->getDepotManager());

        // TODO - implement all necessary steps to obtain a valid DepotManager instance when calling this method
        // TODO - $this->assertTrue($this->sut->getDepotManager() instanceof DepotManager);
    }


    /**
     * testGetPrimaryLanguage
     *
     * @return void
     */
    public function testGetPrimaryLanguage(){

        $this->assertSame('', $this->sut->getPrimaryLanguage());

        // TODO - Test more complex scenarios where an url with a valid language exists
    }

    // TODO - add all pending tests
}

?>