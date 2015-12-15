<?php
/**
 * Your license or something other here.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\creator;
use bovigo\callmap\NewInstance;
use stubbles\console\Console;
use stubbles\input\ValueReader;
use stubbles\lang\Rootpath;

use function bovigo\callmap\verify;
/**
 * Test for stubbles\console\creator\ConsoleAppCreator.
 */
class ConsoleAppCreatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ConsoleAppCreator
     */
    private $consoleAppCreator;
    /**
     * mocked console
     *
     * @type  \bovigo\callmap\Proxy
     */
    private $console;
    /**
     * mocked class file creator
     *
     * @type  \bovigo\callmap\Proxy
     */
    private $classFile;
    /**
     * mocked script file creator
     *
     * @type  \bovigo\callmap\Proxy
     */
    private $scriptFile;
    /**
     * mocked test file creator
     *
     * @type  \bovigo\callmap\Proxy
     */
    private $testFile;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->console    = NewInstance::stub(Console::class);
        $this->classFile  = NewInstance::stub(ClassFileCreator::class);
        $this->scriptFile = NewInstance::stub(ScriptFileCreator::class);
        $this->testFile   = NewInstance::stub(TestFileCreator::class);
        $this->consoleAppCreator = new ConsoleAppCreator(
                $this->console,
                $this->classFile,
                $this->scriptFile,
                $this->testFile
        );
    }

    /**
     * @test
     */
    public function doesNotCreateClassWhenClassNameIsInvalid()
    {
        $this->console->mapCalls(['prompt' => ValueReader::forValue(null)]);
        assertEquals(-10, $this->consoleAppCreator->run());
        verify($this->classFile, 'create')->wasNeverCalled();
        verify($this->scriptFile, 'create')->wasNeverCalled();
        verify($this->testFile, 'create')->wasNeverCalled();
    }

    /**
     * @test
     */
    public function returnsExitCodeZeroOnSuccess()
    {
        $this->console->mapCalls(['prompt' => ValueReader::forValue('foo\\bar\\Example')]);
        assertEquals(0, $this->consoleAppCreator->run());
        verify($this->classFile, 'create')->received('foo\bar\Example');
        verify($this->scriptFile, 'create')->received('foo\bar\Example');
        verify($this->testFile, 'create')->received('foo\bar\Example');
    }

    /**
     * @test
     */
    public function canCreateInstance()
    {
        assertInstanceOf(
                ConsoleAppCreator::class,
                ConsoleAppCreator::create(new Rootpath())
        );
    }
}
