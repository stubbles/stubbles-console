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
use stubbles\console\ioc\ArgumentParser;
use stubbles\ioc\App;
use stubbles\ioc\Binder;
use stubbles\streams\InputStream;
use stubbles\streams\OutputStream;
/**
 * Base class for console applications.
 *
 * @since  2.0.0
 */
abstract class ConsoleApp extends App
{
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
            return (int) self::create($projectPath)->run();
        } catch (ConsoleAppException $cae) {
            $cae->writeTo($err);
            return $cae->getCode();
        } catch (\Exception $e) {
            $err->writeLine('*** ' . get_class($e) . ': ' . $e->getMessage());
            $err->writeLine('Stacktrace:');
            $err->writeLine($e->getTraceAsString());
            return 20;
        }
    }

    /**
     * creates list of bindings from given class
     *
     * @internal
     * @param   string  $className    full qualified class name of class to create an instance of
     * @return  \stubbles\ioc\module\BindingModule[]
     */
    protected static function getBindingsForApp($className)
    {
        $bindings   = parent::getBindingsForApp($className);
        $bindings[] = function(Binder $binder)
        {
            $binder->bind(InputStream::class)
                   ->named('stdin')
                   ->toInstance(ConsoleInputStream::forIn());
            $binder->bind(OutputStream::class)
                   ->named('stdout')
                   ->toInstance(ConsoleOutputStream::forOut());
            $binder->bind(OutputStream::class)
                   ->named('stderr')
                   ->toInstance(ConsoleOutputStream::forError());
            $binder->bind(Executor::class)
                   ->to(ConsoleExecutor::class);
        };
        return $bindings;
    }

    /**
     * creates argument binding module
     *
     * @api
     * @return  \stubbles\console\ioc\ArgumentParser
     */
    protected static function argumentParser()
    {
        return new ArgumentParser();
    }
}
