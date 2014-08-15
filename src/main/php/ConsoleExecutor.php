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
 * Class to execute commands on the command line.
 */
class ConsoleExecutor implements Executor
{
    /**
     * output stream to write data outputted by executed command to
     *
     * @type  \stubbles\streams\OutputStream
     */
    private $out;
    /**
     * redirect direction
     *
     * @type  string
     */
    private $redirect = '2>&1';

    /**
     * sets the output stream to write data outputted by executed command to
     *
     * @param   \stubbles\streams\OutputStream  $out
     * @return  \stubbles\console\Executor
     */
    public function streamOutputTo(OutputStream $out)
    {
        $this->out = $out;
        return $this;
    }

    /**
     * returns the output stream to write data outputted by executed command to
     *
     * @return  \stubbles\streams\OutputStream
     */
    public function out()
    {
        return $this->out;
    }

    /**
     * returns the output stream to write data outputted by executed command to
     *
     * @return  \stubbles\streams\OutputStream
     * @deprecated  since 3.0.0, use out() instead, will be removed with 4.0.0
     */
    public function getOutputStream()
    {
        return $this->out();
    }

    /**
     * sets the redirect
     *
     * @param   string  $redirect
     * @return  \stubbles\console\Executor
     */
    public function redirectTo($redirect)
    {
        $this->redirect = $redirect;
        return $this;
    }

    /**
     * executes given command
     *
     * @param   string  $command
     * @return  \stubbles\console\Executor
     * @throws  \RuntimeException
     */
    public function execute($command)
    {
        $pd = popen($command . ' ' . $this->redirect, 'r');
        if (false === $pd) {
            throw new \RuntimeException('Can not execute ' . $command);
        }

        // must read all output even if we don't need it, otherwise we don't
        // receive a correct return code when closing the process file pointer
        while (!feof($pd) && false !== ($line = fgets($pd, 4096))) {
            if (null !== $this->out) {
                $this->out->writeLine(rtrim($line));
            }
        }

        $returnCode = pclose($pd);
        if (0 != $returnCode) {
            throw new \RuntimeException('Executing command ' . $command . ' failed: #' . $returnCode);
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
     * @return  \stubbles\streams\InputStream
     * @throws  \RuntimeException
     */
    public function executeAsync($command)
    {
        $pd = popen($command . ' ' . $this->redirect, 'r');
        if (false === $pd) {
            throw new \RuntimeException('Can not execute ' . $command);
        }

        return new CommandInputStream($pd, $command);
    }

    /**
     * executes command directly and returns output as array (each line as one entry)
     *
     * @param   string  $command
     * @return  string[]
     * @throws  \RuntimeException
     */
    public function executeDirect($command)
    {
        $pd = popen($command . ' ' . $this->redirect, 'r');
        if (false === $pd) {
            throw new \RuntimeException('Can not execute ' . $command);
        }

        $result = [];
        while (!feof($pd) && false !== ($line = fgets($pd, 4096))) {
            $result[] = rtrim($line);
        }

        $returnCode = pclose($pd);
        if (0 != $returnCode) {
            throw new \RuntimeException('Executing command ' . $command . ' failed: #' . $returnCode);
        }

        return $result;
    }
}
