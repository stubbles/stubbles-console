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
use stubbles\lang\ResourceLoader;
use stubbles\lang\Rootpath;
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
        $this->console         = NewInstance::stub('stubbles\console\Console');
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
        assertTrue(file_exists($this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php')));
        assertEquals(
                '<?php
/**
 * Your license or something other here.
 *
 * @package  example\console
 */
namespace example\console;
use stubbles\lang\Rootpath;
use stubbles\lang\reflect;
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
    public function annotationsPresentOnConstructor()
    {
        $this->assertTrue(
                reflect\annotationsOfConstructor($this->instance)
                        ->contain(\'Inject\')
        );
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
',
                file_get_contents($this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php'))
        );
        assertEquals(
                ['Test for example\console\ExampleConsoleApp created at ' . $this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php')],
                $this->console->argumentsReceivedFor('writeLine')
        );
    }

    /**
     * @test
     */
    public function skipsTestCreationIfTestAlreadyExists()
    {
        mkdir($this->rootpath->to('src/test/php/example/console'), 0755, true);
        file_put_contents($this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php'), 'foo');
        $this->testFileCreator->create('example\console\ExampleConsoleApp');
        assertEquals(
                'foo',
                file_get_contents(
                        $this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php')
                 )
         );
        assertEquals(
                ['Test for example\console\ExampleConsoleApp already exists, skipped creating the test'],
                $this->console->argumentsReceivedFor('writeLine')
        );
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\ConfigurationException
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
        assertEquals(
                '<?php
/**
 * Your license or something other here.
 *
 * @package  example\console
 */
namespace example\console;
use stubbles\lang\Rootpath;
use stubbles\lang\reflect;
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
    public function annotationsPresentOnConstructor()
    {
        $this->assertTrue(
                reflect\annotationsOfConstructor($this->instance)
                        ->contain(\'Inject\')
        );
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
',
                file_get_contents($this->rootpath->to('src/test/php/ExampleConsoleAppTest.php'))
        );
    }
}
