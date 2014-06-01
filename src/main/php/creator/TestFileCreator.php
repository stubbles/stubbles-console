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
 * Creates a test file for given console class name.
 */
class TestFileCreator extends FileCreator
{
    /**
     * creates file
     *
     * @param  string  $className
     */
    public function create($className)
    {
        $testClassFile = $this->getClassFileName($className . 'TestCase', 'test');
        if (!file_exists($testClassFile)) {
            $this->createFile($testClassFile, $className, 'test.tmpl');
            $this->console->writeLine('Test for ' . $className . ' created at ' . $testClassFile);
        } else {
            $this->console->writeLine('Test for ' . $className . ' already exists, skipped creating the test');
        }
    }

}
