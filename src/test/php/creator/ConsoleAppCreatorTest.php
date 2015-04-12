<?php
/**
 * Your license or something other here.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\creator;
use bovigo\callmap;
use bovigo\callmap\NewInstance;
use stubbles\input\ValueReader;
use stubbles\lang\Rootpath;
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
        $this->console    = NewInstance::stub('stubbles\console\Console');
        $this->classFile  = NewInstance::stub('stubbles\console\creator\ClassFileCreator');
        $this->scriptFile = NewInstance::stub('stubbles\console\creator\ScriptFileCreator');
        $this->testFile   = NewInstance::stub('stubbles\console\creator\TestFileCreator');
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
        callmap\verify($this->classFile, 'create')->wasNeverCalled();
        callmap\verify($this->scriptFile, 'create')->wasNeverCalled();
        callmap\verify($this->testFile, 'create')->wasNeverCalled();
    }

    /**
     * @test
     */
    public function returnsExitCodeZeroOnSuccess()
    {
        $this->console->mapCalls(['prompt' => ValueReader::forValue('foo\\bar\\Example')]);
        assertEquals(0, $this->consoleAppCreator->run());
        callmap\verify($this->classFile, 'create')->received('foo\bar\Example');
        callmap\verify($this->scriptFile, 'create')->received('foo\bar\Example');
        callmap\verify($this->testFile, 'create')->received('foo\bar\Example');
    }

    /**
     * @test
     */
    public function canCreateInstance()
    {
        assertInstanceOf(
                'stubbles\console\creator\ConsoleAppCreator',
                ConsoleAppCreator::create(new Rootpath())
        );
    }
}
