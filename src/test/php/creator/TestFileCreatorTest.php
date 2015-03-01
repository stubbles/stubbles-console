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
    private $mockConsole;
    /**
     * root directory
     *
     * @type  Rootpath
     */
    private $rootpath;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->rootpath        = new Rootpath(vfsStream::setup()->url());
        $this->mockConsole     = $this->getMockBuilder('stubbles\console\Console')
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->testFileCreator = new TestFileCreator($this->mockConsole, $this->rootpath, new ResourceLoader());
    }

    /**
     * @test
     */
    public function createsTestIfDoesNotExist()
    {
        $this->mockConsole->expects($this->once())
                          ->method('writeLine')
                          ->with($this->equalTo('Test for example\console\ExampleConsoleApp created at ' . $this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php')));
        $this->testFileCreator->create('example\console\ExampleConsoleApp');
        $this->assertTrue(file_exists($this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php')));
        $this->assertEquals(
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
                reflect\constructorAnnotationsOf($this->instance)
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
    }

    /**
     * @test
     */
    public function skipsTestCreationIfTestAlreadyExists()
    {
        mkdir($this->rootpath->to('src/test/php/example/console'), 0755, true);
        file_put_contents($this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php'), 'foo');
        $this->mockConsole->expects($this->once())
                          ->method('writeLine')
                          ->with($this->equalTo('Test for example\console\ExampleConsoleApp already exists, skipped creating the test'));
        $this->testFileCreator->create('example\console\ExampleConsoleApp');
        $this->assertEquals(
                'foo',
                file_get_contents(
                        $this->rootpath->to('src/test/php/example/console/ExampleConsoleAppTest.php')
                 )
         );
    }
}
