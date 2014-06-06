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
use org\bovigo\vfs\vfsStream;
use stubbles\lang\ResourceLoader;
use stubbles\lang\Rootpath;
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
    private $mockConsole;
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
        $this->mockConsole       = $this->getMockBuilder('stubbles\console\Console')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->scriptFileCreator = new ScriptFileCreator($this->mockConsole, $this->rootpath, new ResourceLoader());
    }

    /**
     * @test
     */
    public function createsScriptIfDoesNotExist()
    {
        $this->mockConsole->expects($this->at(1))
                          ->method('readLine')
                          ->will($this->returnValue('example'));
        $this->mockConsole->expects($this->at(2))
                          ->method('writeLine')
                          ->with($this->equalTo('Script for example\console\ExampleConsoleApp created at ' . $this->rootpath->to('bin/example')));
        $this->scriptFileCreator->create('example\console\ExampleConsoleApp');
        $this->assertTrue(file_exists($this->rootpath->to('bin/example')));
        $this->assertEquals(
                '#!/usr/bin/php
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
',
                file_get_contents($this->rootpath->to('bin/example'))
        );
    }

    /**
     * @test
     */
    public function skipsScriptCreationIfTestAlreadyExists()
    {
        mkdir($this->rootpath->to('bin'));
        file_put_contents($this->rootpath->to('bin/example'), 'foo');
        $this->mockConsole->expects($this->at(1))
                          ->method('readLine')
                          ->will($this->returnValue('example'));
        $this->mockConsole->expects($this->at(2))
                          ->method('writeLine')
                          ->with($this->equalTo('Script for example\console\ExampleConsoleApp already exists, skipped creating the script'));
        $this->scriptFileCreator->create('example\console\ExampleConsoleApp');
        $this->assertEquals('foo', file_get_contents($this->rootpath->to('bin/example')));
    }
}
