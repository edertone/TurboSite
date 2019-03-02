<?php

/**
 * TurboSite is a web site creation framework.
 *
 * Website : -> http://www.turboframework.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2018 Edertone Advanded Solutions. http://www.edertone.com
 */

namespace org\turbocommons\src\test\php\managers;

use PHPUnit\Framework\TestCase;
use org\turbosite\src\main\php\managers\BlogMarkDownManager;
use org\turbodepot\src\main\php\managers\FilesManager;


/**
 * BlogMarkDownManagerTest
 *
 * @return void
 */
class BlogMarkDownManagerTest extends TestCase {


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

        $this->filesManager = new FilesManager();
        $this->tempFolder = $this->filesManager->createTempDirectory('TurboSitePhp-BlogMarkDownManagerTest');
        $this->sut = new BlogMarkDownManager($this->tempFolder);
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
     * testConstruct
     *
     * @return void
     */
    public function testConstruct(){

        $this->assertTrue($this->sut instanceof BlogMarkDownManager);
    }


    /**
     * testGetPost
     *
     * @return void
     */
    public function testGetPost(){

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
     * testGetLatestPosts
     *
     * @return void
     */
    public function testGetLatestPosts(){

        $this->filesManager->copyDirectory(__DIR__.'/../resources/managers/blogmarkdownmanager', $this->tempFolder);

        // Test empty values
        // TODO

        // Test ok values
        $latestPosts = $this->sut->getLatestPosts('en', 10);

        $this->assertSame(4, count($latestPosts));

        $this->assertSame('Convert string to CamelCase, UpperCamelCase or LowerCamelCase in Javascript, typescript and Php',
            $latestPosts[0]->title);

        $this->assertSame('Blog post test 2', $latestPosts[1]->title);

        $this->assertSame('Blog post test 1', $latestPosts[2]->title);

        $this->assertSame('Blog post test 18/9/2014', $latestPosts[3]->title);
        // TODO - more tests

        // Test wrong values
        // TODO

        // Test exceptions
        // TODO
    }
}

?>