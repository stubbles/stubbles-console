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
use stubbles\input\AbstractRequest;
use stubbles\input\Params;
use stubbles\input\ValueReader;
use stubbles\input\ValueValidator;
/**
 * Request implementation for command line.
 *
 * @api
 * @since  2.0.0
 */
class BaseConsoleRequest extends AbstractRequest implements ConsoleRequest
{
    /**
     * list of environment variables
     *
     * @type  \stubbles\input\Params
     */
    private $env;

    /**
     * constructor
     *
     * @param  array  $params
     * @param  array  $env
     */
    public function __construct(array $params, array $env)
    {
        parent::__construct(new Params($params));
        $this->env = new Params($env);
    }

    /**
     * creates an instance from raw data
     *
     * Will use $_SERVER['argv'] for params and $_SERVER for env.
     *
     * @api
     * @return  \stubbles\input\console\ConsoleRequest
     */
    public static function fromRawSource()
    {
        return new self($_SERVER['argv'], $_SERVER);
    }

    /**
     * returns the request method
     *
     * @return  string
     */
    public function method()
    {
        return 'cli';
    }

    /**
     * return a list of all environment names registered in this request
     *
     * @return  string[]
     */
    public function envNames()
    {
        return $this->env->names();
    }

    /**
     * returns list of errors for environment parameters
     *
     * @return  \stubbles\input\ParamErrors
     */
    public function envErrors()
    {
        return $this->env->errors();
    }

    /**
     * checks whether a request param is set
     *
     * @param   string  $envName
     * @return  bool
     */
    public function hasEnv($envName)
    {
        return $this->env->contain($envName);
    }

    /**
     * checks whether a request value from parameters is valid or not
     *
     * @param   string  $envName  name of environment value
     * @return  \stubbles\input\ValueValidator
     */
    public function validateEnv($envName)
    {
        return new ValueValidator($this->env->value($envName));
    }

    /**
     * returns request value from params for validation
     *
     * @param   string  $envName  name of environment value
     * @return  \stubbles\input\ValueReader
     */
    public function readEnv($envName)
    {
        return new ValueReader(
                $this->env->errors(),
                $this->env->get($envName)
        );
    }
}
