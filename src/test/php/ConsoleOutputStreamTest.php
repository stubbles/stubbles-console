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
use stubbles\streams\OutputStream;
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
        assertInstanceOf(
                OutputStream::class,
                ConsoleOutputStream::forOut()
        );
    }

    /**
     * console output stream is always the same instance
     *
     * @test
     */
    public function alwaysReturnsSameInstanceForOut()
    {
        assertSame(ConsoleOutputStream::forOut(), ConsoleOutputStream::forOut());
    }

    /**
     * @test
     */
    public function returnsInstanceOfOutputStreamForError()
    {
        assertInstanceOf(
                OutputStream::class,
                ConsoleOutputStream::forError()
        );
    }

    /**
     * @test
     */
    public function alwaysReturnsSameInstanceForError()
    {
        assertSame(
                ConsoleOutputStream::forError(),
                ConsoleOutputStream::forError()
        );
    }
}
