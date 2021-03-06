<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace org\stubbles\console\test;
use stubbles\console\ConsoleApp;
/**
 * Helper class for the test.
 *
 * @since  2.1.0
 */
class SelfBoundConsoleApp extends ConsoleApp
{
    /**
     * argument via user input parsing
     *
     * @type  string
     */
    public $bar;

    /**
     * returns list of bindings used for this application
     *
     * @return  \stubbles\ioc\module\BindingModule[]
     */
    public static function __bindings()
    {
        return [self::argumentParser()->withUserInput(__CLASS__)];
    }

    /**
     * test method
     *
     * @Request[String](paramName='argv.0')
     * @param  string  $arg
     */
    public function setArgument($arg)
    {
        $this->bar = $arg;
    }

    /**
     * runs the command and returns an exit code
     *
     * @return  int
     */
    public function run()
    {
        return 0;
    }
}
