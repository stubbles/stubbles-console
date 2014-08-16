<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\ioc;
use stubbles\input\broker\RequestBroker;
use stubbles\input\console\BaseConsoleRequest;
use stubbles\ioc\Binder;
use stubbles\ioc\module\BindingModule;
/**
 * Binding module to configure the binder with arguments.
 */
class ArgumentParser implements BindingModule
{
    /**
     * switch whether stubcli was used to run the command
     *
     * @type  bool
     */
    private $stubcliUsed;
    /**
     * options to be used for parsing the arguments
     *
     * @type  string
     */
    protected $options   = null;
    /**
     * long options to be used for parsing the arguments
     *
     * @type  string[]
     */
    protected $longopts  = [];
    /**
     * name of user input class
     *
     * @type  string
     */
    private $userInput   = null;

    /**
     * constructor
     *
     * @param  bool  $stubcliUsed  switch whether stubcli was used to run the command
     */
    public function __construct($stubcliUsed = false)
    {
        $this->stubcliUsed = $stubcliUsed;
    }

    /**
     * sets the options to be used for parsing the arguments
     *
     * @api
     * @param   string  $options
     * @return  \stubbles\console\ioc\ArgumentParser
     */
    public function withOptions($options)
    {
        $this->options = $options;
        if ($this->stubcliUsed && !strstr($options, 'c')) {
            $this->options .= 'c:';
        }

        return $this;
    }

    /**
     * sets the long options to be used for parsing the arguments
     *
     * @api
     * @param   string[]  $options
     * @return  \stubbles\console\ioc\ArgumentParser
     */
    public function withLongOptions(array $options)
    {
        if ($this->stubcliUsed && null === $this->options) {
            $this->options = 'c:';
        }

        $this->longopts = $options;
        return $this;
    }

    /**
     * sets class to store user input into
     *
     * @param   string  $className
     * @return  \stubbles\console\ioc\ArgumentParser
     */
    public function withUserInput($className)
    {
        $this->userInput = $className;
        return $this;
    }

    /**
     * configure the binder
     *
     * @param  \stubbles\ioc\Binder  $binder
     */
    public function configure(Binder $binder)
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

        $request = new BaseConsoleRequest($args, $_SERVER);
        $binder->bind('stubbles\input\Request')
               ->toInstance($request);
        $binder->bind('stubbles\input\console\ConsoleRequest')
               ->toInstance($request);
        if (null !== $this->userInput) {
            $binder->bind($this->userInput)
                   ->toProviderClass('stubbles\console\input\UserInputProvider')
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
    private function parseArgs()
    {
        if (null === $this->options && count($this->longopts) === 0 && null === $this->userInput) {
            return $this->fixArgs($_SERVER['argv']);
        }

        $this->parseOptions();
        $parsedVars = $this->getopt($this->options, $this->longopts);
        if (false === $parsedVars) {
            throw new \RuntimeException('Error parsing "' . join(' ', $_SERVER['argv']) . '" with ' . $this->options . ' and ' . join(' ', $this->longopts));
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
    private function fixArgs(array $args, array $parsedVars = [])
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
    private function parseOptions()
    {
        if (null === $this->userInput) {
            return;
        }

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

        if (!strpos($this->options, 'h')) {
            $this->options .= 'h';
        }

        if (!in_array('help', $this->longopts)) {
            $this->longopts[] = 'help';
        }
    }

    /**
     * helper method to enable proper testing, as PHP's getopt() is not mockable
     *
     * @param   string    $options   options to be used for parsing the arguments
     * @param   string[]  $longopts  long options to be used for parsing the arguments
     * @return  array
     */
    protected function getopt($options, array $longopts)
    {
        return getopt($options, $longopts);
    }
}
