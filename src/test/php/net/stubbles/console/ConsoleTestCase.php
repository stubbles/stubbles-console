<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
namespace net\stubbles\console;
/**
 * Test for net\stubbles\console\Console.
 *
 * @group  console
 */
class ConsoleTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  Console
     */
    private $console;
    /**
     * mocked input stream
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockInputStream;
    /**
     * mocked output stream
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockOutputStream;
    /**
     * mocked error stream
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockErrorStream;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->mockInputStream  = $this->getMock('net\\stubbles\\streams\\InputStream');
        $this->mockOutputStream = $this->getMock('net\\stubbles\\streams\\OutputStream');
        $this->mockErrorStream  = $this->getMock('net\\stubbles\\streams\\OutputStream');
        $this->console          = new Console($this->mockInputStream,
                                              $this->mockOutputStream,
                                              $this->mockErrorStream
                                  );
    }

    /**
     * @test
     */
    public function annotationsPresentOnConstructor()
    {
        $constructor = $this->console->getClass()->getConstructor();
        $this->assertTrue($constructor->hasAnnotation('Inject'));

        $parameters = $constructor->getParameters();
        $this->assertTrue($parameters[0]->hasAnnotation('Named'));
        $this->assertEquals('stdin',
                            $parameters[0]->getAnnotation('Named')->getName()
        );
        $this->assertTrue($parameters[1]->hasAnnotation('Named'));
        $this->assertEquals('stdout',
                            $parameters[1]->getAnnotation('Named')->getName()
        );
        $this->assertTrue($parameters[2]->hasAnnotation('Named'));
        $this->assertEquals('stderr',
                            $parameters[2]->getAnnotation('Named')->getName()
        );
    }

    /**
     * @test
     */
    public function usesInputStreamForRead()
    {
        $this->mockInputStream->expects($this->once())
                              ->method('read')
                              ->will($this->returnValue('foo'));
        $this->assertEquals('foo', $this->console->read());
    }

    /**
     * @test
     */
    public function usesInputStreamForReadLine()
    {
        $this->mockInputStream->expects($this->once())
                              ->method('readLine')
                              ->will($this->returnValue('foo'));
        $this->assertEquals('foo', $this->console->readLine());
    }

    /**
     * @test
     */
    public function usesOutputStreamForWrite()
    {
        $this->mockOutputStream->expects($this->once())
                               ->method('write')
                               ->with($this->equalTo('foo'));
        $this->mockErrorStream->expects($this->never())
                              ->method('write');
        $this->assertSame($this->console, $this->console->write('foo'));
    }

    /**
     * @test
     */
    public function usesOutputStreamForWriteLine()
    {
        $this->mockOutputStream->expects($this->once())
                               ->method('writeLine')
                               ->with($this->equalTo('foo'));
        $this->mockErrorStream->expects($this->never())
                              ->method('writeLine');
        $this->assertSame($this->console, $this->console->writeLine('foo'));
    }

    /**
     * @test
     */
    public function usesErrorStreamForWriteError()
    {
        $this->mockOutputStream->expects($this->never())
                               ->method('write');
        $this->mockErrorStream->expects($this->once())
                              ->method('write')
                              ->with($this->equalTo('foo'));
        $this->assertSame($this->console, $this->console->writeError('foo'));
    }

    /**
     * @test
     */
    public function usesErrorStreamForWriteErrorLine()
    {
        $this->mockOutputStream->expects($this->never())
                               ->method('writeLine');
        $this->mockErrorStream->expects($this->once())
                              ->method('writeLine')
                              ->with($this->equalTo('foo'));
        $this->assertSame($this->console, $this->console->writeErrorLine('foo'));
    }
}
?>