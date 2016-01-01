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
 * Interface for command executors.
 *
 * @api
 */
interface Executor
{
    /**
     * executes given command
     *
     * If no output stream is passed the output of the command is simply
     * ignored.
     *
     * @param   string                          $command
     * @param   \stubbles\streams\OutputStream  $out      optional  where to write command output to
     * @return  \stubbles\console\Executor
     */
    public function execute($command, OutputStream $out = null);

    /**
     * executes given command asynchronous
     *
     * The method starts the command, and returns an input stream which can be
     * used to read the output of the command at a later point in time.
     *
     * @param   string  $command
     * @return  \stubbles\streams\InputStream
     */
    public function executeAsync($command);

    /**
     * executes command directly and returns output as array (each line as one entry)
     *
     * @param   string  $command
     * @return  string[]
     */
    public function executeDirect($command);
}
