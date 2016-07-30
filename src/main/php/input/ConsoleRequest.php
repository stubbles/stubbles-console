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
use stubbles\input\{
    ParamRequest,
    Params,
    ValueReader,
    ValueValidator,
    errors\ParamErrors
};
/**
 * Interface for command line requests.
 *
 * @api
 * @since  2.0.0
 */
class ConsoleRequest extends ParamRequest
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
        parent::__construct($params);
        $this->env = new Params($env);
    }

    /**
     * creates an instance from raw data
     *
     * Will use $_SERVER['argv'] for params and $_SERVER for env.
     *
     * @api
     * @return  \stubbles\console\input\ConsoleRequest
     */
    public static function fromRawSource(): self
    {
        return new self($_SERVER['argv'], $_SERVER);
    }

    /**
     * returns the request method
     *
     * @return  string
     */
    public function method(): string
    {
        return 'cli';
    }

    /**
     * return a list of all environment names registered in this request
     *
     * @return  string[]
     */
    public function envNames(): array
    {
        return $this->env->names();
    }

    /**
     * returns list of errors for environment parameters
     *
     * @return  \stubbles\input\errors\ParamErrors
     */
    public function envErrors(): ParamErrors
    {
        return $this->env->errors();
    }

    /**
     * checks whether a request param is set
     *
     * @param   string  $envName
     * @return  bool
     */
    public function hasEnv(string $envName): bool
    {
        return $this->env->contain($envName);
    }

    /**
     * checks whether a request value from parameters is valid or not
     *
     * @param   string  $envName  name of environment value
     * @return  \stubbles\input\ValueValidator
     */
    public function validateEnv(string $envName): ValueValidator
    {
        return new ValueValidator($this->env->value($envName));
    }

    /**
     * returns request value from params for validation
     *
     * @param   string  $envName  name of environment value
     * @return  \stubbles\input\ValueReader
     */
    public function readEnv(string $envName): ValueReader
    {
        return new ValueReader(
                $this->env->errors(),
                $envName,
                $this->env->value($envName)
        );
    }
}
