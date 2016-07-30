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
namespace stubbles\console\creator;
use stubbles\input\Filter;
use stubbles\input\Param;
use stubbles\input\filter\ReusableFilter;
use stubbles\values\Value;
/**
 * Filter for class names entered via user input.
 *
 * @since  3.0.0
 */
class ClassNameFilter extends Filter
{
    use ReusableFilter;

    /**
     * apply filter on given value
     *
     * @param   \stubbles\values\Value;  $value
     * @return  array
     */
    public function apply(Value $value): array
    {
        if ($value->isEmpty()) {
            return $this->error('CLASSNAME_EMPTY');
        }

        $className = str_replace('\\\\', '\\', trim($value->value()));
        if (!((bool) preg_match('/^([a-zA-Z_]{1}[a-zA-Z0-9_\\\\]*)$/', $className))) {
            return $this->error('CLASSNAME_INVALID');;
        }

        if (! (bool) preg_match('/^([a-zA-Z_]{1}[a-zA-Z0-9_]*)$/', $this->nonQualifiedClassNameOf($className))) {
            return $this->error('CLASSNAME_INVALID');;
        }

        return $this->filtered($className);
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
