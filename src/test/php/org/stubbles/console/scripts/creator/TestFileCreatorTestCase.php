<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
namespace org\stubbles\console\scripts\creator;
use org\bovigo\vfs\vfsStream;
/**
 * Test for org\stubbles\console\scripts\creator\TestFileCreator.
 *
 * @group  scripts
 */
class TestFileCreatorTestCase extends \PHPUnit_Framework_TestCase
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
     * @type  \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->root            = vfsStream::setup();
        $this->mockConsole     = $this->getMockBuilder('net\\stubbles\\console\\Console')
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->testFileCreator = new TestFileCreator($this->mockConsole, vfsStream::url('root'));
    }

    /**
     * @test
     */
    public function createsTestIfDoesNotExist()
    {
        $this->mockConsole->expects($this->once())
                          ->method('writeLine')
                          ->with($this->equalTo('Test for example\\console\\ExampleConsoleApp created at ' . vfsStream::url('root/src/test/php/example/console/ExampleConsoleAppTestCase.php')));
        $this->testFileCreator->create('example\\console\\ExampleConsoleApp');
        $this->assertTrue($this->root->hasChild('src/test/php/example/console/ExampleConsoleAppTestCase.php'));
        $this->assertEquals('<?php
/**
 * Your license or something other here.
 *
 * @package  example\console
 */
namespace example\console;
/**
 * Test for example\console\ExampleConsoleApp.
 */
class ExampleConsoleAppTestCase extends \PHPUnit_Framework_TestCase
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
        $this->assertTrue($this->instance->getClass()
                                         ->getConstructor()
                                         ->hasAnnotation(\'Inject\')
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
        $this->assertInstanceOf(\'example\\\\console\\\\ExampleConsoleApp\',
                                ExampleConsoleApp::create(\net\stubbles\lang\ResourceLoader::getRootPath())
        );
    }
}
?>',
                            $this->root->getChild('src/test/php/example/console/ExampleConsoleAppTestCase.php')
                                       ->getContent()
        );
    }

    /**
     * @test
     */
    public function skipsTestCreationIfTestAlreadyExists()
    {
        vfsStream::newDirectory('src/test/php/org/stubbles/console/scripts/creator')
                 ->at($this->root);
        $testFile = vfsStream::newFile('TestFileCreatorTestCase.php')
                             ->withContent('foo')
                             ->at($this->root->getChild('src/test/php/org/stubbles/console/scripts/creator'));
        $this->mockConsole->expects($this->once())
                          ->method('writeLine')
                          ->with($this->equalTo('Test for org\\stubbles\\console\\scripts\\creator\\TestFileCreator already exists, skipped creating the test'));
        $this->testFileCreator->create('org\\stubbles\\console\\scripts\\creator\\TestFileCreator');
        $this->assertEquals('foo', $testFile->getContent());
    }
}
?>