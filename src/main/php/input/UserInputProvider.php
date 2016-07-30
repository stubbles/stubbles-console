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
use stubbles\ioc\InjectionProvider;
use stubbles\ioc\Injector;
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
     * @type  \stubbles\console\input\RequestParser
     */
    private $requestParser;
    /**
     * injector to create input class with
     *
     * @type  \stubbles\ioc\Injector
     */
    private $injector;

    /**
     * constructor
     *
     * @param  \stubbles\console\input\RequestParser  $requestParser
     * @param  \stubbles\ioc\Injector                 $injector
     * @param  string                                 $userInputClass
     * @Named{userInputClass}('stubbles.console.input.class')
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
    public function get(string $name = null)
    {
        return $this->requestParser->parseInto(
                $this->injector->getInstance(
                        $this->userInputClass,
                        'stubbles.console.input.instance'
                ),
                $name
        );
    }
}
