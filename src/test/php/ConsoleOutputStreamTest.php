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
use stubbles\streams\OutputStream;

use function bovigo\assert\assert;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isNotSameAs;
use function bovigo\assert\predicate\isSameAs;
/**
 * Test for stubbles\console\ConsoleOutputStream.
 *
 * @group  console
 */
class ConsoleOutputStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function returnsInstanceOfOutputStreamForOut()
    {
        assert(ConsoleOutputStream::forOut(), isInstanceOf(OutputStream::class));
    }

    /**
     * console output stream is always the same instance
     *
     * @test
     */
    public function alwaysReturnsSameInstanceForOut()
    {
        assert(ConsoleOutputStream::forOut(), isSameAs(ConsoleOutputStream::forOut()));
    }

    /**
     * @test
     */
    public function returnsInstanceOfOutputStreamForError()
    {
        assert(ConsoleOutputStream::forError(), isInstanceOf(OutputStream::class));
    }

    /**
     * @test
     */
    public function alwaysReturnsSameInstanceForError()
    {
        assert(
                ConsoleOutputStream::forError(),
                isSameAs(ConsoleOutputStream::forError())
        );
    }

    /**
     * @test
     * @since  6.0.0
     */
    public function outStreamIsNotErrorStream()
    {
        assert(
                ConsoleOutputStream::forOut(),
                isNotSameAs(ConsoleOutputStream::forError())
        );
    }
}
