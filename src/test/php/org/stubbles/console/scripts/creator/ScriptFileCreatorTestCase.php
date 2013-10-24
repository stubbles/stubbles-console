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
use org\bovigo\vfs\vfsStream;
/**
 * Test for org\stubbles\console\scripts\creator\ScriptFileCreator.
 *
 * @group  scripts
 */
class ScriptFileCreatorTestCase extends \PHPUnit_Framework_TestCase
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
     * @type  \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->root              = vfsStream::setup();
        $this->mockConsole       = $this->getMockBuilder('net\stubbles\console\Console')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->scriptFileCreator = new ScriptFileCreator($this->mockConsole, vfsStream::url('root'));
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
                          ->with($this->equalTo('Script for example\console\ExampleConsoleApp created at ' . vfsStream::url('root/bin/example')));
        $this->scriptFileCreator->create('example\console\ExampleConsoleApp');
        $this->assertTrue($this->root->hasChild('bin/example'));
        $this->assertEquals('#!/usr/bin/php
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
exit(ExampleConsoleApp::main(realpath($projectPath), \net\stubbles\console\ConsoleOutputStream::forError()));
',
                            $this->root->getChild('bin/example')
                                       ->getContent()
        );
    }

    /**
     * @test
     */
    public function skipsScriptCreationIfTestAlreadyExists()
    {
        vfsStream::newDirectory('bin')
                 ->at($this->root);
        $testFile = vfsStream::newFile('example')
                             ->withContent('foo')
                             ->at($this->root->getChild('bin'));
        $this->mockConsole->expects($this->at(1))
                          ->method('readLine')
                          ->will($this->returnValue('example'));
        $this->mockConsole->expects($this->at(2))
                          ->method('writeLine')
                          ->with($this->equalTo('Script for org\stubbles\console\scripts\creator\TestFileCreator already exists, skipped creating the script'));
        $this->scriptFileCreator->create('org\stubbles\console\scripts\creator\TestFileCreator');
        $this->assertEquals('foo', $testFile->getContent());
    }
}
?>