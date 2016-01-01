<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\input;
use stubbles\console\ConsoleAppException;
use stubbles\input\console\ConsoleRequest;
use stubbles\input\broker\RequestBroker;
use stubbles\input\errors\messages\ParamErrorMessages;
/**
 * Interface for command executors.
 *
 * @since  2.0.0
 */
class RequestParser
{
    /**
     * request instance
     *
     * @type  \stubbles\input\console\ConsoleRequest
     */
    private $request;
    /**
     * request broker
     *
     * @type  \stubbles\input\broker\RequestBrokerFacade
     */
    private $requestBroker;
    /**
     * access to error messages
     *
     * @type  \stubbles\input\errors\ParamErrorMessages
     */
    private $errorMessages;

    /**
     * constructor
     *
     * @param  \stubbles\input\console\ConsoleRequest     $request
     * @param  \stubbles\input\broker\RequestBroker       $requestBroker
     * @param  \stubbles\input\errors\ParamErrorMessages  $errorMessages
     */
    public function __construct(
            ConsoleRequest $request,
            RequestBroker $requestBroker,
            ParamErrorMessages $errorMessages)
    {
        $this->request       = $request;
        $this->requestBroker = $requestBroker;
        $this->errorMessages = $errorMessages;
    }

    /**
     * parses request data into given class and returns an instance of it
     *
     * Prints help for given class when -h or --help param is set.
     *
     * @param   string  $class
     * @param   string  $group   restrict parsing to given group
     * @return  object
     */
    public function parseTo($class, $group = null)
    {
        return $this->parseInto(new $class(), $group);
    }

    /**
     * parses request data into given object
     *
     * Prints help for given object when -h or --help param is set.
     *
     * @param   object  $object
     * @param   string  $group   restrict parsing to given group
     * @return  object
     * @throws  \stubbles\console\input\HelpScreen
     * @throws  \stubbles\console\input\InvalidOptionValue
     */
    public function parseInto($object, $group = null)
    {
        if ($this->request->hasParam('h') || $this->request->hasParam('help')) {
            throw new HelpScreen(
                    $this->request->readEnv('SCRIPT_NAME')->unsecure(),
                    $object,
                    $group
            );
        }

        $this->requestBroker->procure($this->request, $object, $group);
        if ($this->request->paramErrors()->exist()) {
            throw new InvalidOptionValue(
                    $this->request->paramErrors(),
                    $this->errorMessages
            );
        }

        if (method_exists($object, 'finalizeInput')) {
            $object->finalizeInput();
        }

        return $object;
    }
}
