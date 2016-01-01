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
use bovigo\callmap\NewInstance;
use org\bovigo\vfs\vfsStream;
use stubbles\console\Console;
use stubbles\lang\ResourceLoader;
use stubbles\lang\Rootpath;

use function bovigo\assert\assert;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\callmap\verify;
/**
 * Test for stubbles\console\creator\ScriptFileCreator.
 *
 * @group  scripts
 */
class ScriptFileCreatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ScriptFileCreator
     */
    private $scriptFileCreator;
    /**
     * mocked console
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $console;
    /**
     * root directory
     *
     * @type  Rootpath
     */
    private $rootpath;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->rootpath          = new Rootpath(vfsStream::setup()->url());
        $this->console           = NewInstance::stub(Console::class);
        $this->scriptFileCreator = new ScriptFileCreator(
                $this->console,
                $this->rootpath,
                new ResourceLoader()
        );
    }

    /**
     * @test
     */
    public function createsScriptIfDoesNotExist()
    {
        $this->console->mapCalls(['readLine'  => 'example']);
        $this->scriptFileCreator->create('example\console\ExampleConsoleApp');
        assertTrue(file_exists($this->rootpath->to('bin/example')));
    }

    /**
     * @test
     */
    public function createsScriptWithCorrectContents()
    {
        $this->console->mapCalls(['readLine'  => 'example']);
        $this->scriptFileCreator->create('example\console\ExampleConsoleApp');
        assert(
                file_get_contents($this->rootpath->to('bin/example')),
                equals('#!/usr/bin/php
<?php
/**
 * Script to execute example\console\ExampleConsoleApp.
 *
 * @package  example\console
 */
namespace example\console;
if (\Phar::running() !== \'\') {
    $rootDir     = \Phar::running();
    $projectPath = getcwd();
} elseif (file_exists(__DIR__ . \'/../vendor/autoload.php\')) {
    $rootDir     = __DIR__ . \'/../\';
    $projectPath = $rootDir;
} else {
    $rootDir     = __DIR__ . \'/../../../../\';
    $projectPath = $rootDir;
}

require $rootDir . \'/vendor/autoload.php\';
exit(ExampleConsoleApp::main(realpath($projectPath), \stubbles\console\ConsoleOutputStream::forError()));
')
        );
        verify($this->console, 'writeLine')
                ->receivedOn(2, 'Script for example\console\ExampleConsoleApp created at ' . $this->rootpath->to('bin/example'));
    }

    /**
     * @test
     */
    public function skipsScriptCreationIfTestAlreadyExists()
    {
        mkdir($this->rootpath->to('bin'));
        file_put_contents($this->rootpath->to('bin/example'), 'foo');
        $this->console->mapCalls(['readLine'  => 'example']);
        $this->scriptFileCreator->create('example\console\ExampleConsoleApp');
        assert(file_get_contents($this->rootpath->to('bin/example')), equals('foo'));
        verify($this->console, 'writeLine')
                ->receivedOn(2, 'Script for example\console\ExampleConsoleApp already exists, skipped creating the script');
    }
}
