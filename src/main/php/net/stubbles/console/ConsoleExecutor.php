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
use net\stubbles\lang\BaseObject;
use net\stubbles\lang\exception\RuntimeException;
use net\stubbles\streams\OutputStream;
/**
 * Class to execute commands on the command line.
 */
class ConsoleExecutor extends BaseObject implements Executor
{
    /**
     * output stream to write data outputted by executed command to
     *
     * @type  OutputStream
     */
    protected $out;
    /**
     * redirect direction
     *
     * @type  string
     */
    protected $redirect = '2>&1';

    /**
     * sets the output stream to write data outputted by executed command to
     *
     * @param   OutputStream  $out
     * @return  Executor
     */
    public function streamOutputTo(OutputStream $out)
    {
        $this->out = $out;
        return $this;
    }

    /**
     * returns the output stream to write data outputted by executed command to
     *
     * @return  OutputStream
     */
    public function getOutputStream()
    {
        return $this->out;
    }

    /**
     * sets the redirect
     *
     * @param   string  $redirect
     * @return  Executor
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
     * @return  Executor
     * @throws  RuntimeException
     */
    public function execute($command)
    {
        $pd = popen($command . ' ' . $this->redirect, 'r');
        if (false === $pd) {
            throw new RuntimeException('Can not execute ' . $command);
        }

        while (!feof($pd) && false !== ($line = fgets($pd, 4096))) {
            $line = chop($line);
            if (null !== $this->out) {
                $this->out->writeLine($line);
            }
        }

        $returnCode = pclose($pd);
        if (0 != $returnCode) {
            throw new RuntimeException('Executing command ' . $command . ' failed: #' . $returnCode);
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
     * @return  InputStream
     * @throws  RuntimeException
     */
    public function executeAsync($command)
    {
        $pd = popen($command . ' ' . $this->redirect, 'r');
        if (false === $pd) {
            throw new RuntimeException('Can not execute ' . $command);
        }

        return new CommandInputStream($pd, $command);
    }

    /**
     * executes command directly and returns output as array (each line as one entry)
     *
     * @param   string  $command
     * @return  string[]
     * @throws  RuntimeException
     */
    public function executeDirect($command)
    {
        $pd = popen($command . ' ' . $this->redirect, 'r');
        if (false === $pd) {
            throw new RuntimeException('Can not execute ' . $command);
        }

        $result = array();
        while (!feof($pd) && false !== ($line = fgets($pd, 4096))) {
            $result[] = chop($line);
        }

        $returnCode = pclose($pd);
        if (0 != $returnCode) {
            throw new RuntimeException('Executing command ' . $command . ' failed: #' . $returnCode);
        }

        return $result;
    }
}
?>