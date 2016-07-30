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
use stubbles\values\Value;

use function bovigo\assert\assert;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\console\creator\ClassNameFilter.
 *
 * @group  scripts
 * @since  3.0.0
 */
class ClassNameFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ClassNameFilter
     */
    private $classNameFilter;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->classNameFilter = new ClassNameFilter();
    }

    /**
     * @return  array
     */
    public function emptyParamValues()
    {
        return [[null], ['']];
    }

    /**
     * @param  string  $value
     * @test
     * @dataProvider  emptyParamValues
     */
    public function returnsNullWhenParamValueIsEmpty($value)
    {
        assertNull(
                $this->classNameFilter->apply(Value::of($value))[0]
        );
    }

    /**
     * @param  string  $value
     * @test
     * @dataProvider  emptyParamValues
     */
    public function addsErrorToParamWhenParamValueIsEmpty($value)
    {
        list($_, $errors) = $this->classNameFilter->apply(Value::of($value));
        assertTrue(isset($errors['CLASSNAME_EMPTY']));
    }

    /**
     * @return  array
     */
    public function invalidParamValues()
    {
        return [['500'], ['foo\500']];
    }

    /**
     * @param  string  $value
     * @test
     * @dataProvider  invalidParamValues
     */
    public function returnsNullWhenParamValueHasInvalidSyntax($value)
    {
        assertNull(
                $this->classNameFilter->apply(Value::of($value))[0]
        );
    }

    /**
     * @param  string  $value
     * @test
     * @dataProvider  invalidParamValues
     */
    public function addsErrorToParamWhenParamValueHasInvalidSyntax($value)
    {
        list($_, $errors) = $this->classNameFilter->apply(Value::of($value));
        assertTrue(isset($errors['CLASSNAME_INVALID']));
    }

    /**
     * @test
     */
    public function trimsInputValue()
    {
        assert(
                $this->classNameFilter->apply(Value::of('  foo\bar\Baz  '))[0],
                equals('foo\bar\Baz')
        );
    }

    /**
     * @test
     */
    public function fixesQuotedNamespaceSeparator()
    {
        assert(
                $this->classNameFilter->apply(Value::of('foo\\\\bar\\\\Baz'))[0],
                equals('foo\bar\Baz')
        );
    }
}
