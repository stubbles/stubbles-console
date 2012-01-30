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
use net\stubbles\ioc\App;
use net\stubbles\lang\BaseObject;
use net\stubbles\streams\OutputStream;
/**
 * Runner for console commands.
 */
class ConsoleCommandRunner extends BaseObject
{
    /**
     * main method
     *
     * @param   string  $projectPath
     * @param   array   $argv
     * @return  int     exit code
     */
    public static function main($projectPath, array $argv)
    {
        return self::run($projectPath, $argv, ConsoleOutputStream::forError());
    }

    /**
     * running method
     *
     * @param   string        $projectPath
     * @param   array         $argv
     * @param   OutputStream  $err
     * @return  int  exit code
     */
    public static function run($projectPath, array $argv, OutputStream $err)
    {
        if (!isset($argv[1])) {
            $err->writeLine('*** Missing classname of command class to execute');
            return 1;
        }

        array_shift($argv); // stubcli
        $commandClass = array_shift($argv);
        try {
            return (int) App::createInstance($commandClass, $projectPath, array_values($argv))
                            ->run();
        } catch (\Exception $e) {
            $err->writeLine('*** ' . get_class($e) . ': ' . $e->getMessage());
            return 70;
        }
    }
}
?>