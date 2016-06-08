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
use stubbles\input\broker\RequestBroker;
use stubbles\input\broker\TargetMethod;

use function stubbles\reflect\annotationsOf;
/**
 * Helpscreen to be displayed when script usage is requested.
 *
 * @since  6.0.0
 */
class HelpScreen extends \Exception
{
    /**
     * constructor
     *
     * @param   string  $scriptName  name of script to print help for
     * @param   object  $object
     * @param   string  $group       optional  restrict parsing to given group
     * @return  \Closure
     */
    public function __construct($scriptName, $object, $group = null)
    {
        $help  = $this->readAppDescription($object);
        $help .= 'Usage: ' . $scriptName . ' [options]';
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
        foreach ($parameters as $type) {
            $help .= ' ' . $type;
        }

        $help .= "\nOptions:\n";
        $longestName = max(array_map('strlen', array_keys($options)));
        foreach ($options as $name => $description) {
            $help .= '   ' . trim(str_pad($name, $longestName) . '   ' . $description) . "\n";
        }

        parent::__construct($help);
    }

    /**
     * retrieves app description for given object
     *
     * @param   object  $object
     * @return  string
     */
    private function readAppDescription($object)
    {
        $annotations = annotationsOf($object);
        if (!$annotations->contain('AppDescription')) {
            return '';
        }

        return $annotations->firstNamed('AppDescription')->getValue() . "\n";
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
}
