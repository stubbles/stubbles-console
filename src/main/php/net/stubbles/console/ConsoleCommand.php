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
use net\stubbles\lang\Object;
/**
 * Interface for commands to be executed on the command line.
 */
interface ConsoleCommand extends Object
{
    /**
     * runs the command and returns an exit code
     *
     * @return  int
     */
    public function run();
}
?>