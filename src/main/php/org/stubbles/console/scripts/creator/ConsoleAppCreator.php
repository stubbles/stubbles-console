<?php
/**
 * Your license or something other here.
 *
 * @package  net\stubbles\console
 */
namespace org\stubbles\console\scripts\creator;
use net\stubbles\console\Console;
use net\stubbles\console\ConsoleApp;
/**
 * Your own console app.
 */
class ConsoleAppCreator extends ConsoleApp
{
    /**
     * console to read and write to
     *
     * @type  Console
     */
    private $console;
    /**
     * class file creator
     *
     * @type  ClassFileCreator
     */
    private $classFile;
    /**
     * stub file creator
     *
     * @type  StubFileCreator
     */
    private $stubFile;
    /**
     * test file creator
     *
     * @type  TestFileCreator
     */
    private $testFile;

    /**
     * returns list of bindings used for this application
     *
     * @param   string  $projectPath
     * @return  \net\stubbles\ioc\module\BindingModule[]
     */
    public static function __bindings($projectPath)
    {
        return array(self::createConsoleBindingModule(),
                     self::createPropertiesBindingModule($projectPath)
        );
    }

    /**
     * constructor
     *
     * @param  Console           $console
     * @param  ClassFileCreator  $classFile
     * @param  StubFileCreator   $stubFile
     * @param  TestFileCreator   $testFile
     * @Inject
     */
    public function __construct(Console $console,
                                ClassFileCreator $classFile,
                                StubFileCreator $stubFile,
                                TestFileCreator $testFile)
    {
        $this->console   = $console;
        $this->classFile = $classFile;
        $this->stubFile  = $stubFile;
        $this->testFile  = $testFile;
    }

    /**
     * runs the command and returns an exit code
     *
     * @return  int
     */
    public function run()
    {
        $this->console->writeLine('Stubbles ConsoleAppCreator')
                      ->writeLine(' (c) 2012 Stubbles Development Group')
                      ->writeLine('')
                      ->writeLine('Please enter the full qualified class name for the console app: ');
        $className = str_replace('\\\\', '\\', trim($this->console->readLine()));
        if (!$this->isValid($className)) {
            $this->console->writeLine('The class name ' . $className . ' is not a valid class name');
            return -10;
        }

        $this->classFile->create($className);
        $this->stubFile->create($className);
        $this->testFile->create($className);
        return 0;
    }

    /**
     * checks if given class name is valid
     *
     * @param   string  $className
     * @return  bool
     */
    private function isValid($className)
    {
        if (! (bool) preg_match('/^([a-zA-Z_]{1}[a-zA-Z0-9_\\\\]*)$/', $className)) {
            return false;
        }

        return (bool) preg_match('/^([a-zA-Z_]{1}[a-zA-Z0-9_]*)$/',
                                 substr($className, strrpos($className, '\\') + 1)
                      );
    }
}
?>