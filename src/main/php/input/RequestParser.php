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
use stubbles\input\broker\TargetMethod;
use stubbles\input\errors\messages\ParamErrorMessages;
use stubbles\lang\reflect;
use stubbles\streams\OutputStream;
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
     * @throws  \stubbles\console\ConsoleAppException
     */
    public function parseInto($object, $group = null)
    {
        if ($this->request->hasParam('h') || $this->request->hasParam('help')) {
            throw new ConsoleAppException($this->createHelp($object, $group), 0);
        }

        $this->requestBroker->procure($this->request, $object, $group);
        if ($this->request->paramErrors()->exist()) {
            throw new ConsoleAppException(
                    function(OutputStream $err)
                    {
                        foreach ($this->request->paramErrors() as $paramName => $errors) {
                            $name = substr($paramName, 0, 5) !== 'argv.' ? ($paramName . ': ') : '';
                            foreach ($errors as $error) {
                                $err->writeLine($name . $this->errorMessages->messageFor($error));
                            }
                        }

                    },
                    10
            );
        }

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
     * @return  \Closure
     */
    private function createHelp($object, $group)
    {
        $options    = [];
        $parameters = [];
        foreach (RequestBroker::targetMethodsOf($object, $group) as $targetMethod) {
            if (substr($targetMethod->paramName(), 0, 5) !== 'argv.') {
                $options[$this->getOptionName($targetMethod)] = $targetMethod->paramDescription();
            } elseif (!$targetMethod->isRequired()) {
                $parameters[$targetMethod->paramName()] = '[' . $targetMethod->paramDescription() . ']';
            } else {
                $parameters[$targetMethod->paramName()] = $targetMethod->paramDescription();
            }
        }

        $options['-h or --help'] = 'Prints this help.';
        asort($parameters);
        return $this->creatHelpWriter(
                $this->readAppDescription($object),
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
    private function readAppDescription($object)
    {
        $annotations = reflect\annotationsOf($object);
        if (!$annotations->contain('AppDescription')) {
            return null;
        }

        return $annotations->firstNamed('AppDescription')->getValue();
    }

    /**
     * retrieves name of option
     *
     * @param   \stubbles\input\broker\TargetMethod  $targetMethod
     * @return  string
     */
    private function getOptionName(TargetMethod $targetMethod)
    {
        $name   = $targetMethod->paramName();
        $prefix = strlen($name) === 1 ? '-' : '--';
        $suffix = $targetMethod->requiresParameter() ? ' ' . $targetMethod->valueDescription() : '';
        return $prefix . $name . $suffix;
    }

    /**
     * creates help writing closure
     *
     * @param   string  $appDescription
     * @param   string  $scriptName
     * @param   array   $options
     * @param   array   $parameters
     * @return  \Closure
     */
    private function creatHelpWriter($appDescription, $scriptName, array $options, array $parameters)
    {
        return function(OutputStream $out) use ($appDescription, $scriptName, $options, $parameters)
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
                       $out->writeLine('   ' . trim(str_pad($name, $longestName) . '   ' . $description));
                   }

                   $out->writeLine('');
               };
    }
}
