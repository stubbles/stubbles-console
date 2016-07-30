<?php
declare(strict_types=1);
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

use function bovigo\assert\{
    assert,
    assertEmptyString,
    expect,
    predicate\equals,
    predicate\isSameAs
};
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
                ->withMessage(
                        'Parameter $out must be a string or an array, but was of'
                        . ' type integer'
                );
    }

    /**
     * @test
     */
    public function executeFailsThrowsRuntimeException()
    {
        expect(function() {
                $this->executor->execute(
                        PHP_BINARY . ' -r "throw new Exception();"'
                );
        })
                ->throws(\RuntimeException::class)
                ->withMessage(
                        'Executing command "' . PHP_BINARY
                        . ' -r "throw new Exception();"" failed: #255'
                );
    }

    /**
     * @test
     */
    public function executeAsyncReturnsStreamToReadResultFrom()
    {
        $inputStream = $this->executor->executeAsync('echo foo');
        assert(chop($inputStream->read()), equals('foo'));
    }

    /**
     * @test
     * @since  7.0.0
     */
    public function readAfterEofOnAsyncExecutionReturnsEmptyString()
    {
        $inputStream = $this->executor->executeAsync('echo foo');
        $inputStream->read();
        assertEmptyString($inputStream->read());
    }

    /**
     * @test
     */
    public function executeAsyncFailsThrowsRuntimeException()
    {
        $inputStream = $this->executor->executeAsync(
                PHP_BINARY . ' -r "throw new Exception();"'
        );
        while (!$inputStream->eof()) {
            $inputStream->readLine();
        }

        expect(function() use ($inputStream) { $inputStream->close(); })
                ->throws(\RuntimeException::class)
                ->withMessage(
                        'Executing command "' . PHP_BINARY
                        . ' -r "throw new Exception();"" failed: #255'
                );
    }

    /**
     * @test
     */
    public function readAfterCloseThrowsIllegalStateException()
    {
        $inputStream = $this->executor->executeAsync('echo foo');
        $inputStream->read(); // read before close
        $inputStream->close();
        expect(function() use ($inputStream) { $inputStream->read(); })
                ->throws(\LogicException::class)
                ->withMessage('Can not read from closed input stream.');
    }

    /**
     * @test
     * @since  7.0.0
     */
    public function destructInputStreamForFailedCommandDoesNotThrowException()
    {
        $inputStream = $this->executor->executeAsync(
                PHP_BINARY . ' -r "throw new Exception();"'
        );
        expect(function() use ($inputStream) {
            $inputStream->read();
            $inputStream = null;
        })
                ->doesNotThrow();
    }
}
