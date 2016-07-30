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

use function stubbles\values\typeOf;
/**
 * creates a callable to collect output in given reference
 *
 * @param   string|array  &$out  something which will receive each line from the command output
 * @return  callable
 * @throws  \InvalidArgumentException  in case $out is neither a string nor an array
 * @since   6.1.0
 */
function collect(&$out): callable
{
    if (is_string($out)) {
        return function($line) use(&$out) { $out .= $line . PHP_EOL; };
    } elseif (is_array($out)) {
        return function($line) use(&$out) { $out[] = $line; };
    }

    throw new \InvalidArgumentException(
            'Parameter $out must be a string or an array, but was of type '
             . typeOf($out)
    );
}

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
     * @param   string    $command
     * @param   callable  $collect   optional  callable which will receive each line from the command output
     * @param   string    $redirect  optional  how to redirect error output
     * @return  \stubbles\console\Executor
     */
    public function execute(string $command, callable $collect = null, string $redirect = '2>&1'): self
    {
        foreach ($this->outputOf($command, $redirect) as $line) {
            if (null !== $collect) {
                $collect($line);
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
    public function executeAsync(string $command, string $redirect = '2>&1'): InputStream
    {
        return new CommandInputStream(
                $this->runCommand($command, $redirect),
                $command
        );
    }

    /**
     * executes command directly and returns output as array (each line as one entry)
     *
     * In case you only want to iterate over the output you should use outputOf()
     * instead as it will yield a line as soon as it occurs, whereas this method
     * waits until the command finishes until it returns all output lines at once.
     *
     * @param   string  $command
     * @param   string  $redirect  optional  how to redirect error output
     * @return  string[]
     * @deprecated  since 6.1.0, use $executor->execute($command, collect($array)) or iterator_to_array($executor->outputOf($command)) instead, will be removed with 7.0.0
     */
    public function executeDirect(string $command, string $redirect = '2>&1'): array
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
    public function outputOf(string $command, string $redirect = '2>&1'): \Generator
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
    private function runCommand(string $command, string $redirect = '2>&1')
    {
        $pd = popen($command . ' ' . $redirect, 'r');
        if (false === $pd) {
            throw new \RuntimeException('Can not execute ' . $command);
        }

        return $pd;
    }
}
