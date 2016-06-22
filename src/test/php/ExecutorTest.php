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

use function bovigo\assert\assert;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isSameAs;
/**
 * Test for stubbles\console\Executor.
 *
 * @group  console
 */
class ExecutorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  \stubbles\console\Executor
     */
    private $executor;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->executor = new Executor();
    }

    /**
     * @test
     */
    public function executeWithoutOutputStream()
    {
        assert($this->executor->execute('echo foo'), isSameAs($this->executor));
    }

    /**
     * @test
     */
    public function executeWithCallableCallsCallableWithCommandOutput()
    {
        $memory = new MemoryOutputStream();
        $this->executor->execute('echo foo;echo bar', [$memory, 'writeLine']);
        assert($memory->buffer(), equals("foo\nbar\n"));
    }

    /**
     * @test
     * @since  6.1.0
     */
    public function executeWithCollectStringAppendsCommandOutputToString()
    {
        $out = '';
        $this->executor->execute('echo foo;echo bar', collect($out));
        assert($out, equals("foo\nbar\n"));
    }

    /**
     * @test
     * @since  6.1.0
     */
    public function executeWithCollectArrayAppendsCommandOutputToArray()
    {
        $out = [];
        $this->executor->execute('echo foo;echo bar', collect($out));
        assert($out, equals(['foo', 'bar']));
    }

    /**
     * @test
     * @since  6.1.0
     */
    public function collectInNonStringAndNonArrayThrowsInvalidArgumentException()
    {
        expect(function() { $var = 0; collect($var); })
                ->throws(\InvalidArgumentException::class)
                ->withMessage('Parameter $out must be a string or an array, but was of type integer');
    }

    /**
     * @test
     * @expectedException  RuntimeException
     */
    public function executeFailsThrowsRuntimeException()
    {
        $this->executor->execute(PHP_BINARY . ' -r "throw new Exception();"');
    }

    /**
     * @test
     */
    public function executeAsyncReturnsStreamToReadResultFrom()
    {
        $commandInputStream = $this->executor->executeAsync('echo foo');
        assert(chop($commandInputStream->read()), equals('foo'));
    }

    /**
     * @test
     * @expectedException  RuntimeException
     */
    public function executeAsyncFailsThrowsRuntimeException()
    {
        $commandInputStream = $this->executor->executeAsync(
                PHP_BINARY . ' -r "throw new Exception();"'
        );
        while (!$commandInputStream->eof()) {
            $commandInputStream->readLine();
        }

        $commandInputStream->close();
    }

    /**
     * @test
     * @expectedException  InvalidArgumentException
     */
    public function illegalResourceForCommandInputStreamThrowsIllegalArgumentException()
    {
        new CommandInputStream('invalid');
    }

    /**
     * @test
     * @expectedException  LogicException
     */
    public function readAfterCloseThrowsIllegalStateException()
    {
        $commandInputStream = $this->executor->executeAsync('echo foo');
        $commandInputStream->read(); // read before close
        $commandInputStream->close();
        $commandInputStream->read();
    }

    /**
     * @test
     * @deprecated  since 6.1.0, will be removed with 7.0.0
     */
    public function executeDirectReturnsOutputAsArray()
    {
        assert($this->executor->executeDirect('echo foo && echo bar'), equals(['foo', 'bar']));
    }

    /**
     * @test
     * @expectedException  RuntimeException
     * @deprecated  since 6.1.0, will be removed with 7.0.0
     */
    public function executeDirectFailsThrowsRuntimeException()
    {
        $this->executor->executeDirect(
                PHP_BINARY . ' -r "throw new Exception();"'
        );
    }
}
