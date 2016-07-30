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
namespace stubbles\console;
use stubbles\App;
use stubbles\console\input\ArgumentParser;
use stubbles\console\input\HelpScreen;
use stubbles\console\input\InvalidOptionValue;
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
    public static function main(string $projectPath, OutputStream $err): int
    {
        try {
            return (int) self::create($projectPath)->run();
        } catch (HelpScreen $helpscreen) {
            $err->writeLine($helpscreen->getMessage());
            return 0;
        } catch (InvalidOptionValue $iov) {
            $err->writeLine($iov->getMessage());
            return 10;
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
    protected static function getBindingsForApp(string $className): array
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
        };
        return $bindings;
    }

    /**
     * creates argument binding module
     *
     * @api
     * @return  \stubbles\console\input\ArgumentParser
     */
    protected static function argumentParser(): ArgumentParser
    {
        return new ArgumentParser();
    }
}
