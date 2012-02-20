<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
namespace org\stubbles\console\scripts\creator;
/**
 * Creates a stub file for given console class name.
 */
class StubFileCreator extends FileCreator
{
    /**
     * creates file
     *
     * @param  string  $className
     */
    public function create($className)
    {
        $this->console->writeLine('Please enter the stub name for the console app: ');
        $stubFileName = $this->projectPath . '/bin/'  . $this->console->readLine();
        if (!file_exists($stubFileName)) {
            $this->createFile($stubFileName, $className, 'stub.tmpl');
            $this->console->writeLine('Stub for ' . $className . ' created at ' . $stubFileName);
        } else {
            $this->console->writeLine('Stub for ' . $className . ' already exists, skipped creating the stub');
        }
    }
}
?>