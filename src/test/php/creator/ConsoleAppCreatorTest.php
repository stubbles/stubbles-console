<?php
declare(strict_types=1);
/**
 * Your license or something other here.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\creator;
use bovigo\callmap\NewInstance;
use stubbles\console\Console;
use stubbles\input\ValueReader;
use stubbles\values\Rootpath;

use function bovigo\assert\assert;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
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
        assert($this->consoleAppCreator->run(), equals(-10));
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
        assert($this->consoleAppCreator->run(), equals(0));
        verify($this->classFile, 'create')->received('foo\bar\Example');
        verify($this->scriptFile, 'create')->received('foo\bar\Example');
        verify($this->testFile, 'create')->received('foo\bar\Example');
    }

    /**
     * @test
     */
    public function canCreateInstance()
    {
        assert(
                ConsoleAppCreator::create(Rootpath::default()),
                isInstanceOf(ConsoleAppCreator::class)
        );
    }
}
