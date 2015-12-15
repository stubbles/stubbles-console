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
use bovigo\callmap\NewInstance;
use stubbles\input\errors\ParamErrors;
use stubbles\streams\InputStream;
use stubbles\streams\OutputStream;

use function bovigo\callmap\onConsecutiveCalls;
use function bovigo\callmap\verify;
use function stubbles\lang\reflect\annotationsOfConstructorParameter;
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
        $this->inputStream  = NewInstance::of(InputStream::class);
        $this->outputStream = NewInstance::of(OutputStream::class);
        $this->errorStream  = NewInstance::of(OutputStream::class);
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
        $inParamAnnotations = annotationsOfConstructorParameter('in', $this->console);
        assertTrue($inParamAnnotations->contain('Named'));
        assertEquals(
                'stdin',
                $inParamAnnotations->firstNamed('Named')->getName()
        );

        $outParamAnnotations = annotationsOfConstructorParameter('out', $this->console);
        assertTrue($outParamAnnotations->contain('Named'));
        assertEquals(
                'stdout',
                $outParamAnnotations->firstNamed('Named')->getName()
        );

        $errParamAnnotations = annotationsOfConstructorParameter('err', $this->console);
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
        verify($this->outputStream, 'write')->received('foo');
        verify($this->errorStream, 'write')->wasNeverCalled();
    }

    /**
     * @test
     */
    public function usesOutputStreamForWriteLine()
    {
        assertSame($this->console, $this->console->writeLine('foo'));
        verify($this->outputStream, 'writeLine')->received('foo');
        verify($this->errorStream, 'writeLine')->wasNeverCalled();
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
        verify($this->outputStream, 'writeLines')->received(['foo', 'bar', 'baz']);
        verify($this->errorStream, 'writeLines')->wasNeverCalled();
    }

    /**
     * @test
     * @since  2.6.0
     */
    public function usesOutputStreamForWriteEmptyLine()
    {
        assertSame($this->console, $this->console->writeEmptyLine());
        verify($this->outputStream, 'writeLine')->received('');
        verify($this->errorStream, 'writeLine')->wasNeverCalled();
    }

    /**
     * @test
     */
    public function usesErrorStreamForWriteError()
    {
        assertSame($this->console, $this->console->writeError('foo'));
        verify($this->errorStream, 'write')->received('foo');
        verify($this->outputStream, 'write')->wasNeverCalled();
    }

    /**
     * @test
     */
    public function usesErrorStreamForWriteErrorLine()
    {
        assertSame($this->console, $this->console->writeErrorLine('foo'));
        verify($this->errorStream, 'writeLine')->received('foo');
        verify($this->outputStream, 'writeLine')->wasNeverCalled();
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
        verify($this->errorStream, 'writeLines')->received(['foo', 'bar', 'baz']);
        verify($this->outputStream, 'writeLines')->wasNeverCalled();
    }

    /**
     * @test
     * @since  2.6.0
     */
    public function usesErrorStreamForWriteEmptyErrorLine()
    {
        assertSame($this->console, $this->console->writeEmptyErrorLine(''));
        verify($this->errorStream, 'writeLine')->received('');
        verify($this->outputStream, 'writeLine')->wasNeverCalled();
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
        verify($this->outputStream, 'write')
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
        verify($this->outputStream, 'write')
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
        verify($this->outputStream, 'write')
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
        $this->inputStream->mapCalls([
                'readLine' => onConsecutiveCalls('foo', '', 'n')
        ]);
        assertFalse($this->console->confirm('Do you want to continue: '));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function confirmUsesDefaultWhenInputIsEmpty()
    {
        $this->inputStream->mapCalls([
                'readLine' => onConsecutiveCalls('foo', '')
        ]);
        assertFalse($this->console->confirm('Do you want to continue: ', 'n'));
    }

    /**
     * @test
     * @since  2.4.0
     */
    public function closeClosesAllStreams()
    {
        $this->console->close();
        verify($this->inputStream, 'close')->wasCalledOnce();
        verify($this->outputStream, 'close')->wasCalledOnce();
        verify($this->errorStream, 'close')->wasCalledOnce();
    }
}
