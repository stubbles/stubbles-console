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
     * main method for stubcli
     *
     * @param   string        $projectPath
     * @param   array         $argv
     * @param   OutputStream  $err
     * @return  int  exit code
     */
    public static function stubcli($projectPath, array $argv, OutputStream $err)
    {
        if (!isset($argv[1])) {
            $err->writeLine('*** Missing classname of command app to execute');
            return 1;
        }

        $commandClass = $argv[1];
        try {
            return (int) $commandClass::create($projectPath)
                                      ->run();
        } catch (\Exception $e) {
            $err->writeLine('*** ' . get_class($e) . ': ' . $e->getMessage());
            return 70;
        }
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
        return new ArgumentsBindingModule();
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