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
namespace stubbles\console\creator;
/**
 * Creates a script file for given console class name.
 */
class ScriptFileCreator extends FileCreator
{
    /**
     * creates file
     *
     * @param  string  $className
     */
    public function create(string $className)
    {
        $this->console->writeLine('Please enter the script name for the console app: ');
        $stubFileName = $this->rootpath->to('bin',  $this->console->readLine());
        if (!file_exists($stubFileName)) {
            $this->createFile($stubFileName, $className, 'script.tmpl');
            $this->console->writeLine(
                    'Script for ' . $className . ' created at ' . $stubFileName
            );
        } else {
            $this->console->writeLine(
                    'Script for ' . $className
                    . ' already exists, skipped creating the script'
            );
        }
    }
}
