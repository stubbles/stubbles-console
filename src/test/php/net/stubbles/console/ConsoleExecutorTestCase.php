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
/**
 * Test for net\stubbles\console\ConsoleExecutor.
 *
 * @group  console
 */
class ConsoleExecutorTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ConsoleExecutor
     */
    protected $executor;

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
        $this->assertSame($this->executor, $this->executor->redirectTo('2>&1'));
    }

    /**
     * @test
     */
    public function executeWithoutOutputStream()
    {
        $this->assertNull($this->executor->getOutputStream());
        $this->assertSame($this->executor, $this->executor->execute('echo foo'));
    }

    /**
     * @test
     */
    public function executeWithOutputStreamWritesResponseDataToOutputStream()
    {
        $mockOutputStream = $this->getMock('stubbles\streams\OutputStream');
        $mockOutputStream->expects($this->once())
                         ->method('writeLine')
                         ->with($this->equalTo('foo'));
        $this->assertSame($this->executor, $this->executor->streamOutputTo($mockOutputStream));
        $this->assertSame($mockOutputStream, $this->executor->getOutputStream());
        $this->assertSame($this->executor, $this->executor->execute('echo foo'));
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\RuntimeException
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
        $this->assertInstanceOf('net\stubbles\console\CommandInputStream', $commandInputStream);
        $this->assertEquals('foo', chop($commandInputStream->read()));
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\RuntimeException
     */
    public function executeAsyncFailsThrowsRuntimeException()
    {
        $commandInputStream = $this->executor->executeAsync('php -r "throw new Exception();"');
        $this->assertInstanceOf('net\stubbles\console\CommandInputStream', $commandInputStream);
        while (!$commandInputStream->eof()) {
            $commandInputStream->readLine();
        }

        $commandInputStream->close();
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\IllegalArgumentException
     */
    public function illegalResourceForCommandInputStreamThrowsIllegalArgumentException()
    {
        new CommandInputStream('invalid');
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\IllegalStateException
     */
    public function readAfterCloseThrowsIllegalStateException()
    {
        $commandInputStream = $this->executor->executeAsync('echo foo');
        $this->assertInstanceOf('net\stubbles\console\CommandInputStream', $commandInputStream);
        $this->assertEquals('foo', chop($commandInputStream->read()));
        $commandInputStream->close();
        $commandInputStream->read();
    }

    /**
     * @test
     */
    public function executeDirectReturnsOutputAsArray()
    {
        $this->assertEquals(['foo'], $this->executor->executeDirect('echo foo'));
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\RuntimeException
     */
    public function executeDirectFailsThrowsRuntimeException()
    {
        $this->executor->executeDirect('php -r "throw new Exception();"');
    }

}
