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
use net\stubbles\console\ConsoleAppException;
use stubbles\input\console\ConsoleRequest;
use stubbles\input\broker\RequestBrokerFacade;
use stubbles\lang;
use stubbles\lang\reflect\annotation\Annotation;
use stubbles\streams\OutputStream;
/**
 * Interface for command executors.
 *
 * @since  2.0.0
 */
class RequestParser
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
     * @type  ConsoleRequest
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
     * @param  ConsoleRequest       $request
     * @param  RequestBrokerFacade  $requestBroker
     * @Inject
     * @Named{out}('stdout')
     */
    public function __construct(OutputStream $out, ConsoleRequest $request, RequestBrokerFacade $requestBroker)
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
                                                           throw new ConsoleAppException(function(OutputStream $err) use($error)
                                                                                         {
                                                                                             $err->writeLine($error);
                                                                                         },
                                                                                         10
                                                           );
                                                       }
        );

        if (method_exists($object, 'finalizeInput')) {
            $object->finalizeInput();
        }

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
        $options    = [];
        $parameters = [];
        foreach ($this->requestBroker->getAnnotations($object, $group) as $requestAnnotation) {
            if (substr($requestAnnotation->getName(), 0, 5) !== 'argv.') {
                $options[$this->getOptionName($requestAnnotation)] = $requestAnnotation->getDescription();
            } elseif (!$requestAnnotation->isRequired()) {
                $parameters[] = '[' . $requestAnnotation->getDescription() . ']';
            } else {
                $parameters[] = $requestAnnotation->getDescription();
            }
        }

        $options['-h or --help'] = 'Prints this help.';
        return $this->creatHelpWriter($this->getAppDescription($object),
                                      $this->out,
                                      $this->request->readEnv('SCRIPT_NAME')->unsecure(),
                                      $options,
                                      $parameters
        );
    }

    /**
     * retrieves app description for given object
     *
     * @param   object  $object
     * @return  string
     */
    private function getAppDescription($object)
    {
        $class = lang\reflect($object);
        if (!$class->hasAnnotation('AppDescription')) {
            return null;
        }

        return $class->getAnnotation('AppDescription')->getValue();
    }

    /**
     * retrieves name of option
     *
     * @param   Annotation  $requestAnnotation
     * @return  string
     */
    private function getOptionName(Annotation $requestAnnotation)
    {
        if ($requestAnnotation->hasOption()) {
            return $requestAnnotation->getOption();
        }

        $name = $requestAnnotation->getName();
        if (strlen($name) === 1) {
            return '-' . $name;
        }

        return '--' . $name;
    }

    /**
     * creates help writing closure
     *
     * @param   string        $appDescription
     * @param   OutputStream  $out
     * @param   string        $scriptName
     * @param   array         $options
     * @param   array         $parameters
     * @return  Closure
     */
    private function creatHelpWriter($appDescription, OutputStream $out, $scriptName, array $options, array $parameters)
    {
        return function() use ($appDescription, $out, $scriptName, $options, $parameters)
               {
                   if (!empty($appDescription)) {
                       $out->writeLine($appDescription);
                   }

                   $out->write('Usage: ' . $scriptName . ' [options]');
                   foreach ($parameters as $type) {
                       $out->write(' ' . $type);
                   }

                   $out->writeLine('');
                   $longestName = max(array_map('strlen', array_keys($options)));
                   $out->writeLine('Options:');
                   foreach ($options as $name => $description) {
                       $out->writeLine('   ' . str_pad($name, $longestName) . '   ' . $description);
                   }

                   $out->writeLine('');
               };
    }
}
