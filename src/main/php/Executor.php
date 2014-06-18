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
    public function out();

    /**
     * returns the output stream to write data outputted by executed command to
     *
     * @return  OutputStream
     * @deprecated  since 3.0.0, use out() instead, will be removed with 4.0.0
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
