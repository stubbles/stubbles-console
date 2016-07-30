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
use bovigo\callmap\NewInstance;
use stubbles\console\input\ArgumentParser;
use stubbles\console\input\HelpScreen;
use stubbles\console\input\InvalidOptionValue;
use stubbles\input\errors\ParamErrors;
use stubbles\input\errors\messages\ParamErrorMessages;
use stubbles\streams\memory\MemoryOutputStream;
use stubbles\values\Rootpath;
use org\stubbles\console\test\AppWithoutBindingCanGetConsoleClassesInjected;
use org\stubbles\console\test\ConsoleAppUsingBindingModule;
use org\stubbles\console\test\SelfBoundConsoleApp;
use org\stubbles\console\test\TestConsoleApp;

use function bovigo\assert\assert;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
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
     * @test
     * @since  6.0.0
     */
    public function helpScreenLeadsToExitCode0()
    {
        TestConsoleApp::$exception = new HelpScreen('foo', new \stdClass());
        assert(
                TestConsoleApp::main(
                        'projectPath',
                        $this->errorOutputStream
                ),
                equals(0)
        );
    }

    /**
     * @test
     * @since  6.0.0
     */
    public function invalidOptionValueLeadsToExitCode10()
    {
        TestConsoleApp::$exception = new InvalidOptionValue(
                NewInstance::of(ParamErrors::class),
                NewInstance::of(ParamErrorMessages::class)
        );
        assert(
                TestConsoleApp::main(
                        'projectPath',
                        $this->errorOutputStream
                ),
                equals(10)
        );
    }

    /**
     * @test
     */
    public function applicationExceptionThrownInMainLeadsToExitCode20()
    {
        TestConsoleApp::$exception = new \Exception('failure');
        assert(
                TestConsoleApp::main(new Rootpath(), $this->errorOutputStream),
                equals(20)
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
        assert(
                trim($this->errorOutputStream->buffer()),
                equals("*** Exception: failure\nStacktrace:\n" . $e->getTraceAsString())
        );
    }

    /**
     * @test
     */
    public function commandReturnCodeIsReturned()
    {
        assert(
                TestConsoleApp::main(new Rootpath(), $this->errorOutputStream),
                equals(0)
        );
    }

    /**
     * @since  4.0.0
     * @test
     */
    public function parseArgumentsReturnsBindingModuleForArguments()
    {
        assert(
                ConsoleAppUsingBindingModule::returnArgumentParser(),
                isInstanceOf(ArgumentParser::class)
        );
    }

    /**
     * @since  2.1.0
     * @test
     */
    public function canCreateInstanceWithSelfBoundApp()
    {
        $_SERVER['argv'][1] = 'value';
        $app = SelfBoundConsoleApp::create((string) (new Rootpath()));
        assert($app->bar, equals('value'));
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function stdInputStreamIsBoundAutomatically()
    {
        $app = AppWithoutBindingCanGetConsoleClassesInjected::create('projectPath');
        assert($app->in, isSameAs(ConsoleInputStream::forIn()));
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function stdOutputStreamIsBoundAutomatically()
    {
        $app = AppWithoutBindingCanGetConsoleClassesInjected::create('projectPath');
        assert($app->out, isSameAs(ConsoleOutputStream::forOut()));
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function stdErrOutputStreamIsBoundAutomatically()
    {
        $app = AppWithoutBindingCanGetConsoleClassesInjected::create('projectPath');
        assert($app->err, isSameAs(ConsoleOutputStream::forError()));
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function executorIsBoundAutomatically()
    {
        $app = AppWithoutBindingCanGetConsoleClassesInjected::create('projectPath');
        assert($app->executor, isInstanceOf(Executor::class));
    }
}
