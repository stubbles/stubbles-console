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
use stubbles\streams\InputStream;

use function bovigo\assert\assert;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
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
        assert(ConsoleInputStream::forIn(), isInstanceOf(InputStream::class));
    }

    /**
     * @test
     */
    public function alwaysReturnsSameInstance()
    {
        assert(ConsoleInputStream::forIn(), isSameAs(ConsoleInputStream::forIn()));
    }
}
