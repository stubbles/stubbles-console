<?php
/**
 * Your license or something other here.
 *
 * @package  org\stubbles\console\scripts\creator
 */
namespace org\stubbles\console\scripts\creator;
/**
 * Test for org\stubbles\console\scripts\creator\ConsoleAppCreator.
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
        $this->mockConsole       = $this->getMockBuilder('net\stubbles\console\Console')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->mockClassFile     = $this->getMockBuilder('org\stubbles\console\scripts\creator\ClassFileCreator')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->mockScriptFile    = $this->getMockBuilder('org\stubbles\console\scripts\creator\ScriptFileCreator')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->mockTestFile      = $this->getMockBuilder('org\stubbles\console\scripts\creator\TestFileCreator')
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
        $this->assertTrue($this->consoleAppCreator->getClass()
                                                  ->getConstructor()
                                                  ->hasAnnotation('Inject')
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
        $this->assertInstanceOf('org\stubbles\console\scripts\creator\ConsoleAppCreator',
                                ConsoleAppCreator::create(\net\stubbles\lang\ResourceLoader::getRootPath())
        );
    }
}
?>