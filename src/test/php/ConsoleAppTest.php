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
        $this->assertEquals(
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
                $this->createStubCliApp(['-c'])
        );
    }

    /**
     * @test
     */
    public function missingClassnameValueInOptionWritesErrorMessageToErrorStream()
    {
        $this->createStubCliApp(['-c']);
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
                $this->createStubCliApp(['-c', 'doesNotExist'])
        );
    }

    /**
     * @test
     */
    public function invalidClassnameWritesErrorMessageToErrorStream()
    {
        $this->createStubCliApp(['-c', 'doesNotExist']);
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
                $this->createStubCliApp(
                        ['stubcli',
                         '-c',
                         'org\stubbles\console\test\TestConsoleApp'
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
                 'org\stubbles\console\test\TestConsoleApp'
                ]
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
        $this->createStubCliApp(
                ['stubcli',
                 '-c',
                 'org\stubbles\console\test\TestConsoleApp'
                ]
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
                $this->createStubCliApp(
                        ['stubcli',
                         '-c',
                         'org\stubbles\console\test\TestConsoleApp'
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
                 'org\stubbles\console\test\TestConsoleApp'
                ]
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
                $this->createStubCliApp(
                        ['stubcli',
                         '-c',
                         'org\stubbles\console\test\TestConsoleApp'
                        ]
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
                $this->createStubCliApp(
                        ['stubcli',
                         '-v',
                         '-other',
                         'value',
                         '-c',
                         'org\stubbles\console\test\TestConsoleApp'
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
                TestConsoleApp::main(new Rootpath(), $this->errorOutputStream)
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
        TestConsoleApp::main(new Rootpath(), $this->errorOutputStream);
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
                TestConsoleApp::main(new Rootpath(), $this->errorOutputStream)
        );
    }

    /**
     * @since  4.0.0
     * @test
     */
    public function canCreateArguments()
    {
        $this->assertInstanceOf(
                'stubbles\console\ioc\Arguments',
                ConsoleAppUsingBindingModule::returnBindArguments()
        );
    }

    /**
     * @since  2.0.0
     * @test
     * @deprecated  since 4.0.0
     */
    public function canCreateArgumentsBindingModule()
    {
        $this->assertInstanceOf(
                'stubbles\console\ioc\Arguments',
                ConsoleAppUsingBindingModule::getArgumentsBindingModule()
        );
    }

    /**
     * @since  2.0.0
     * @test
     * @deprecated  since 4.0.0
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
                $this->createStubCliApp(
                        ['stubcli',
                         'value',
                         '-c',
                         'org\stubbles\console\test\SelfBoundConsoleApp'
                        ]
                )
         );
        $this->assertEquals('value', SelfBoundConsoleApp::$bar);
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
                 'org\stubbles\console\test\SelfBoundConsoleApp'
                ]
         );
        $this->assertEquals('', $this->errorOutputStream->buffer());
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function stdInputStreamIsBoundAutomatically()
    {
        $app = AppWithoutBindingCanGetConsoleClassesInjected::create('projectPath');
        $this->assertSame(
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
        $this->assertSame(
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
        $this->assertSame(
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
        $this->assertInstanceOf(
                'stubbles\console\ConsoleExecutor',
                $app->executor
        );
    }
}
