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
/**
 * Test for stubbles\console\ConsoleInputStream.
 *
 * @group  console
 */
class ConsoleInputStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function returnsInstanceOfInputStream()
    {
        assertInstanceOf(
                'stubbles\streams\InputStream',
                ConsoleInputStream::forIn()
        );
    }

    /**
     * @test
     */
    public function alwaysReturnsSameInstance()
    {
        assertSame(ConsoleInputStream::forIn(), ConsoleInputStream::forIn());
    }
}
