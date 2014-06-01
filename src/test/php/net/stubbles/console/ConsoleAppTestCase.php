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
use stubbles\lang\exception\Exception;
require_once __DIR__ . '/ConsoleAppUsingBindingModule.php';
require_once __DIR__ . '/SelfBoundConsoleApp.php';
require_once __DIR__ . '/TestConsoleApp.php';
use org\stubbles\console\test\ConsoleAppUsingBindingModule;
use org\stubbles\console\test\SelfBoundConsoleApp;
use org\stubbles\console\test\TestConsoleApp;
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
        $this->mockOutputStream    = $this->getMock('stubbles\streams\OutputStream');
        TestConsoleApp::$exception = null;
    }

    /**
     * @test
     */
    public function missingClassnameOptionGivesErrorMessageAndReturns()
    {
        $this->mockOutputStream->expects($this->once())
                               ->method('writeLine');
        $this->assertEquals(1, TestConsoleApp::stubcli('projectPath',
                                                       [],
                                                       $this->mockOutputStream)
        );
    }

    /**
     * @test
     */
    public function missingClassnameInOptionGivesErrorMessageAndReturns()
    {
        $this->mockOutputStream->expects($this->once())
                               ->method('writeLine');
        $this->assertEquals(2, TestConsoleApp::stubcli('projectPath',
                                                       ['-c'],
                                                       $this->mockOutputStream)
        );
    }

    /**
     * @test
     */
    public function invalidClassnameGivesErrorMessageAndReturns()
    {
        $this->mockOutputStream->expects($this->once())
                               ->method('writeLine');
        $this->assertEquals(3, TestConsoleApp::stubcli('projectPath',
                                                       ['-c', 'doesNotExist'],
                                                       $this->mockOutputStream)
        );
    }

    /**
     * @test
     */
    public function thrownConsoleAppExceptionWithMessageIsCatchedInStubcli()
    {
        TestConsoleApp::$exception = new ConsoleAppException('failure', 10);
        $this->mockOutputStream->expects($this->at(0))
                               ->method('writeLine')
                               ->with($this->equalTo('*** Exception: failure'));
        $this->assertEquals(10, ConsoleApp::stubcli('projectPath',
                                                    ['stubcli',
                                                     '-c',
                                                     'org\stubbles\console\test\TestConsoleApp'
                                                    ],
                                                    $this->mockOutputStream
                                )
        );
    }

    /**
     * @test
     */
    public function thrownConsoleAppExceptionWithClosureIsCatchedInStubcli()
    {
        $this->mockOutputStream->expects($this->never())
                               ->method('writeLine');
        $out = $this->getMock('stubbles\streams\OutputStream');
        $out->expects($this->at(0))
            ->method('writeLine')
            ->with($this->equalTo('something happened'));
        TestConsoleApp::$exception = new ConsoleAppException(function() use($out)
                                                             {
                                                                 $out->writeLine('something happened');
                                                             },
                                                             10
                                     );
        $this->assertEquals(10, ConsoleApp::stubcli('projectPath',
                                                    ['stubcli',
                                                     '-c',
                                                     'org\stubbles\console\test\TestConsoleApp'
                                                    ],
                                                    $this->mockOutputStream
                                )
        );
    }

    /**
     * @test
     */
    public function thrownApplicationExceptionIsCatchedInStubcli()
    {
        TestConsoleApp::$exception = new \Exception('failure');
        $this->mockOutputStream->expects($this->at(0))
                               ->method('writeLine')
                               ->with($this->equalTo('*** Exception: failure'));
        $this->assertEquals(20, ConsoleApp::stubcli('projectPath',
                                                    ['stubcli',
                                                     '-c',
                                                     'org\stubbles\console\test\TestConsoleApp'
                                                    ],
                                                    $this->mockOutputStream
                                )
        );
    }

    /**
     * @test
     */
    public function thrownApplicationStubExceptionIsCatchedInStubcli()
    {
        TestConsoleApp::$exception = new Exception('failure');
        $this->mockOutputStream->expects($this->at(0))
                               ->method('writeLine')
                               ->with($this->equalTo('*** stubbles\lang\exception\Exception: failure'));
        $this->assertEquals(20, ConsoleApp::stubcli('projectPath',
                                                    ['stubcli',
                                                     '-c',
                                                     'org\stubbles\console\test\TestConsoleApp'
                                                    ],
                                                     $this->mockOutputStream
                                )
        );
    }

    /**
     * @test
     */
    public function commandReturnCodeIsReturnedInStubcli()
    {
        $this->mockOutputStream->expects($this->never())
                               ->method('writeLine');
        $this->assertEquals(0, ConsoleApp::stubcli('projectPath',
                                                   ['stubcli',
                                                     '-c',
                                                     'org\stubbles\console\test\TestConsoleApp'
                                                    ],
                                                   $this->mockOutputStream
                               )
        );
    }

    /**
     * @test
     */
    public function detectsClassNameIfOnOtherPosition()
    {
        $this->mockOutputStream->expects($this->never())
                               ->method('writeLine');
        $this->assertEquals(0, ConsoleApp::stubcli('projectPath',
                                                   ['stubcli',
                                                    '-v',
                                                    '-other',
                                                    'value',
                                                    '-c',
                                                    'org\stubbles\console\test\TestConsoleApp'
                                                   ],
                                                   $this->mockOutputStream
                               )
        );
    }

    /**
     * @test
     */
    public function thrownConsoleAppExceptionWithMessageIsCatched()
    {
        TestConsoleApp::$exception = new ConsoleAppException('failure', 10);
        $this->mockOutputStream->expects($this->once())
                               ->method('writeLine')
                               ->with($this->equalTo('*** Exception: failure'));
        $this->assertEquals(10, TestConsoleApp::main('projectPath',
                                                     $this->mockOutputStream
                                )
        );
    }

    /**
     * @test
     */
    public function thrownConsoleAppExceptionWithClosureIsCatched()
    {
        $this->mockOutputStream->expects($this->never())
                               ->method('writeLine');
        $out = $this->getMock('stubbles\streams\OutputStream');
        $out->expects($this->once())
            ->method('writeLine')
            ->with($this->equalTo('something happened'));
        TestConsoleApp::$exception = new ConsoleAppException(function() use($out)
                                                             {
                                                                 $out->writeLine('something happened');
                                                             },
                                                             10
                                     );
        $this->assertEquals(10, TestConsoleApp::main('projectPath',
                                                     $this->mockOutputStream
                                )
        );
    }

    /**
     * @test
     */
    public function thrownApplicationExceptionIsCatched()
    {
        TestConsoleApp::$exception = new \Exception('failure');
        $this->mockOutputStream->expects($this->at(0))
                               ->method('writeLine')
                               ->with($this->equalTo('*** Exception: failure'));
        $this->assertEquals(20, TestConsoleApp::main('projectPath',
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
        $this->mockOutputStream->expects($this->at(0))
                               ->method('writeLine')
                               ->with($this->equalTo('*** stubbles\lang\exception\Exception: failure'));
        $this->assertEquals(20, TestConsoleApp::main('projectPath',
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
        $this->assertEquals(0, TestConsoleApp::main('projectPath',
                                                    $this->mockOutputStream
                               )
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function canCreateArgumentsBindingModule()
    {
        $this->assertInstanceOf('net\stubbles\console\ioc\ArgumentsBindingModule',
                                ConsoleAppUsingBindingModule::getArgumentsBindingModule()
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function canCreateConsoleBindingModule()
    {
        $this->assertInstanceOf('net\stubbles\console\ioc\ConsoleBindingModule',
                                ConsoleAppUsingBindingModule::getConsoleBindingModule()
        );
    }

    /**
     * @since  2.1.0
     * @test
     */
    public function canCreateInstanceWithSelfBoundApp()
    {
        $_SERVER['argv'][1] = 'value';
        $this->assertEquals(0, ConsoleApp::stubcli('projectPath',
                                                   ['stubcli',
                                                    'value',
                                                    '-c',
                                                    'org\stubbles\console\test\SelfBoundConsoleApp'
                                                   ],
                                                   $this->mockOutputStream
                               )
         );
        $this->assertEquals('value', SelfBoundConsoleApp::$bar);
    }
}
