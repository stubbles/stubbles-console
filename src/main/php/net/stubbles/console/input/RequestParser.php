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
class RequestParser extends BaseObject
{
    /**
     * Console
     *
     * @type  OutputStream
     */
    private $out;
    /**
     * request instance
     *
     * @type  Request
     */
    private $request;
    /**
     * request broker
     *
     * @type  RequestBrokerFacade
     */
    private $requestBroker;

    /**
     * constructor
     *
     * @param  OutputStream         $out
     * @param  Request              $request
     * @param  RequestBrokerFacade  $requestBroker
     * @Inject
     * @Named{out}('stdout')
     */
    public function __construct(OutputStream $out, Request $request, RequestBrokerFacade $requestBroker)
    {
        $this->out           = $out;
        $this->request       = $request;
        $this->requestBroker = $requestBroker;
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
     * @throws  ConsoleAppException
     */
    public function parseInto($object, $group = null)
    {
        if ($this->request->hasParam('h') || $this->request->hasParam('help')) {
            throw new ConsoleAppException($this->createHelp($object, $group), 0);
        }

        $this->requestBroker->procure($object, $group, function($paramName, $error)
                                                       {
                                                           throw new ConsoleAppException($paramName . ': ' . $error, 10);
                                                       }
        );

        return $object;
    }

    /**
     * prints help to console
     *
     * @param   object  $object
     * @param   string  $group   restrict parsing to given group
     * @return  Closure
     */
    private function createHelp($object, $group)
    {
        $annotations = array();
        foreach ($this->requestBroker->getAnnotations($object, $group) as $requestAnnotation) {
            $name = $requestAnnotation->getName();
            if (strlen($name) === 1) {
                $name = '-' . $name;
            } else {
                $name = '--' . $name;
            }

            $annotations[$name] = $requestAnnotation->getDescription();
        }

        $out = $this->out;
        return function() use ($out, $annotations)
               {
                   $longestName = max(array_map('strlen', array_keys($annotations)));
                   $out->writeLine('Usage: ');
                   foreach ($annotations as $name => $description) {
                       $out->writeLine('   ' . str_pad($name, $longestName) . '   ' . $description);
                   }
               };
    }
}
?>