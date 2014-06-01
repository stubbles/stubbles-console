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
 * Test for net\stubbles\console\ConsoleInputStream.
 *
 * @group  console
 */
class ConsoleInputStreamTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function returnsInstanceOfInputStream()
    {
        $this->assertInstanceOf('stubbles\streams\InputStream',
                                ConsoleInputStream::forIn()
        );
    }

    /**
     * @test
     */
    public function alwaysReturnsSameInstance()
    {
        $this->assertSame(ConsoleInputStream::forIn(),
                          ConsoleInputStream::forIn()
        );
    }
}
