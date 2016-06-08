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
 * Execute commands on the command line.
 *
 * @api
 */
class Executor
{
    /**
     * executes given command
     *
     * If no callable is passed the output of the command is simply ignored.
     *
     * @param   string    $command
     * @param   callable  $out       optional  callable which will receive each line from the command output
     * @param   string    $redirect  optional  how to redirect error output
     * @return  \stubbles\console\Executor
     */
    public function execute($command, callable $out = null, $redirect = '2>&1')
    {
        foreach ($this->outputOf($command, $redirect) as $line) {
            if (null !== $out) {
                $out($line);
            }
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
        return new CommandInputStream(
                $this->runCommand($command, $redirect),
                $command
        );
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
        return iterator_to_array($this->outputOf($command, $redirect));
    }

    /**
     * returns output from a command as it occurs
     *
     * @param   string  $command
     * @param   string  $redirect  optional  how to redirect error output
     * @return  \Generator
     * @since   6.0.0
     */
    public function outputOf($command, $redirect = '2>&1')
    {
        $pd = $this->runCommand($command, $redirect);
        while (!feof($pd) && false !== ($line = fgets($pd, 4096))) {
            yield rtrim($line);
        }

        $returnCode = pclose($pd);
        if (0 != $returnCode) {
            throw new \RuntimeException(
                    'Executing command ' . $command
                    . ' failed: #' . $returnCode
            );
        }
    }

    /**
     * runs given command and returns a handle to it
     *
     * @param   string  $command
     * @param   string  $redirect  optional  how to redirect error output
     * @return  resource
     * @throws  \RuntimeException
     */
    private function runCommand($command, $redirect = '2>&1')
    {
        $pd = popen($command . ' ' . $redirect, 'r');
        if (false === $pd) {
            throw new \RuntimeException('Can not execute ' . $command);
        }

        return $pd;
    }
}
