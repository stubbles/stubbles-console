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
 *
 * @since  2.0.0
 */
abstract class ConsoleApp extends App
{
    /**
     * switch whether stubcli was used
     *
     * @type  bool
     */
    private static $stubcli = false;

    /**
     * main method for stubcli
     *
     * @param   string        $projectPath
     * @param   array         $argv
     * @param   OutputStream  $err
     * @return  int  exit code
     */
    public static function stubcli($projectPath, array $argv, OutputStream $err)
    {
        $commandClass  = self::parseCommandClass($argv, $err);
        if (is_int($commandClass)) {
            return $commandClass;
        }

        if (!class_exists($commandClass)) {
            $err->writeLine('*** Can not find ' . $commandClass);
            return 3;
        }

        self::$stubcli = true;
        try {
            return (int) $commandClass::create($projectPath)
                                      ->run();
        } catch (\Exception $e) {
            $err->writeLine('*** ' . get_class($e) . ': ' . $e->getMessage());
            return 70;
        }
    }

    /**
     * tries to parse command class from input
     *
     * @param   array         $argv
     * @param   OutputStream  $err
     * @return  int|string
     */
    private static function parseCommandClass(array $argv, OutputStream $err)
    {
        $c = array_search('-c', $argv);
        if (false === $c) {
            $err->writeLine('*** Missing classname option -c');
            return 1;
        }

        if (!isset($argv[$c + 1])) {
            $err->writeLine('*** No classname specified in -c');
            return 2;
        }

        return $argv[$c + 1];
    }

    /**
     * main method
     *
     * @param   string        $projectPath
     * @param   OutputStream  $err
     * @return  int  exit code
     */
    public static function main($projectPath, OutputStream $err)
    {
        try {
            return (int) self::create($projectPath)
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
     */
    protected static function createArgumentsBindingModule()
    {
        return new ArgumentsBindingModule(self::$stubcli);
    }

    /**
     * creates console binding module
     *
     * @return  ConsoleBindingModule
     */
    protected static function createConsoleBindingModule()
    {
        return new ConsoleBindingModule();
    }
}
?>