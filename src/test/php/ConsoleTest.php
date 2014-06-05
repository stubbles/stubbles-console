<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console;
use stubbles\input\errors\ParamErrors;
use stubbles\lang;
/**
 * Test for stubbles\console\Console.
 *
 * @group  console
 */
class ConsoleTest extends \PHPUnit_Framework_TestCase
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
        $this->mockInputStream  = $this->getMock('stubbles\streams\InputStream');
        $this->mockOutputStream = $this->getMock('stubbles\streams\OutputStream');
        $this->mockErrorStream  = $this->getMock('stubbles\streams\OutputStream');
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
        $constructor = lang\reflectConstructor($this->console);
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
     * @since  2.4.0
     */
    public function usesInputStreamForBytesLeft()
    {
        $this->mockInputStream->expects($this->once())
                              ->method('bytesLeft')
                              ->will($this->returnValue(20));
        $this->assertEquals(20, $this->console->bytesLeft());
    }

    /**
     * @test
     * @since  2.4.0
     */
    public function usesInputStreamForEof()
    {
        $this->mockInputStream->expects($this->once())
                              ->method('eof')
                              ->will($this->returnValue(true));
        $this->assertTrue($this->console->eof());
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
     * @since  2.4.0
     */
    public function usesOutputStreamForWriteLines()
    {
        $this->mockOutputStream->expects($this->at(0))
                               ->method('writeLine')
                               ->with($this->equalTo('foo'));
        $this->mockOutputStream->expects($this->at(1))
                               ->method('writeLine')
                               ->with($this->equalTo('bar'));
        $this->mockOutputStream->expects($this->at(2))
                               ->method('writeLine')
                               ->with($this->equalTo('baz'));
        $this->mockErrorStream->expects($this->never())
                              ->method('writeLine');
        $this->assertSame($this->console,
                          $this->console->writeLines(['foo', 'bar', 'baz'])
        );
    }

    /**
     * @test
     * @since  2.6.0
     */
    public function usesOutputStreamForWriteEmptyLine()
    {
        $this->mockOutputStream->expects($this->once())
                               ->method('writeLine')
                               ->with($this->equalTo(''));
        $this->mockErrorStream->expects($this->never())
                              ->method('writeLine');
        $this->assertSame($this->console, $this->console->writeEmptyLine());
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

    /**
     * @test
     * @since  2.4.0
     */
    public function usesErrorStreamForWriteErrorLines()
    {
        $this->mockOutputStream->expects($this->never())
                               ->method('writeLine');
        $this->mockErrorStream->expects($this->at(0))
                              ->method('writeLine')
                              ->with($this->equalTo('foo'));
        $this->mockErrorStream->expects($this->at(1))
                              ->method('writeLine')
                              ->with($this->equalTo('bar'));
        $this->mockErrorStream->expects($this->at(2))
                              ->method('writeLine')
                              ->with($this->equalTo('baz'));
        $this->assertSame($this->console, $this->console->writeErrorLines(['foo', 'bar', 'baz']));
    }

    /**
     * @test
     * @since  2.6.0
     */
    public function usesErrorStreamForWriteEmptyErrorLine()
    {
        $this->mockOutputStream->expects($this->never())
                               ->method('writeLine');
        $this->mockErrorStream->expects($this->once())
                              ->method('writeLine')
                              ->with($this->equalTo(''));
        $this->assertSame($this->console, $this->console->writeEmptyErrorLine(''));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function promptWritesMessageToOutputStreamAndReturnsValueFromInputStream()
    {
        $this->mockOutputStream->expects($this->once())
                               ->method('write')
                               ->with($this->equalTo('Please enter a number: '));
        $this->mockInputStream->expects($this->once())
                              ->method('readLine')
                              ->will($this->returnValue('303'));
        $this->assertEquals(303,
                            $this->console->prompt('Please enter a number: ')
                                          ->asInt()
        );
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function promptEnrichesParamErrors()
    {
        $paramErrors = new ParamErrors();
        $this->mockInputStream->expects($this->once())
                              ->method('readLine')
                              ->will($this->returnValue('no date here'));
        $this->assertNull($this->console->prompt('Please enter a number: ', $paramErrors)
                                        ->asHttpUri()
        );
        $this->assertTrue($paramErrors->existFor('stdin'));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function readValueReturnsValueFromInputStream()
    {
        $this->mockInputStream->expects($this->once())
                              ->method('readLine')
                              ->will($this->returnValue('303'));
        $this->assertEquals(303,
                            $this->console->readValue()
                                          ->asInt()
        );
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function readValueEnrichesParamErrors()
    {
        $paramErrors = new ParamErrors();
        $this->mockInputStream->expects($this->once())
                              ->method('readLine')
                              ->will($this->returnValue('no date here'));
        $this->assertNull($this->console->readValue($paramErrors)
                                        ->asHttpUri()
        );
        $this->assertTrue($paramErrors->existFor('stdin'));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function confirmReturnsTrueWhenInputValueIsLowercaseY()
    {
        $this->mockOutputStream->expects($this->once())
                               ->method('write')
                               ->with($this->equalTo('Do you want to continue: '));
        $this->mockInputStream->expects($this->once())
                              ->method('readLine')
                              ->will($this->returnValue('y'));
        $this->assertTrue($this->console->confirm('Do you want to continue: '));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function confirmReturnsTrueWhenInputValueIsUppercaseY()
    {
        $this->mockOutputStream->expects($this->once())
                               ->method('write')
                               ->with($this->equalTo('Do you want to continue: '));
        $this->mockInputStream->expects($this->once())
                              ->method('readLine')
                              ->will($this->returnValue('Y'));
        $this->assertTrue($this->console->confirm('Do you want to continue: '));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function confirmReturnsFalseWhenInputValueIsLowercaseN()
    {
        $this->mockOutputStream->expects($this->once())
                               ->method('write')
                               ->with($this->equalTo('Do you want to continue: '));
        $this->mockInputStream->expects($this->once())
                              ->method('readLine')
                              ->will($this->returnValue('n'));
        $this->assertFalse($this->console->confirm('Do you want to continue: '));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function confirmReturnsFalseWhenInputValueIsUppercaseN()
    {
        $this->mockOutputStream->expects($this->once())
                               ->method('write')
                               ->with($this->equalTo('Do you want to continue: '));
        $this->mockInputStream->expects($this->once())
                              ->method('readLine')
                              ->will($this->returnValue('N'));
        $this->assertFalse($this->console->confirm('Do you want to continue: '));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function confirmRepeatsQuestionUntilValidInput()
    {
        $this->mockOutputStream->expects($this->exactly(3))
                               ->method('write')
                               ->with($this->equalTo('Do you want to continue: '));
        $this->mockInputStream->expects($this->exactly(3))
                              ->method('readLine')
                              ->will($this->onConsecutiveCalls('foo', '', 'n'));
        $this->assertFalse($this->console->confirm('Do you want to continue: '));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function confirmUsesDefaultWhenInputIsEmpty()
    {
        $this->mockOutputStream->expects($this->exactly(2))
                               ->method('write')
                               ->with($this->equalTo('Do you want to continue: '));
        $this->mockInputStream->expects($this->exactly(2))
                              ->method('readLine')
                              ->will($this->onConsecutiveCalls('foo', ''));
        $this->assertFalse($this->console->confirm('Do you want to continue: ', 'n'));
    }

    /**
     * @test
     * @since  2.4.0
     */
    public function closeClosesAllStreams()
    {
        $this->mockInputStream->expects($this->once())
                               ->method('close');
        $this->mockOutputStream->expects($this->once())
                               ->method('close');
        $this->mockErrorStream->expects($this->once())
                              ->method('close');
        $this->console->close();
    }
}
