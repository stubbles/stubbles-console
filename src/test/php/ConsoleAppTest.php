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
use stubbles\console\ioc\ArgumentParser;
use stubbles\lang\Rootpath;
use stubbles\streams\memory\MemoryOutputStream;
use org\stubbles\console\test\AppWithoutBindingCanGetConsoleClassesInjected;
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
     * clean up test environment
     */
    public function tearDown()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * @param   string[]  $argv
     * @return  ConsoleApp
     */
    private function createStubCliApp(array $argv)
    {
        return ConsoleApp::stubcli(
                new Rootpath(),
                $argv,
                $this->errorOutputStream
        );
    }

    /**
     * @test
     */
    public function missingClassnameOptionLeadsToExistCode1()
    {
        assertEquals(
                1,
                $this->createStubCliApp([])
        );
    }

    /**
     * @test
     */
    public function missingClassnameOptionWritesErrorMessageToErrorStream()
    {
        $this->createStubCliApp([]);
        assertEquals(
                '*** Missing classname option -c',
                trim($this->errorOutputStream->buffer())
        );
    }

    /**
     * @test
     */
    public function missingClassnameValueInOptionLeadsToExistCode2()
    {
        assertEquals(
                2,
                $this->createStubCliApp(['-c'])
        );
    }

    /**
     * @test
     */
    public function missingClassnameValueInOptionWritesErrorMessageToErrorStream()
    {
        $this->createStubCliApp(['-c']);
        assertEquals(
                '*** No classname specified in -c',
                trim($this->errorOutputStream->buffer())
        );
    }

    /**
     * @test
     */
    public function invalidClassnameLeadsToExistCode3()
    {
        assertEquals(
                3,
                $this->createStubCliApp(['-c', 'doesNotExist'])
        );
    }

    /**
     * @test
     */
    public function invalidClassnameWritesErrorMessageToErrorStream()
    {
        $this->createStubCliApp(['-c', 'doesNotExist']);
        assertEquals(
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
        assertEquals(
                10,
                $this->createStubCliApp(
                        ['stubcli',
                         '-c',
                         TestConsoleApp::class
                        ]
                )
        );
    }

    /**
     * @test
     */
    public function messageFromConsoleAppExceptionThrownInStubcliIsWrittenToErrorStream()
    {
        TestConsoleApp::$exception = new ConsoleAppException('failure', 10);
        $this->createStubCliApp(
                ['stubcli',
                 '-c',
                 TestConsoleApp::class
                ]
        );
        assertEquals(
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
        $this->createStubCliApp(
                ['stubcli',
                 '-c',
                 TestConsoleApp::class
                ]
        );
        assertEquals(
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
        assertEquals(
                20,
                $this->createStubCliApp(
                        ['stubcli',
                         '-c',
                         TestConsoleApp::class
                        ]
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
        $this->createStubCliApp(
                ['stubcli',
                 '-c',
                 TestConsoleApp::class
                ]
        );
        assertEquals(
                "*** Exception: failure\nStacktrace:\n" . $e->getTraceAsString(),
                trim($this->errorOutputStream->buffer())
        );
    }

    /**
     * @test
     */
    public function commandReturnCodeIsReturnedInStubcli()
    {
        assertEquals(
                0,
                $this->createStubCliApp(
                        ['stubcli',
                         '-c',
                         TestConsoleApp::class
                        ]
                )
        );
    }

    /**
     * @test
     */
    public function detectsClassNameIfOnOtherPosition()
    {
        assertEquals(
                0,
                $this->createStubCliApp(
                        ['stubcli',
                         '-v',
                         '-other',
                         'value',
                         '-c',
                         TestConsoleApp::class
                        ]
                )
        );
    }

    /**
     * @test
     */
    public function thrownConsoleAppExceptionLeadsToExitCodeOfException()
    {
        TestConsoleApp::$exception = new ConsoleAppException('failure', 10);
        assertEquals(
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
        assertEquals(
                10,
                TestConsoleApp::main(new Rootpath(), $this->errorOutputStream)
        );
        assertEquals(
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
        TestConsoleApp::main(new Rootpath(), $this->errorOutputStream);
        assertEquals(
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
        assertEquals(
                20,
                TestConsoleApp::main(new Rootpath(), $this->errorOutputStream)
        );
    }

    /**
     * @test
     */
    public function messageFromApplicationExceptionThrownInMainIsWrittenToErrorStream()
    {
        $e = new \Exception('failure');
        TestConsoleApp::$exception = $e;
        TestConsoleApp::main(new Rootpath(), $this->errorOutputStream);
        assertEquals(
                "*** Exception: failure\nStacktrace:\n" . $e->getTraceAsString(),
                trim($this->errorOutputStream->buffer())
        );
    }

    /**
     * @test
     */
    public function commandReturnCodeIsReturned()
    {
        assertEquals(
                0,
                TestConsoleApp::main(new Rootpath(), $this->errorOutputStream)
        );
    }

    /**
     * @since  4.0.0
     * @test
     */
    public function parseArgumentsReturnsBindingModuleForArguments()
    {
        assertInstanceOf(
                ArgumentParser::class,
                ConsoleAppUsingBindingModule::returnArgumentParser()
        );
    }

    /**
     * @since  2.1.0
     * @test
     */
    public function canCreateInstanceWithSelfBoundApp()
    {
        $_SERVER['argv'][1] = 'value';
        assertEquals(
                0,
                $this->createStubCliApp(
                        ['stubcli',
                         'value',
                         '-c',
                         SelfBoundConsoleApp::class
                        ]
                )
         );
        assertEquals('value', SelfBoundConsoleApp::$bar);
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function successfulInstanceCreationDoesNotWriteToErrorStream()
    {
        $_SERVER['argv'][1] = 'value';
        $this->createStubCliApp(
                ['stubcli',
                 'value',
                 '-c',
                 SelfBoundConsoleApp::class
                ]
         );
        assertEquals('', $this->errorOutputStream->buffer());
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function stdInputStreamIsBoundAutomatically()
    {
        $app = AppWithoutBindingCanGetConsoleClassesInjected::create('projectPath');
        assertSame(
                ConsoleInputStream::forIn(),
                $app->in
        );
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function stdOutputStreamIsBoundAutomatically()
    {
        $app = AppWithoutBindingCanGetConsoleClassesInjected::create('projectPath');
        assertSame(
                ConsoleOutputStream::forOut(),
                $app->out
        );
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function stdErrOutputStreamIsBoundAutomatically()
    {
        $app = AppWithoutBindingCanGetConsoleClassesInjected::create('projectPath');
        assertSame(
                ConsoleOutputStream::forError(),
                $app->err
        );
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function executorIsBoundAutomatically()
    {
        $app = AppWithoutBindingCanGetConsoleClassesInjected::create('projectPath');
        assertInstanceOf(
                ConsoleExecutor::class,
                $app->executor
        );
    }
}
