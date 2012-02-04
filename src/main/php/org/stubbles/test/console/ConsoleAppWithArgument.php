<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles
 */
namespace org\stubbles\test\console;
use net\stubbles\console\ConsoleApp;
/**
 * Helper class for ioc tests.
 */
class ConsoleAppWithArgument extends ConsoleApp
{
    /**
     * returns list of binding modules
     *
     * @param   string  $projectPath
     * @return  array
     */
    public static function __bindings($projectPath)
    {
        return array(self::createArgumentsBindingModule(),
                     self::createConsoleBindingModule()
        );
    }

    /**
     * given project path
     *
     * @type  string
     */
    protected static $arg;

    /**
     * returns set project path
     *
     * @return  string
     * @Inject
     * @Named('argv.0')
     */
    public function setArgument($arg)
    {
        self::$arg = $arg;
    }

    /**
     * returns the argument
     *
     * @return  string
     */
    public static function getArgument()
    {
        return self::$arg;
    }

    /**
     * runs the command
     */
    public function run() { }
}
?>