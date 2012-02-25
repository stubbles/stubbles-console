<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
namespace org\stubbles\console\test;
use net\stubbles\console\ConsoleApp;
/**
 * Helper class for the test.
 */
class TestConsoleApp extends ConsoleApp
{
    /**
     * exception to be thrown
     *
     * @type  \Exception
     */
    public static $exception;

    /**
     * runs the command and returns an exit code
     *
     * @return  int
     */
    public function run()
    {
        if (null !== self::$exception) {
            throw self::$exception;
        }

        return 0;
    }
}
?>