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
 * Execute commands on the command line.
 *
 * @api
 */
class Executor
{
    /**
     * executes given command
     *
     * If no output stream is passed the output of the command is simply
     * ignored.
     *
     * @param   string                          $command
     * @param   \stubbles\streams\OutputStream  $out       optional  where to write command output to
     * @param   string                          $redirect  optional  how to redirect error output
     * @return  \stubbles\console\Executor
     */
    public function execute($command, OutputStream $out = null, $redirect = '2>&1')
    {
        $pd = popen($command . ' ' . $redirect, 'r');
        if (false === $pd) {
            throw new \RuntimeException('Can not execute ' . $command);
        }

        // must read all output even if we don't need it, otherwise we don't
        // receive a correct return code when closing the process file pointer
        while (!feof($pd) && false !== ($line = fgets($pd, 4096))) {
            if (null !== $out) {
                $out->writeLine(rtrim($line));
            }
        }

        $returnCode = pclose($pd);
        if (0 != $returnCode) {
            throw new \RuntimeException(
                    'Executing command ' . $command . ' failed: #' . $returnCode
            );
        }

        return $this;
    }

    /**
     * executes given command asynchronous
     *
     * The method starts the command, and returns an input stream which can be
     * used to read the output of the command at a later point in time.
     *
     * @param   string  $command
     * @param   string  $redirect  optional  how to redirect error output
     * @return  \stubbles\streams\InputStream
     */
    public function executeAsync($command, $redirect = '2>&1')
    {
        $pd = popen($command . ' ' . $redirect, 'r');
        if (false === $pd) {
            throw new \RuntimeException('Can not execute ' . $command);
        }

        return new CommandInputStream($pd, $command);
    }

    /**
     * executes command directly and returns output as array (each line as one entry)
     *
     * @param   string  $command
     * @param   string  $redirect  optional  how to redirect error output
     * @return  string[]
     */
    public function executeDirect($command, $redirect = '2>&1')
    {
        $pd = popen($command . ' ' . $redirect, 'r');
        if (false === $pd) {
            throw new \RuntimeException('Can not execute ' . $command);
        }

        $result = [];
        while (!feof($pd) && false !== ($line = fgets($pd, 4096))) {
            $result[] = rtrim($line);
        }

        $returnCode = pclose($pd);
        if (0 != $returnCode) {
            throw new \RuntimeException(
                    'Executing command ' . $command
                    . ' failed: #' . $returnCode
            );
        }

        return $result;
    }
}
