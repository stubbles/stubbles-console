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
 * Creates a test file for given console class name.
 */
class TestFileCreator extends FileCreator
{
    /**
     * creates file
     *
     * @param  string  $className
     */
    public function create(string $className)
    {
        $testClassFile = $this->fileNameforClass($className . 'Test', 'test');
        if (!file_exists($testClassFile)) {
            $this->createFile($testClassFile, $className, 'test.tmpl');
            $this->console->writeLine(
                    'Test for ' . $className . ' created at ' . $testClassFile
            );
        } else {
            $this->console->writeLine(
                    'Test for ' . $className
                    . ' already exists, skipped creating the test'
            );
        }
    }

}
