<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
namespace net\stubbles\console;
use net\stubbles\console\ioc\ArgumentsBindingModule;
use net\stubbles\console\ioc\ConsoleBindingModule;
use net\stubbles\ioc\App;
use net\stubbles\streams\OutputStream;
/**
 * Base class for console applications.
 */
abstract class ConsoleApp extends App
{
    /**
     * list of arguments
     *
     * @type  string[]
     */
    private static $_argv;

    /**
     * main method
     *
     * @param   string        $projectPath
     * @param   array         $argv
     * @param   OutputStream  $err
     * @return  int  exit code
     */
    public static function main($projectPath, array $argv, OutputStream $err)
    {
        if (!isset($argv[1])) {
            $err->writeLine('*** Missing classname of command app to execute');
            return 1;
        }

        array_shift($argv); // stubcli
        $commandClass = array_shift($argv);
        self::$_argv  = array_values($argv);
        try {
            return (int) $commandClass::create($projectPath)
                                      ->run();
        } catch (\Exception $e) {
            $err->writeLine('*** ' . get_class($e) . ': ' . $e->getMessage());
            return 70;
        }
    }

    /**
     * creates argument binding module
     *
     * @return  ArgumentsBindingModule
     * @since   2.0.0
     */
    protected static function createArgumentsBindingModule()
    {
        return new ArgumentsBindingModule(self::$_argv);
    }

    /**
     * creates console binding module
     *
     * @return  ConsoleBindingModule
     * @since   2.0.0
     */
    protected static function createConsoleBindingModule()
    {
        return new ConsoleBindingModule();
    }
}
?>