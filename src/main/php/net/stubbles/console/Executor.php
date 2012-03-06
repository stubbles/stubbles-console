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
use net\stubbles\streams\OutputStream;
/**
 * Interface for command executors.
 *
 * @api
 */
interface Executor extends Object
{
    /**
     * sets the output stream to write data outputted by executed command to
     *
     * @param   OutputStream  $out
     * @return  Executor
     */
    public function streamOutputTo(OutputStream $out);

    /**
     * returns the output stream to write data outputted by executed command to
     *
     * @return  OutputStream
     */
    public function getOutputStream();

    /**
     * executes given command
     *
     * @param   string  $command
     * @return  Executor
     */
    public function execute($command);

    /**
     * executes given command asynchronous
     *
     * The method starts the command, and returns an input stream which can be
     * used to read the output of the command at a later point in time.
     *
     * @param   string  $command
     * @return  InputStream
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
?>