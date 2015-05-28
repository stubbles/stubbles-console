<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\creator;
use stubbles\console\Console;
use stubbles\console\ConsoleApp;
/**
 * App to create initial source files for other console apps.
 */
class ConsoleAppCreator extends ConsoleApp
{
    /**
     * console to read and write to
     *
     * @type  \stubbles\console\Console
     */
    private $console;
    /**
     * class file creator
     *
     * @type  \stubbles\console\creator\ClassFileCreator
     */
    private $classFile;
    /**
     * stub file creator
     *
     * @type  \stubbles\console\creator\ScriptFileCreator
     */
    private $scriptFile;
    /**
     * test file creator
     *
     * @type  \stubbles\console\creator\TestFileCreator
     */
    private $testFile;

    /**
     * constructor
     *
     * @param  \stubbles\console\Console                    $console
     * @param  \stubbles\console\creator\ClassFileCreator   $classFile
     * @param  \stubbles\console\creator\ScriptFileCreator  $scriptFile
     * @param  \stubbles\console\creator\TestFileCreator    $testFile
     */
    public function __construct(
            Console $console,
            ClassFileCreator $classFile,
            ScriptFileCreator $scriptFile,
            TestFileCreator $testFile)
    {
        $this->console    = $console;
        $this->classFile  = $classFile;
        $this->scriptFile = $scriptFile;
        $this->testFile   = $testFile;
    }

    /**
     * runs the command and returns an exit code
     *
     * @return  int
     */
    public function run()
    {
        $className = $this->console->writeLine('Stubbles ConsoleAppCreator')
                                   ->writeLine(' (c) 2012-2014 Stubbles Development Group')
                                   ->writeLine('')
                                   ->prompt('Please enter the full qualified class name for the console app: ')
                                   ->withFilter(ClassNameFilter::instance());
        if (null === $className) {
            $this->console->writeLine('The entered class name is not a valid class name');
            return -10;
        }

        $this->classFile->create($className);
        $this->scriptFile->create($className);
        $this->testFile->create($className);
        return 0;
    }
}
