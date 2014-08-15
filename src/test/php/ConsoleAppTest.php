<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console;
use stubbles\streams\memory\MemoryOutputStream;
use org\stubbles\console\test\ConsoleAppUsingBindingModule;
use org\stubbles\console\test\SelfBoundConsoleApp;
use org\stubbles\console\test\TestConsoleApp;
/**
 * Test for stubbles\console\ConsoleApp.
 *
 * @group  console
 */
class ConsoleAppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * output stream
     *
     * @type  \stubbles\streams\memory\MemoryOutputStream
     */
    private $errorOutputStream;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->errorOutputStream   = new MemoryOutputStream();
        TestConsoleApp::$exception = null;
    }

    /**
     * @test
     */
    public function missingClassnameOptionLeadsToExistCode1()
    {
        $this->assertEquals(
                1,
                TestConsoleApp::stubcli(
                        'projectPath',
                        [],
                        $this->errorOutputStream
                )
        );
    }

    /**
     * @test
     */
    public function missingClassnameOptionWritesErrorMessageToErrorStream()
    {
        TestConsoleApp::stubcli('projectPath', [], $this->errorOutputStream);
        $this->assertEquals(
                '*** Missing classname option -c',
                trim($this->errorOutputStream->buffer())
        );
    }

    /**
     * @test
     */
    public function missingClassnameValueInOptionLeadsToExistCode2()
    {
        $this->assertEquals(
                2,
                TestConsoleApp::stubcli(
                        'projectPath',
                        ['-c'],
                        $this->errorOutputStream
                )
        );
    }

    /**
     * @test
     */
    public function missingClassnameValueInOptionWritesErrorMessageToErrorStream()
    {
        TestConsoleApp::stubcli('projectPath', ['-c'],$this->errorOutputStream);
        $this->assertEquals(
                '*** No classname specified in -c',
                trim($this->errorOutputStream->buffer())
        );
    }

    /**
     * @test
     */
    public function invalidClassnameLeadsToExistCode3()
    {
        $this->assertEquals(
                3,
                TestConsoleApp::stubcli(
                        'projectPath',
                        ['-c', 'doesNotExist'],
                        $this->errorOutputStream
                )
        );
    }

    /**
     * @test
     */
    public function invalidClassnameWritesErrorMessageToErrorStream()
    {
        TestConsoleApp::stubcli('projectPath',['-c', 'doesNotExist'], $this->errorOutputStream);
        $this->assertEquals(
                '*** Can not find doesNotExist',
                trim($this->errorOutputStream->buffer())
        );
    }

    /**
     * @test
     */
    public function thrownConsoleAppExceptionInStubCliLeadsToExitCodeOfException()
    {
        TestConsoleApp::$exception = new ConsoleAppException('failure', 10);
        $this->assertEquals(
                10,
                ConsoleApp::stubcli(
                        'projectPath',
                        ['stubcli',
                         '-c',
                         'org\stubbles\console\test\TestConsoleApp'
                        ],
                        $this->errorOutputStream
                )
        );
    }

    /**
     * @test
     */
    public function messageFromConsoleAppExceptionThrownInStubcliIsWrittenToErrorStream()
    {
        TestConsoleApp::$exception = new ConsoleAppException('failure', 10);
        ConsoleApp::stubcli(
                'projectPath',
                ['stubcli',
                 '-c',
                 'org\stubbles\console\test\TestConsoleApp'
                ],
                $this->errorOutputStream
        );
        $this->assertEquals(
                '*** Exception: failure',
                trim($this->errorOutputStream->buffer())
        );
    }

    /**
     * @test
     */
    public function thrownConsoleAppExceptionWithMessageClosureIsWrittenToErrorStream()
    {
        TestConsoleApp::$exception = new ConsoleAppException(function(MemoryOutputStream $out)
                                                             {
                                                                 $out->writeLine('something happened');
                                                             },
                                                             10
                                     );
        ConsoleApp::stubcli(
                'projectPath',
                ['stubcli',
                 '-c',
                 'org\stubbles\console\test\TestConsoleApp'
                ],
                $this->errorOutputStream
        );
        $this->assertEquals(
                'something happened',
                trim($this->errorOutputStream->buffer())
        );
    }

    /**
     * @test
     */
    public function applicationExceptionThrownInStubCliLeadsToExitCode20()
    {
        TestConsoleApp::$exception = new \Exception('failure');
        $this->assertEquals(
                20,
                ConsoleApp::stubcli(
                        'projectPath',
                        ['stubcli',
                         '-c',
                         'org\stubbles\console\test\TestConsoleApp'
                        ],
                        $this->errorOutputStream
                )
        );
    }

    /**
     * @test
     */
    public function messageFromApplicationExceptionThrownInStubCliIsWrittenToErrorStream()
    {
        $e = new \Exception('failure');
        TestConsoleApp::$exception = $e;
        ConsoleApp::stubcli(
                'projectPath',
                ['stubcli',
                 '-c',
                 'org\stubbles\console\test\TestConsoleApp'
                ],
                $this->errorOutputStream
        );
        $this->assertEquals(
                "*** Exception: failure\nStacktrace:\n" . $e->getTraceAsString(),
                trim($this->errorOutputStream->buffer())
        );
    }

    /**
     * @test
     */
    public function commandReturnCodeIsReturnedInStubcli()
    {
        $this->assertEquals(
                0,
                ConsoleApp::stubcli(
                        'projectPath',
                        ['stubcli',
                          '-c',
                          'org\stubbles\console\test\TestConsoleApp'
                         ],
                        $this->errorOutputStream
                )
        );
    }

    /**
     * @test
     */
    public function detectsClassNameIfOnOtherPosition()
    {
        $this->assertEquals(
                0,
                ConsoleApp::stubcli(
                        'projectPath',
                        ['stubcli',
                         '-v',
                         '-other',
                         'value',
                         '-c',
                         'org\stubbles\console\test\TestConsoleApp'
                        ],
                        $this->errorOutputStream
                )
        );
    }

    /**
     * @test
     */
    public function thrownConsoleAppExceptionLeadsToExitCodeOfException()
    {
        TestConsoleApp::$exception = new ConsoleAppException('failure', 10);
        $this->assertEquals(
                10,
                TestConsoleApp::main(
                        'projectPath',
                        $this->errorOutputStream
                )
        );
    }

    /**
     * @test
     */
    public function messageFromConsoleAppExceptionThrownInMainIsWrittenToErrorStream()
    {
        TestConsoleApp::$exception = new ConsoleAppException('failure', 10);
        $this->assertEquals(
                10,
                TestConsoleApp::main(
                        'projectPath',
                        $this->errorOutputStream
                )
        );
        $this->assertEquals(
                '*** Exception: failure',
                trim($this->errorOutputStream->buffer())
        );
    }

    /**
     * @test
     */
    public function messageClosureFromConsoleAppExceptionThrownInMainIsWrittenToErrorStream()
    {
        TestConsoleApp::$exception = new ConsoleAppException(function(MemoryOutputStream $out)
                                                             {
                                                                 $out->writeLine('something happened');
                                                             },
                                                             10
                                     );
        TestConsoleApp::main(
                'projectPath',
                $this->errorOutputStream
        );
        $this->assertEquals(
                'something happened',
                trim($this->errorOutputStream->buffer())
        );
    }

    /**
     * @test
     */
    public function applicationExceptionThrownInMainLeadsToExitCode20()
    {
        TestConsoleApp::$exception = new \Exception('failure');
        $this->assertEquals(
                20,
                TestConsoleApp::main(
                        'projectPath',
                        $this->errorOutputStream
                )
        );
    }

    /**
     * @test
     */
    public function messageFromApplicationExceptionThrownInMainIsWrittenToErrorStream()
    {
        $e = new \Exception('failure');
        TestConsoleApp::$exception = $e;
        TestConsoleApp::main(
                'projectPath',
                $this->errorOutputStream
        );
        $this->assertEquals(
                "*** Exception: failure\nStacktrace:\n" . $e->getTraceAsString(),
                trim($this->errorOutputStream->buffer())
        );
    }

    /**
     * @test
     */
    public function commandReturnCodeIsReturned()
    {
        $this->assertEquals(
                0,
                TestConsoleApp::main(
                        'projectPath',
                        $this->errorOutputStream
                )
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function canCreateArgumentsBindingModule()
    {
        $this->assertInstanceOf(
                'stubbles\console\ioc\ArgumentsBindingModule',
                ConsoleAppUsingBindingModule::getArgumentsBindingModule()
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function canCreateConsoleBindingModule()
    {
        $this->assertInstanceOf(
                'stubbles\console\ioc\ConsoleBindingModule',
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
        $this->assertEquals(
                0,
                ConsoleApp::stubcli(
                        'projectPath',
                        ['stubcli',
                         'value',
                         '-c',
                         'org\stubbles\console\test\SelfBoundConsoleApp'
                        ],
                        $this->errorOutputStream
                )
         );
        $this->assertEquals('value', SelfBoundConsoleApp::$bar);
    }

    /**
     * @since  4.0.0
     * @test
     */
    public function successfulInstanceCreationDoesNotWriteToErrorStream()
    {
        $_SERVER['argv'][1] = 'value';
        ConsoleApp::stubcli(
                'projectPath',
                ['stubcli',
                 'value',
                 '-c',
                 'org\stubbles\console\test\SelfBoundConsoleApp'
                ],
                $this->errorOutputStream
         );
        $this->assertEquals('', $this->errorOutputStream->buffer());
    }
}
