<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\creator;
use bovigo\callmap\NewInstance;
use org\bovigo\vfs\vfsStream;
use stubbles\console\Console;
use stubbles\values\ResourceLoader;
use stubbles\values\Rootpath;

use function bovigo\assert\assert;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\callmap\verify;
/**
 * Test for stubbles\console\creator\TestFileCreator.
 *
 * @group  scripts
 */
class TestFileCreatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  TestFileCreator
     */
    private $testFileCreator;
    /**
     * mocked console
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $console;
    /**
     * root directory
     *
     * @type  Rootpath
     */
    private $rootpath;
    /**
     * @type  \org\bovigo\vfs\vfsDirectory
     */
    private $root;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->root            = vfsStream::setup();
        $this->rootpath        = new Rootpath($this->root->url());
        $this->console         = NewInstance::stub(Console::class);
        $this->testFileCreator = new TestFileCreator(
                $this->console,
                $this->rootpath,
                new ResourceLoader()
        );
    }

    /**
     * @test
     */
    public function createsTestIfDoesNotExist()
    {
        $this->testFileCreator->create('example\console\ExampleConsoleApp');
        assertTrue(file_exists(
                $this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php')
        ));
    }

    /**
     * @test
     */
    public function createsTestWithCorrectContents()
    {
        $this->testFileCreator->create('example\console\ExampleConsoleApp');
        assert(
                file_get_contents($this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php')),
                equals('<?php
/**
 * Your license or something other here.
 *
 * @package  example\console
 */
namespace example\console;
use stubbles\values\Rootpath;

use function stubbles\reflect\annotationsOfConstructor;
/**
 * Test for example\console\ExampleConsoleApp.
 */
class ExampleConsoleAppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ExampleConsoleApp
     */
    private $instance;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->instance = new ExampleConsoleApp();
    }

    /**
     * @test
     */
    public function returnsExitCode0()
    {
        $this->assertEquals(0, $this->instance->run());
    }

    /**
     * @test
     */
    public function canCreateInstance()
    {
        $this->assertInstanceOf(
                \'example\console\ExampleConsoleApp\',
                ExampleConsoleApp::create(new Rootpath())
        );
    }
}
')
        );
        verify($this->console, 'writeLine')
                ->received('Test for example\console\ExampleConsoleApp created at ' . $this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php'));
    }

    /**
     * @test
     */
    public function skipsTestCreationIfTestAlreadyExists()
    {
        mkdir($this->rootpath->to('src/test/php/example/console'), 0755, true);
        file_put_contents($this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php'), 'foo');
        $this->testFileCreator->create('example\console\ExampleConsoleApp');
        assert(
                file_get_contents(
                        $this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php')
                ),
                equals('foo')
         );
        verify($this->console, 'writeLine')
                ->received('Test for example\console\ExampleConsoleApp already exists, skipped creating the test');
    }

    /**
     * @test
     * @expectedException  UnexpectedValueException
     * @since  4.1.0
     * @group  issue_49
     */
    public function throwsConfigurationExceptionWhenNoPsr4PathDefinedForNamespace()
    {
        vfsStream::newFile('composer.json')
                 ->withContent('{"autoload": { "psr-4": { "stubbles\\\foo\\\": "src/main/php" } }}')
                 ->at($this->root);;
        $this->testFileCreator->create('example\console\ExampleConsoleApp');
    }

    /**
     * @test
     * @since  4.1.0
     * @group  issue_49
     */
    public function createsTestInPsr4PathIfDoesNotExist()
    {
        vfsStream::newFile('composer.json')
                 ->withContent('{"autoload": { "psr-4": { "example\\\console\\\": "src/main/php" } }}')
                 ->at($this->root);;
        $this->testFileCreator->create('example\console\ExampleConsoleApp');
        assertTrue(file_exists($this->rootpath->to('src/test/php/ExampleConsoleAppTest.php')));
    }

    /**
     * @test
     * @since  4.1.0
     * @group  issue_49
     */
    public function createsTestInPsr4PathWithCorrectContents()
    {
        vfsStream::newFile('composer.json')
                 ->withContent('{"autoload": { "psr-4": { "example\\\console\\\": "src/main/php" } }}')
                 ->at($this->root);;
        $this->testFileCreator->create('example\console\ExampleConsoleApp');
        assert(
                file_get_contents($this->rootpath->to('src/test/php/ExampleConsoleAppTest.php')),
                equals('<?php
/**
 * Your license or something other here.
 *
 * @package  example\console
 */
namespace example\console;
use stubbles\values\Rootpath;

use function stubbles\reflect\annotationsOfConstructor;
/**
 * Test for example\console\ExampleConsoleApp.
 */
class ExampleConsoleAppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ExampleConsoleApp
     */
    private $instance;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->instance = new ExampleConsoleApp();
    }

    /**
     * @test
     */
    public function returnsExitCode0()
    {
        $this->assertEquals(0, $this->instance->run());
    }

    /**
     * @test
     */
    public function canCreateInstance()
    {
        $this->assertInstanceOf(
                \'example\console\ExampleConsoleApp\',
                ExampleConsoleApp::create(new Rootpath())
        );
    }
}
')
        );
    }
}
