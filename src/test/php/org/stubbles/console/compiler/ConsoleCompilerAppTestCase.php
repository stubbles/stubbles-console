<?php
/**
 * Your license or something other here.
 *
 * @package  org\stubbles\console\compiler
 */
namespace org\stubbles\console\compiler;
/**
 * Test for org\stubbles\console\compiler\ConsoleCompilerApp.
 */
class ConsoleCompilerAppTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ConsoleCompilerApp
     */
    private $consoleCompilerApp;
    /**
     * mocked compiler instance
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCompiler;
    /**
     * mocked error output stream
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockOutputStream;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->mockCompiler       = $this->getMockBuilder('org\stubbles\console\compiler\Compiler')
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->mockOutputStream   = $this->getMock('net\stubbles\streams\OutputStream');
        $this->consoleCompilerApp = new ConsoleCompilerApp($this->mockCompiler, $this->mockOutputStream);
    }

    /**
     * @test
     */
    public function annotationsPresentOnConstructor()
    {
        $this->assertTrue($this->consoleCompilerApp->getClass()
                                         ->getConstructor()
                                         ->hasAnnotation('Inject')
        );
    }

    /**
     * @test
     */
    public function annotationsPresentSetScriptMethod()
    {
        $method = $this->consoleCompilerApp->getClass()->getMethod('setScript');
        $this->assertTrue($method->hasAnnotation('Inject'));
        $this->assertTrue($method->getAnnotation('Inject')->isOptional());
        $this->assertTrue($method->hasAnnotation('Named'));
        $this->assertEquals('argv.0', $method->getAnnotation('Named')->getName());
    }

    /**
     * @test
     */
    public function returnsExitCode30WhenNoScriptSet()
    {
        $this->assertEquals(30, $this->consoleCompilerApp->run());
    }

    /**
     * @test
     */
    public function writesErrorMessageWhenNoScriptSet()
    {
        $this->mockOutputStream->expects($this->once())
                               ->method('writeLine');
        $this->consoleCompilerApp->run();
    }

    /**
     * @test
     */
    public function returnsExitCode0WhenSuccessful()
    {
        $this->mockOutputStream->expects($this->never())
                               ->method('writeLine');
        $mockPharCreator = $this->getMockBuilder('org\stubbles\console\compiler\PharFileCreator')
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->mockCompiler->expects($this->once())
                           ->method('compile')
                           ->with($this->equalTo('fooScript'))
                           ->will($this->returnValue($mockPharCreator));
        $mockPharCreator->expects($this->once())
                        ->method('save');
        $this->assertEquals(0, $this->consoleCompilerApp->setScript('fooScript')->run());
    }

    /**
     * @test
     */
    public function canCreateInstance()
    {
        $this->assertInstanceOf('org\stubbles\console\compiler\ConsoleCompilerApp',
                                ConsoleCompilerApp::create(\net\stubbles\lang\ResourceLoader::getRootPath())
        );
    }
}
?>