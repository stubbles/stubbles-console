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
use stubbles\input\Request;
use stubbles\input\broker\RequestBroker;
use stubbles\ioc\Binder;
use stubbles\ioc\module\BindingModule;
/**
 * Binding module to configure the binder with arguments.
 */
class ArgumentParser implements BindingModule
{
    /**
     * options to be used for parsing the arguments
     *
     * @type  string
     */
    private $options   = null;
    /**
     * long options to be used for parsing the arguments
     *
     * @type  string[]
     */
    private $longopts  = [];
    /**
     * name of user input class
     *
     * @type  string
     */
    private $userInput = null;
    /**
     * callable to parse command line options with
     *
     * @type   callable
     * @since  7.0.0
     */
    private $cliOptionParser = 'getopt';

    /**
     * sets callable which will be used to parse command line options with
     *
     * @param   callable  $getopt
     * @return  \stubbles\console\input\ArgumentParser
     * @since   7.0.0
     */
    public function withCliOptionParser(callable $getopt): self
    {
        $this->cliOptionParser = $getopt;
        return $this;
    }

    /**
     * sets the options to be used for parsing the arguments
     *
     * @api
     * @param   string  $options
     * @return  \stubbles\console\input\ArgumentParser
     */
    public function withOptions(string $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * sets the long options to be used for parsing the arguments
     *
     * @api
     * @param   string[]  $options
     * @return  \stubbles\console\input\ArgumentParser
     */
    public function withLongOptions(array $options): self
    {
        $this->longopts = $options;
        return $this;
    }

    /**
     * sets class to store user input into
     *
     * @param   string  $className
     * @return  \stubbles\console\input\ArgumentParser
     */
    public function withUserInput(string $className): self
    {
        $this->userInput = $className;
        return $this;
    }

    /**
     * configure the binder
     *
     * @param  \stubbles\ioc\Binder  $binder
     * @param  string                $projectPath  optional  project base path
     */
    public function configure(Binder $binder, string $projectPath = null)
    {
        $args = $this->parseArgs();
        $binder->bindConstant('argv')
               ->to($args);
        foreach ($args as $name => $value) {
            if (substr($name, 0, 5) !== 'argv.') {
                $name = 'argv.' . $name;
            }

            $binder->bindConstant($name)
                   ->to($value);
        }

        $request = new ConsoleRequest($args, $_SERVER);
        $binder->bind(Request::class)
               ->toInstance($request);
        $binder->bind(ConsoleRequest::class)
               ->toInstance($request);
        if (null !== $this->userInput) {
            $binder->bind($this->userInput)
                   ->toProviderClass(UserInputProvider::class)
                   ->asSingleton();
            $binder->bind($this->userInput)
                   ->named('stubbles.console.input.instance')
                   ->to($this->userInput);
            $binder->bindConstant('stubbles.console.input.class')
                   ->to($this->userInput);
        }
    }

    /**
     * returns parsed arguments
     *
     * @return  array
     * @throws  \RuntimeException
     */
    private function parseArgs(): array
    {
        if (null === $this->options && count($this->longopts) === 0 && null === $this->userInput) {
            return $this->fixArgs($_SERVER['argv']);
        }

        if (null !== $this->userInput) {
            $this->collectOptionsFromUserInputClass();
        }

        $parseCommandLineOptions = $this->cliOptionParser;
        $parsedVars = $parseCommandLineOptions($this->options, $this->longopts);
        if (false === $parsedVars) {
            throw new \RuntimeException(
                    'Error parsing "' . join(' ', $_SERVER['argv'])
                    . '" with ' . $this->options
                    . ' and ' . join(' ', $this->longopts)
            );
        }

        return $this->fixArgs($_SERVER['argv'], $parsedVars);
    }

    /**
     * retrieves list of arguments
     *
     * @param   array  $args
     * @param   array  $parsedVars
     * @return  array
     */
    private function fixArgs(array $args, array $parsedVars = []): array
    {
        array_shift($args); // script name
        $vars     = [];
        $position = 0;
        foreach ($args as $arg) {
            if (isset($parsedVars[substr($arg, 1)]) || isset($parsedVars[substr($arg, 2)]) || in_array($arg, $parsedVars)) {
                continue;
            }

            $vars['argv.' . $position] = $arg;
            $position++;
        }

        return array_merge($vars, $parsedVars);
    }

    /**
     * parses options from user input class
     */
    private function collectOptionsFromUserInputClass()
    {
        foreach (RequestBroker::targetMethodsOf($this->userInput) as $targetMethod) {
            $name = $targetMethod->paramName();
            if (substr($name, 0, 5) === 'argv.') {
                continue;
            }

            if (strlen($name) === 1) {
                $this->options .= $name . ($targetMethod->requiresParameter() ? ':' : '');
            } else {
                $this->longopts[] = $name . ($targetMethod->requiresParameter() ? ':' : '');
            }
        }

        if (null === $this->options || !strpos($this->options, 'h')) {
            $this->options .= 'h';
        }

        if (!in_array('help', $this->longopts)) {
            $this->longopts[] = 'help';
        }
    }
}
