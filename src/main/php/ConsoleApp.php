<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console;
use stubbles\console\ioc\Arguments;
use stubbles\ioc\App;
use stubbles\ioc\Binder;
use stubbles\streams\OutputStream;
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
     * @param   string                          $projectPath  path of current project
     * @param   array                           $argv         list of command line arguments
     * @param   \stubbles\streams\OutputStream  $err          stream to write errors to
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
            return self::handleException($e, $err);
        }
    }

    /**
     * tries to parse command class from input
     *
     * @param   array                           $argv  list of command line arguments
     * @param   \stubbles\streams\OutputStream  $err   stream to write errors to
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
     * @api
     * @param   string                          $projectPath  path of current project
     * @param   \stubbles\streams\OutputStream  $err          stream to write errors to
     * @return  int  exit code
     */
    public static function main($projectPath, OutputStream $err)
    {
        try {
            return (int) self::create($projectPath)
                             ->run();
        } catch (\Exception $e) {
            return self::handleException($e, $err);
        }
    }

    /**
     * handle exception
     *
     * @param   \Exception                      $e    exception to handle
     * @param   \stubbles\streams\OutputStream  $err  stream to write exception information to
     * @return  int
     */
    private static function handleException(\Exception $e, OutputStream $err)
    {
        if ($e instanceof ConsoleAppException) {
            $e->writeTo($err);
            return $e->getCode();
        }

        $err->writeLine('*** ' . get_class($e) . ': ' . $e->getMessage());
        $err->writeLine('Stacktrace:');
        $err->writeLine($e->getTraceAsString());
        return 20;
    }

    /**
     * creates list of bindings from given class
     *
     * @internal
     * @param   string  $className    full qualified class name of class to create an instance of
     * @param   string  $projectPath  path to project
     * @return  \stubbles\ioc\module\BindingModule[]
     */
    protected static function getBindingsForApp($className, $projectPath)
    {
        $bindings   = parent::getBindingsForApp($className, $projectPath);
        $bindings[] = function(Binder $binder)
        {
            $binder->bind('stubbles\streams\InputStream')
                   ->named('stdin')
                   ->toInstance(ConsoleInputStream::forIn());
            $binder->bind('stubbles\streams\OutputStream')
                   ->named('stdout')
                   ->toInstance(ConsoleOutputStream::forOut());
            $binder->bind('stubbles\streams\OutputStream')
                   ->named('stderr')
                   ->toInstance(ConsoleOutputStream::forError());
            $binder->bind('stubbles\console\Executor')
                   ->to('stubbles\console\ConsoleExecutor');
        };
        return $bindings;
    }

    /**
     * creates argument binding module
     *
     * @api
     * @return  \stubbles\console\ioc\Arguments
     */
    protected static function parseArguments()
    {
        return new Arguments(self::$stubcli);
    }

    /**
     * creates argument binding module
     *
     * @api
     * @return  \stubbles\console\ioc\Arguments
     * @deprecated  since 4.0.0, use bindArguments() instead, will be removed with 5.0.0
     */
    protected static function createArgumentsBindingModule()
    {
        return self::parseArguments();
    }
}
