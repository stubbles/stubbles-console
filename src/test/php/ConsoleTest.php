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
use bovigo\callmap;
use bovigo\callmap\NewInstance;
use stubbles\input\errors\ParamErrors;
use stubbles\lang\reflect;
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
    private $inputStream;
    /**
     * mocked output stream
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $outputStream;
    /**
     * mocked error stream
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $errorStream;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->inputStream  = NewInstance::of('stubbles\streams\InputStream');
        $this->outputStream = NewInstance::of('stubbles\streams\OutputStream');
        $this->errorStream  = NewInstance::of('stubbles\streams\OutputStream');
        $this->console          = new Console(
                $this->inputStream,
                $this->outputStream,
                $this->errorStream
        );
    }

    /**
     * @test
     */
    public function annotationsPresentOnConstructor()
    {
        $inParamAnnotations = reflect\annotationsOfConstructorParameter('in', $this->console);
        assertTrue($inParamAnnotations->contain('Named'));
        assertEquals(
                'stdin',
                $inParamAnnotations->firstNamed('Named')->getName()
        );

        $outParamAnnotations = reflect\annotationsOfConstructorParameter('out', $this->console);
        assertTrue($outParamAnnotations->contain('Named'));
        assertEquals(
                'stdout',
                $outParamAnnotations->firstNamed('Named')->getName()
        );

        $errParamAnnotations = reflect\annotationsOfConstructorParameter('err', $this->console);
        assertTrue($errParamAnnotations->contain('Named'));
        assertEquals(
                'stderr',
                $errParamAnnotations->firstNamed('Named')->getName()
        );
    }

    /**
     * @test
     */
    public function usesInputStreamForRead()
    {
        $this->inputStream->mapCalls(['read' => 'foo']);
        assertEquals('foo', $this->console->read());
    }

    /**
     * @test
     */
    public function usesInputStreamForReadLine()
    {
        $this->inputStream->mapCalls(['readLine' => 'foo']);
        assertEquals('foo', $this->console->readLine());
    }

    /**
     * @test
     * @since  2.4.0
     */
    public function usesInputStreamForBytesLeft()
    {
        $this->inputStream->mapCalls(['bytesLeft' => 20]);
        assertEquals(20, $this->console->bytesLeft());
    }

    /**
     * @test
     * @since  2.4.0
     */
    public function usesInputStreamForEof()
    {
        $this->inputStream->mapCalls(['eof' => true]);
        assertTrue($this->console->eof());
    }

    /**
     * @test
     */
    public function usesOutputStreamForWrite()
    {
        assertSame($this->console, $this->console->write('foo'));
        callmap\verify($this->outputStream, 'write')->received('foo');
        callmap\verify($this->errorStream, 'write')->wasNeverCalled();
    }

    /**
     * @test
     */
    public function usesOutputStreamForWriteLine()
    {
        assertSame($this->console, $this->console->writeLine('foo'));
        callmap\verify($this->outputStream, 'writeLine')->received('foo');
        callmap\verify($this->errorStream, 'writeLine')->wasNeverCalled();
    }

    /**
     * @test
     * @since  2.4.0
     */
    public function usesOutputStreamForWriteLines()
    {
        assertSame(
                $this->console,
                $this->console->writeLines(['foo', 'bar', 'baz'])
        );
        callmap\verify($this->outputStream, 'writeLines')->received(['foo', 'bar', 'baz']);
        callmap\verify($this->errorStream, 'writeLines')->wasNeverCalled();
    }

    /**
     * @test
     * @since  2.6.0
     */
    public function usesOutputStreamForWriteEmptyLine()
    {
        assertSame($this->console, $this->console->writeEmptyLine());
        callmap\verify($this->outputStream, 'writeLine')->received('');
        callmap\verify($this->errorStream, 'writeLine')->wasNeverCalled();
    }

    /**
     * @test
     */
    public function usesErrorStreamForWriteError()
    {
        assertSame($this->console, $this->console->writeError('foo'));
        callmap\verify($this->errorStream, 'write')->received('foo');
        callmap\verify($this->outputStream, 'write')->wasNeverCalled();
    }

    /**
     * @test
     */
    public function usesErrorStreamForWriteErrorLine()
    {
        assertSame($this->console, $this->console->writeErrorLine('foo'));
        callmap\verify($this->errorStream, 'writeLine')->received('foo');
        callmap\verify($this->outputStream, 'writeLine')->wasNeverCalled();
    }

    /**
     * @test
     * @since  2.4.0
     */
    public function usesErrorStreamForWriteErrorLines()
    {
        assertSame(
                $this->console,
                $this->console->writeErrorLines(['foo', 'bar', 'baz'])
        );
        callmap\verify($this->errorStream, 'writeLines')->received(['foo', 'bar', 'baz']);
        callmap\verify($this->outputStream, 'writeLines')->wasNeverCalled();
    }

    /**
     * @test
     * @since  2.6.0
     */
    public function usesErrorStreamForWriteEmptyErrorLine()
    {
        assertSame($this->console, $this->console->writeEmptyErrorLine(''));
        callmap\verify($this->errorStream, 'writeLine')->received('');
        callmap\verify($this->outputStream, 'writeLine')->wasNeverCalled();
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function promptWritesMessageToOutputStreamAndReturnsValueFromInputStream()
    {
        $this->inputStream->mapCalls(['readLine' => '303']);
        assertEquals(
                303,
                $this->console->prompt('Please enter a number: ')
                              ->asInt()
        );
        callmap\verify($this->outputStream, 'write')
                ->received('Please enter a number: ');
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function promptEnrichesParamErrors()
    {
        $paramErrors = new ParamErrors();
        $this->inputStream->mapCalls(['readLine' => 'no date here']);
        assertNull(
                $this->console->prompt('Please enter a number: ', $paramErrors)
                        ->asHttpUri()
        );
        assertTrue($paramErrors->existFor('stdin'));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function readValueReturnsValueFromInputStream()
    {
        $this->inputStream->mapCalls(['readLine' => '303']);
        assertEquals(303, $this->console->readValue()->asInt());
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function readValueEnrichesParamErrors()
    {
        $paramErrors = new ParamErrors();
        $this->inputStream->mapCalls(['readLine' => 'no date here']);
        assertNull($this->console->readValue($paramErrors)->asHttpUri());
        assertTrue($paramErrors->existFor('stdin'));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function confirmReturnsTrueWhenInputValueIsLowercaseY()
    {
        $this->inputStream->mapCalls(['readLine' => 'y']);
        assertTrue($this->console->confirm('Do you want to continue: '));
        callmap\verify($this->outputStream, 'write')
                ->received('Do you want to continue: ');
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function confirmReturnsTrueWhenInputValueIsUppercaseY()
    {
        $this->inputStream->mapCalls(['readLine' => 'Y']);
        assertTrue($this->console->confirm('Do you want to continue: '));
        callmap\verify($this->outputStream, 'write')
                ->received('Do you want to continue: ');
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function confirmReturnsFalseWhenInputValueIsLowercaseN()
    {
        $this->inputStream->mapCalls(['readLine' => 'n']);
        assertFalse($this->console->confirm('Do you want to continue: '));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function confirmReturnsFalseWhenInputValueIsUppercaseN()
    {
        $this->inputStream->mapCalls(['readLine' => 'N']);
        assertFalse($this->console->confirm('Do you want to continue: '));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function confirmRepeatsQuestionUntilValidInput()
    {
        $this->inputStream->mapCalls(['readLine' => callmap\onConsecutiveCalls('foo', '', 'n')]);
        assertFalse($this->console->confirm('Do you want to continue: '));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function confirmUsesDefaultWhenInputIsEmpty()
    {
        $this->inputStream->mapCalls(['readLine' => callmap\onConsecutiveCalls('foo', '')]);
        assertFalse($this->console->confirm('Do you want to continue: ', 'n'));
    }

    /**
     * @test
     * @since  2.4.0
     */
    public function closeClosesAllStreams()
    {
        $this->console->close();
        callmap\verify($this->inputStream, 'close')->wasCalledOnce();
        callmap\verify($this->outputStream, 'close')->wasCalledOnce();
        callmap\verify($this->errorStream, 'close')->wasCalledOnce();
    }
}
