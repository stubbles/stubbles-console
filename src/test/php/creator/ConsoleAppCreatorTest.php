<?php
/**
 * Your license or something other here.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\creator;
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
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $console;
    /**
     * mocked class file creator
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $classFile;
    /**
     * mocked script file creator
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $scriptFile;
    /**
     * mocked test file creator
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
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
        assertEquals(0, $this->classFile->callsReceivedFor('create'));
        assertEquals(0, $this->scriptFile->callsReceivedFor('create'));
        assertEquals(0, $this->testFile->callsReceivedFor('create'));
    }

    /**
     * @test
     */
    public function returnsExitCodeZeroOnSuccess()
    {
        $this->console->mapCalls(['prompt' => ValueReader::forValue('foo\\bar\\Example')]);
        assertEquals(0, $this->consoleAppCreator->run());
        assertEquals(
                ['foo\bar\Example'],
                $this->classFile->argumentsReceivedFor('create')
        );
        assertEquals(
                ['foo\bar\Example'],
                $this->scriptFile->argumentsReceivedFor('create')
        );
        assertEquals(
                ['foo\bar\Example'],
                $this->testFile->argumentsReceivedFor('create')
        );
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
