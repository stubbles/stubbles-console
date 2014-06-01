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
 * Test for net\stubbles\console\ConsoleOutputStream.
 *
 * @group  console
 */
class ConsoleOutputStreamTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function returnsInstanceOfOutputStreamForOut()
    {
        $this->assertInstanceOf('stubbles\streams\OutputStream',
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
        $this->assertSame(ConsoleOutputStream::forOut(),
                          ConsoleOutputStream::forOut()
        );
    }

    /**
     * @test
     */
    public function returnsInstanceOfOutputStreamForError()
    {
        $this->assertInstanceOf('stubbles\streams\OutputStream',
                                ConsoleOutputStream::forError()
        );
    }

    /**
     * @test
     */
    public function alwaysReturnsSameInstanceForError()
    {
        $this->assertSame(ConsoleOutputStream::forError(),
                          ConsoleOutputStream::forError()
        );
    }
}
