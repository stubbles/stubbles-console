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
use stubbles\streams\memory\MemoryOutputStream;
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
     * @type  Closure
     */
    private $messenger;

    /**
     * constructor
     *
     * @param  string|Closure   $message  failure message
     * @param  int              $code     return code for application
     * @param  \Exception       $cause
     */
    public function __construct($message, $code, \Exception $cause = null)
    {
        if ($message instanceof \Closure) {
            $out = new MemoryOutputStream();
            $message($out);
            parent::__construct($out->getBuffer(), $cause, $code);
            $this->messenger = $message;
        } else {
            parent::__construct($message, $cause, $code);
        }
    }

    /**
     * returns messenger
     *
     * @return  \Closure
     */
    public function getMessenger()
    {
        if (null !== $this->messenger) {
            return $this->messenger;
        }

        return function(OutputStream $out)
               {
                   return $out->writeLine('*** Exception: ' . $this->getMessage());
               };
    }
}
