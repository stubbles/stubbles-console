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
use stubbles\lang\exception\Exception;
use stubbles\streams\OutputStream;
/**
 * Exception for signaling errors on app execution.
 *
 * @since  2.0.0
 */
class ConsoleAppException extends Exception
{
    /**
     * closure
     *
     * @type  callable
     */
    private $messenger;

    /**
     * constructor
     *
     * @param  string|callable  $message  failure message or message writer
     * @param  int              $code     return code for application
     * @param  \Exception       $cause    optional  lower level cause for this exception
     */
    public function __construct($message, $code, \Exception $cause = null)
    {
        parent::__construct($message, $cause, $code);
    }

    /**
     * writes error message to given output stream
     *
     * @param   \stubbles\streams\OutputStream  $out  stream to write message to
     */
    public function writeTo(OutputStream $out)
    {
        if (null === $this->messenger) {
            $out->writeLine('*** Exception: ' . $this->getMessage());
            return;
        }

        $writeMessage = $this->messenger;
        $writeMessage($out);
    }
}
