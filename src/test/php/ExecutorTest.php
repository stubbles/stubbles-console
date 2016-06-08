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
    public function executeWithOutputStreamWritesResponseDataToOutputStream()
    {
        $memory = new MemoryOutputStream();
        $this->executor->execute('echo foo', $memory);
        assert($memory->buffer(), equals("foo\n"));
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
     */
    public function executeDirectReturnsOutputAsArray()
    {
        assert($this->executor->executeDirect('echo foo'), equals(['foo']));
    }

    /**
     * @test
     * @expectedException  RuntimeException
     */
    public function executeDirectFailsThrowsRuntimeException()
    {
        $this->executor->executeDirect(
                PHP_BINARY . ' -r "throw new Exception();"'
        );
    }
}
