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
use stubbles\values\Value;

use function bovigo\assert\{
    assert,
    assertNull,
    assertTrue,
    predicate\equals
};
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

    public function emptyParamValues(): array
    {
        return [[null], ['']];
    }

    /**
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
     * @test
     * @dataProvider  emptyParamValues
     */
    public function addsErrorToParamWhenParamValueIsEmpty($value)
    {
        list($_, $errors) = $this->classNameFilter->apply(Value::of($value));
        assertTrue(isset($errors['CLASSNAME_EMPTY']));
    }

    public function invalidParamValues(): array
    {
        return [['500'], ['foo\500']];
    }

    /**
     * @test
     * @dataProvider  invalidParamValues
     */
    public function returnsNullWhenParamValueHasInvalidSyntax(string $value)
    {
        assertNull(
                $this->classNameFilter->apply(Value::of($value))[0]
        );
    }

    /**
     * @test
     * @dataProvider  invalidParamValues
     */
    public function addsErrorToParamWhenParamValueHasInvalidSyntax(string $value)
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
