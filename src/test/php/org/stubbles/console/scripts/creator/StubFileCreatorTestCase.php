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
 * Test for org\stubbles\console\scripts\creator\StubFileCreator.
 *
 * @group  scripts
 */
class StubFileCreatorTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  StubFileCreator
     */
    private $stubFileCreator;
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
        $this->root            = vfsStream::setup();
        $this->mockConsole     = $this->getMockBuilder('net\\stubbles\\console\\Console')
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->stubFileCreator = new StubFileCreator($this->mockConsole, vfsStream::url('root'));
    }

    /**
     * @test
     */
    public function createsClassIfDoesNotExist()
    {
        $this->mockConsole->expects($this->at(1))
                          ->method('readLine')
                          ->will($this->returnValue('example'));
        $this->mockConsole->expects($this->at(2))
                          ->method('writeLine')
                          ->with($this->equalTo('Stub for example\\console\\ExampleConsoleApp created at ' . vfsStream::url('root/bin/example')));
        $this->stubFileCreator->create('example\\console\\ExampleConsoleApp');
        $this->assertTrue($this->root->hasChild('bin/example'));
        $this->assertEquals('#!/usr/bin/php
<?php
/**
 * Script to execute example\console\ExampleConsoleApp.
 *
 * @package  example\console
 */
namespace example\console;
require __DIR__ . \'/../bootstrap.php\';
exit(ExampleConsoleApp::main(\net\stubbles\Bootstrap::getRootPath(), \net\stubbles\console\ConsoleOutputStream::forError()));
?>',
                            $this->root->getChild('bin/example')
                                       ->getContent()
        );
    }

    /**
     * @test
     */
    public function skipsTestCreationIfTestAlreadyExists()
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
                          ->with($this->equalTo('Stub for org\\stubbles\\console\\scripts\\creator\\TestFileCreator already exists, skipped creating the stub'));
        $this->stubFileCreator->create('org\\stubbles\\console\\scripts\\creator\\TestFileCreator');
        $this->assertEquals('foo', $testFile->getContent());
    }
}
?>