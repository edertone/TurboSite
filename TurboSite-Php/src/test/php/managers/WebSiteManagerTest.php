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

use stdClass;
use ReflectionObject;
use PHPUnit\Framework\TestCase;
use org\turbodepot\src\main\php\managers\FilesManager;
use org\turbosite\src\main\php\managers\GlobalErrorManager;
use org\turbosite\src\main\php\managers\WebSiteManager;
use org\turbotesting\src\main\php\utils\AssertUtils;
use org\turbodepot\src\main\php\managers\DepotManager;


/**
 * WebSiteManagerTest
 *
 * @return void
 */
class WebSiteManagerTest extends TestCase {


    /**
     * Auxiliary method to mock the depot manager class inside the websitemanager class
     *
     * @param WebSiteManager $webSiteManager
     */
    public static function mockDepotManager(WebSiteManager $webSiteManager){

        // Mock all the values that are required to call the tested method
        $depotSetup = new stdClass();
        $depotSetup->{'$schema'} = 'mockschema';
        $depotSetup->depots = [new stdClass()];

        $depotSetup->depots[0]->users = new stdClass();
        $depotSetup->depots[0]->users->prefix = 'usr_';
        $depotSetup->depots[0]->users->source = 'db';

        $reflectionObject = new ReflectionObject($webSiteManager);
        $reflectionProperty = $reflectionObject->getProperty('_depotManager');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($webSiteManager, new DepotManager($depotSetup));

        return $webSiteManager;
    }


    /**
     * @see TestCase::setUp()
     *
     * @return void
     */
    protected function setUp(){

        $this->filesManager = new FilesManager();
        $this->tempFolder = $this->filesManager->createTempDirectory('TurboSitePhp-WebSiteManagerTest');

        // Note that we create it as an instance instead of a singleton cause we want to use a new one for each test
        $this->sut = new WebSiteManager();

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
     * test
     *
     * @return void
     */
    public function testGetInstance(){

        $this->assertTrue($this->sut instanceof WebSiteManager);
    }


    /**
     * test
     *
     * @return void
     */
    public function testGetDepotManager(){

        $this->assertSame(null, $this->sut->getDepotManager());
        $this->assertFalse($this->sut->getDepotManager() instanceof DepotManager);

        self::mockDepotManager($this->sut);

        $this->assertTrue($this->sut->getDepotManager() instanceof DepotManager);
        $this->assertTrue($this->sut->getDepotManager()->getFilesManager() instanceof FilesManager);
    }


    /**
     * test
     *
     * @return void
     */
    public function testGetPrimaryLanguage(){

        $this->assertSame('', $this->sut->getPrimaryLanguage());

        // TODO - Test more complex scenarios where an url with a valid language exists
    }


    /**
     * test
     *
     * @return void
     */
    public function testEchoHtmlFromMarkDownFile(){

        self::mockDepotManager($this->sut);

        // Capture the echo output from the method and test it is correct
        ob_start();

        $this->sut->echoHtmlFromMarkDownFile(__DIR__.'/../../resources/managers/webSiteManager/only-title.md');

        $this->assertSame('<h1>this is a title</h1>', ob_get_clean());

        // Invalid path
        AssertUtils::throwsException(function() { $this->sut->echoHtmlFromMarkDownFile(__DIR__.'/../../resources/managers/nonexistant.md'); }, '/File does not exist.*nonexistant.md/');

    }

    // TODO - add all pending tests
}

?>