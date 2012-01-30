<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
namespace org\stubbles\test\console;
use net\stubbles\console\ConsoleCommand;
use net\stubbles\lang\BaseObject;
/**
 * Helper class for the test.
 */
class TestConsoleCommandRunner extends BaseObject implements ConsoleCommand
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

        return 313;
    }
}
?>