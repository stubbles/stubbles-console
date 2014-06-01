<?php
/**
 * Your license or something other here.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\creator;
use stubbles\lang;
/**
 * Test for stubbles\console\creator\ConsoleAppCreator.
 */
class ConsoleAppCreatorTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ConsoleAppCreator
     */
    private $consoleAppCreator;
    /**
     * mocked console
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockConsole;
    /**
     * mocked class file creator
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockClassFile;
    /**
     * mocked script file creator
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockScriptFile;
    /**
     * mocked test file creator
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockTestFile;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->mockConsole       = $this->getMockBuilder('stubbles\console\Console')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->mockClassFile     = $this->getMockBuilder('stubbles\console\creator\ClassFileCreator')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->mockScriptFile    = $this->getMockBuilder('stubbles\console\creator\ScriptFileCreator')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->mockTestFile      = $this->getMockBuilder('stubbles\console\creator\TestFileCreator')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->consoleAppCreator = new ConsoleAppCreator($this->mockConsole,
                                                         $this->mockClassFile,
                                                         $this->mockScriptFile,
                                                         $this->mockTestFile
                                   );
        $this->mockConsole->expects(($this->any()))
                          ->method('writeLine')
                          ->will($this->returnValue($this->mockConsole));
    }

    /**
     * @test
     */
    public function annotationsPresentOnConstructor()
    {
        $this->assertTrue(
                lang\reflectConstructor($this->consoleAppCreator)->hasAnnotation('Inject')
        );
    }

    /**
     * @test
     */
    public function doesNotCreateClassWhenClassNameIsInvalid()
    {
        $this->mockConsole->expects(($this->any()))
                          ->method('readLine')
                          ->will($this->returnValue('500'));
        $this->mockClassFile->expects($this->never())
                            ->method('create');
        $this->mockScriptFile->expects($this->never())
                           ->method('create');
        $this->mockTestFile->expects($this->never())
                           ->method('create');
        $this->assertEquals(-10, $this->consoleAppCreator->run());
    }

    /**
     * @test
     */
    public function doesNotCreateNonQualifiedPartOfClassWhenClassNameIsInvalid()
    {
        $this->mockConsole->expects(($this->any()))
                          ->method('readLine')
                          ->will($this->returnValue('foo\500'));
        $this->mockClassFile->expects($this->never())
                            ->method('create');
        $this->mockScriptFile->expects($this->never())
                           ->method('create');
        $this->mockTestFile->expects($this->never())
                           ->method('create');
        $this->assertEquals(-10, $this->consoleAppCreator->run());
    }

    /**
     * @test
     */
    public function returnsExitCodeZeroOnSuccess()
    {
        $this->mockConsole->expects(($this->any()))
                          ->method('readLine')
                          ->will($this->returnValue('foo\bar\Example'));
        $this->mockClassFile->expects($this->once())
                            ->method('create')
                            ->with($this->equalTo('foo\bar\Example'));
        $this->mockScriptFile->expects($this->once())
                           ->method('create')
                           ->with($this->equalTo('foo\bar\Example'));
        $this->mockTestFile->expects($this->once())
                           ->method('create')
                           ->with($this->equalTo('foo\bar\Example'));
        $this->assertEquals(0, $this->consoleAppCreator->run());
    }

    /**
     * @test
     */
    public function trimsInputClassName()
    {
        $this->mockConsole->expects(($this->any()))
                          ->method('readLine')
                          ->will($this->returnValue(' foo\bar\Example   '));
        $this->mockClassFile->expects($this->once())
                            ->method('create')
                            ->with($this->equalTo('foo\bar\Example'));
        $this->mockScriptFile->expects($this->once())
                           ->method('create')
                           ->with($this->equalTo('foo\bar\Example'));
        $this->mockTestFile->expects($this->once())
                           ->method('create')
                           ->with($this->equalTo('foo\bar\Example'));
        $this->assertEquals(0, $this->consoleAppCreator->run());
    }

    /**
     * @test
     */
    public function fixesQuotedNamespaceSeparator()
    {
        $this->mockConsole->expects(($this->any()))
                          ->method('readLine')
                          ->will($this->returnValue('foo\\\\bar\\\\Example'));
        $this->mockClassFile->expects($this->once())
                            ->method('create')
                            ->with($this->equalTo('foo\\bar\\Example'));
        $this->mockScriptFile->expects($this->once())
                           ->method('create')
                           ->with($this->equalTo('foo\\bar\\Example'));
        $this->mockTestFile->expects($this->once())
                           ->method('create')
                           ->with($this->equalTo('foo\\bar\\Example'));
        $this->assertEquals(0, $this->consoleAppCreator->run());
    }

    /**
     * @test
     */
    public function canCreateInstance()
    {
        $this->assertInstanceOf('stubbles\console\creator\ConsoleAppCreator',
                                ConsoleAppCreator::create(\stubbles\lang\ResourceLoader::getRootPath())
        );
    }
}
