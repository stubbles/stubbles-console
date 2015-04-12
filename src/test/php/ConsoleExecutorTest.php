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
use stubbles\streams\memory\MemoryOutputStream;
/**
 * Test for stubbles\console\ConsoleExecutor.
 *
 * @group  console
 */
class ConsoleExecutorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  \stubbles\console\ConsoleExecutor
     */
    private $executor;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->executor = new ConsoleExecutor();
    }

    /**
     * @test
     */
    public function redirectToReturnsItself()
    {
        assertSame($this->executor, $this->executor->redirectTo('2>&1'));
    }

    /**
     * @test
     */
    public function hasNoOutputStreamByDefault()
    {
        assertNull($this->executor->out());
    }

    /**
     * @test
     */
    public function executeWithoutOutputStream()
    {
        assertSame($this->executor, $this->executor->execute('echo foo'));
    }

    /**
     * @test
     */
    public function outReturnsOutputStreamOriginallySet()
    {
        $outputStream = NewInstance::of('stubbles\streams\OutputStream');
        assertSame(
                $outputStream,
                $this->executor->streamOutputTo($outputStream)->out()
        );
    }

    /**
     * @test
     */
    public function executeWithOutputStreamWritesResponseDataToOutputStream()
    {
        $memory = new MemoryOutputStream();
        $this->executor->streamOutputTo($memory)->execute('echo foo');
        assertEquals("foo\n", $memory->buffer());
    }

    /**
     * @test
     * @expectedException  RuntimeException
     */
    public function executeFailsThrowsRuntimeException()
    {
        $this->executor->execute('php -r "throw new Exception();"');
    }

    /**
     * @test
     */
    public function executeAsyncReturnsStreamToReadResultFrom()
    {
        $commandInputStream = $this->executor->executeAsync('echo foo');
        assertInstanceOf('stubbles\console\CommandInputStream', $commandInputStream);
        assertEquals('foo', chop($commandInputStream->read()));
    }

    /**
     * @test
     * @expectedException  RuntimeException
     */
    public function executeAsyncFailsThrowsRuntimeException()
    {
        $commandInputStream = $this->executor->executeAsync('php -r "throw new Exception();"');
        assertInstanceOf('stubbles\console\CommandInputStream', $commandInputStream);
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
        assertInstanceOf('stubbles\console\CommandInputStream', $commandInputStream);
        assertEquals('foo', chop($commandInputStream->read()));
        $commandInputStream->close();
        $commandInputStream->read();
    }

    /**
     * @test
     */
    public function executeDirectReturnsOutputAsArray()
    {
        assertEquals(['foo'], $this->executor->executeDirect('echo foo'));
    }

    /**
     * @test
     * @expectedException  RuntimeException
     */
    public function executeDirectFailsThrowsRuntimeException()
    {
        $this->executor->executeDirect('php -r "throw new Exception();"');
    }
}
