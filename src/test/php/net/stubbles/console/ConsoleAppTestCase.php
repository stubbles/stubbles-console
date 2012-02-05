<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
namespace net\stubbles\console;
use net\stubbles\lang\exception\Exception;
use org\stubbles\test\console\TestConsoleApp;
/**
 * Test for net\stubbles\console\ConsoleApp.
 *
 * @group  console
 */
class ConsoleAppTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * mocked output stream
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockOutputStream;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->mockOutputStream    = $this->getMock('net\\stubbles\\streams\\OutputStream');
        TestConsoleApp::$exception = null;
    }

    /**
     * @test
     */
    public function missingClassnameGivesErrorMessageAndReturns()
    {
        $this->mockOutputStream->expects($this->once())
                               ->method('writeLine');
        $this->assertEquals(1, TestConsoleApp::main('projectPath', array(), $this->mockOutputStream));
    }

    /**
     * @test
     */
    public function thrownApplicationExceptionIsCatched()
    {
        TestConsoleApp::$exception = new \Exception('failure');
        $this->mockOutputStream->expects($this->once())
                               ->method('writeLine')
                               ->with($this->equalTo('*** Exception: failure'));
        $this->assertEquals(70, ConsoleApp::main('projectPath',
                                                 array('stubcli',
                                                       'org\\stubbles\\test\\console\\TestConsoleApp'
                                                 ),
                                                 $this->mockOutputStream
                                )
        );
    }

    /**
     * @test
     */
    public function thrownApplicationStubExceptionIsCatched()
    {
        TestConsoleApp::$exception = new Exception('failure');
        $this->mockOutputStream->expects($this->once())
                               ->method('writeLine')
                               ->with($this->equalTo('*** net\stubbles\lang\exception\Exception: failure'));
        $this->assertEquals(70, ConsoleApp::main('projectPath',
                                                 array('stubcli',
                                                       'org\\stubbles\\test\\console\\TestConsoleApp'
                                                 ),
                                                 $this->mockOutputStream
                                )
        );
    }

    /**
     * @test
     */
    public function commandReturnCodeIsReturned()
    {
        $this->mockOutputStream->expects($this->never())
                               ->method('writeLine');
        $this->assertEquals(313, ConsoleApp::main('projectPath',
                                                  array('stubcli',
                                                        'org\\stubbles\\test\\console\\TestConsoleApp'
                                                  ),
                                                  $this->mockOutputStream
                                 )
        );
    }
}
?>