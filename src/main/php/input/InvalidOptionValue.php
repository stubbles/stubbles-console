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
namespace stubbles\console\input;
use stubbles\input\errors\ParamErrors;
use stubbles\input\errors\messages\ParamErrorMessages;
/**
 * Exception to be thrown when an option value or an argument is invalid.
 *
 * @since  6.0.0
 */
class InvalidOptionValue extends \Exception
{
    /**
     * constructor
     *
     * @param  \stubbles\input\errors\ParamErrors                  $paramErrors
     * @param  \stubbles\input\errors\messages\ParamErrorMessages  $messages
     */
    public function __construct(ParamErrors $paramErrors, ParamErrorMessages $messages)
    {
        $message = [];
        foreach ($paramErrors as $paramName => $errors) {
            $name = substr($paramName, 0, 5) !== 'argv.' ? ($paramName . ': ') : '';
            foreach ($errors as $error) {
                $message[] = $name . $messages->messageFor($error);
            }
        }

        parent::__construct(join("\n", $message));
    }
}
