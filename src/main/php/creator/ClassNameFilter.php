<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\creator;
use stubbles\input\Filter;
use stubbles\input\Param;
use stubbles\input\filter\ReusableFilter;
/**
 * Filter for class names entered via user input.
 *
 * @since  3.0.0
 */
class ClassNameFilter implements Filter
{
    use ReusableFilter;

    /**
     * apply filter on given param
     *
     * @param   \stubbles\input\Param  $param
     * @return  mixed  filtered value
     */
    public function apply(Param $param)
    {
        if ($param->isEmpty()) {
            $param->addError('CLASSNAME_EMPTY');
            return null;
        }

        $className = str_replace('\\\\', '\\', trim($param->value()));
        if (! ((bool) preg_match('/^([a-zA-Z_]{1}[a-zA-Z0-9_\\\\]*)$/', $className))) {
            $param->addError('CLASSNAME_INVALID');
            return null;
        }

        if (! (bool) preg_match('/^([a-zA-Z_]{1}[a-zA-Z0-9_]*)$/', $this->nonQualifiedClassNameOf($className))) {
            $param->addError('CLASSNAME_INVALID');
            return null;
        }

        return $className;
    }

    /**
     * returns non qualified part of class name
     *
     * @param   string  $className
     * @return  string
     */
    private function nonQualifiedClassNameOf($className)
    {
        return substr($className, strrpos($className, '\\') + 1);
    }
}
