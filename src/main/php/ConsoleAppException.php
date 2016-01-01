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
/**
 * Exception for signaling errors on app execution.
 *
 * @since  2.0.0
 */
class ConsoleAppException extends Exception
{
    /**
     * constructor
     *
     * @param  string|callable  $message  failure message or message writer
     * @param  int              $code     return code for application
     * @param  \Exception       $cause    optional  lower level cause for this exception
     */
    public function __construct($message, $code, \Exception $cause = null)
    {
        parent::__construct('*** Exception: ' . $message, $cause, $code);
    }
}
