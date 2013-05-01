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
use net\stubbles\input\ParamErrors;
use net\stubbles\lang\reflect\ReflectionObject;
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
        $this->mockInputStream  = $this->getMock('net\stubbles\streams\InputStream');
        $this->mockOutputStream = $this->getMock('net\stubbles\streams\OutputStream');
        $this->mockErrorStream  = $this->getMock('net\stubbles\streams\OutputStream');
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
        $constructor = ReflectionObject::fromInstance($this->console)
                                       ->getConstructor();
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
                                        ->asDate()
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
                                        ->asDate()
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
}
?>