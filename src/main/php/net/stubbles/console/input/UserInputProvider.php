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
use net\stubbles\ioc\Injector;
/**
 * Interface for command executors.
 *
 * @since  2.0.0
 */
class UserInputProvider implements InjectionProvider
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
     * injector to create input class with
     *
     * @type  Injector
     */
    private $injector;

    /**
     * constructor
     *
     * @param  RequestParser  $requestParser
     * @param  Injector       $injector
     * @param  string         $userInputClass
     * @Inject
     * @Named{userInputClass}('net.stubbles.console.input.class')
     */
    public function __construct(RequestParser $requestParser, Injector $injector, $userInputClass)
    {
        $this->requestParser  = $requestParser;
        $this->injector       = $injector;
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
        return $this->requestParser->parseInto($this->injector->getInstance($this->userInputClass,
                                                                            'net.stubbles.console.input.instance'
                                               ),
                                               $name
        );
    }
}
?>