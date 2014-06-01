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
     * @type  ScriptFileCreator
     */
    private $scriptFile;
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
     * @return  \stubbles\ioc\module\BindingModule[]
     */
    public static function __bindings($projectPath)
    {
        return [self::createModeBindingModule($projectPath),
                self::createConsoleBindingModule()
        ];
    }

    /**
     * constructor
     *
     * @param  Console            $console
     * @param  ClassFileCreator   $classFile
     * @param  ScriptFileCreator  $scriptFile
     * @param  TestFileCreator    $testFile
     * @Inject
     */
    public function __construct(Console $console,
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
        $this->console->writeLine('Stubbles ConsoleAppCreator')
                      ->writeLine(' (c) 2012-2014 Stubbles Development Group')
                      ->writeLine('')
                      ->writeLine('Please enter the full qualified class name for the console app: ');
        $className = str_replace('\\\\', '\\', trim($this->console->readLine()));
        if (!$this->isValid($className)) {
            $this->console->writeLine('The class name ' . $className . ' is not a valid class name');
            return -10;
        }

        $this->classFile->create($className);
        $this->scriptFile->create($className);
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
                                 $this->getNonQualifiedClassName($className)
                      );
    }

    /**
     * returns non qualified part of class name
     *
     * @param   string  $className
     * @return  string
     */
    private function getNonQualifiedClassName($className)
    {
        return substr($className, strrpos($className, '\\') + 1);
    }
}
