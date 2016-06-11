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
use stubbles\input\Request;
/**
 * Interface for command line requests.
 *
 * @api
 * @since  2.0.0
 */
interface ConsoleRequest extends Request
{
    /**
     * return a list of all environment names registered in this request
     *
     * @return  string[]
     */
    public function envNames();

    /**
     * returns list of errors for environment parameters
     *
     * @return  \stubbles\input\errors\ParamErrors
     */
    public function envErrors();

    /**
     * checks whether a request param is set
     *
     * @param   string  $envName
     * @return  bool
     */
    public function hasEnv($envName);

    /**
     * checks whether a request value from parameters is valid or not
     *
     * @param   string  $envName  name of environment value
     * @return  \stubbles\input\ValueValidator
     */
    public function validateEnv($envName);

    /**
     * returns request value from params for validation
     *
     * @param   string  $envName  name of environment value
     * @return  \stubbles\input\ValueReader
     */
    public function readEnv($envName);
}
