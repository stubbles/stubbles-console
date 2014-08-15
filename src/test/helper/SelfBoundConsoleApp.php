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
    public static $bar;

    /**
     * returns list of bindings used for this application
     *
     * @param   string  $projectPath
     * @return  \stubbles\ioc\module\BindingModule[]
     */
    public static function __bindings($projectPath)
    {
        return [self::createModeBindingModule($projectPath),
                self::createArgumentsBindingModule()
                    ->withUserInput(__CLASS__),
                self::createConsoleBindingModule()
        ];
    }

    /**
     * test method
     *
     * @Request[String](paramName='argv.0')
     * @param  string  $bar
     */
    public function setArgument($arg)
    {
        self::$bar = $arg;
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
