<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
namespace net\stubbles\console\input;
use net\stubbles\ioc\InjectionProvider;
use net\stubbles\console\Console;
use net\stubbles\console\ConsoleAppException;
use net\stubbles\input\Request;
use net\stubbles\input\broker\RequestBrokerFacade;
use net\stubbles\lang\BaseObject;
use net\stubbles\streams\OutputStream;
/**
 * Interface for command executors.
 *
 * @since  2.0.0
 */
class UserInputProvider extends BaseObject implements InjectionProvider
{
    /**
     * user input class to provide
     *
     * @type  string
     */
    private $userInputClass;
    /**
     * request parser to be used
     *
     * @type  RequestParser
     */
    private $requestParser;

    /**
     * constructor
     *
     * @param  RequestParser  $requestParser
     * @param  string         $userInputClass
     * @Inject
     * @Named{userInputClass}('net.stubbles.console.input.class')
     */
    public function __construct(RequestParser $requestParser, $userInputClass)
    {
        $this->requestParser  = $requestParser;
        $this->userInputClass = $userInputClass;
    }

    /**
     * returns the value to provide
     *
     * @param   string  $name
     * @return  object
     */
    public function get($name = null)
    {
        return $this->requestParser->parseTo($this->userInputClass, $name);
    }
}
?>