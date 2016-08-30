<?php
declare(strict_types=1);
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
use stubbles\streams\{InputStream, OutputStream, memory\MemoryInputStream};

use function bovigo\assert\{
    assert,
    assertFalse,
    assertNull,
    assertTrue,
    expect,
    predicate\equals,
    predicate\isSameAs
};
use function bovigo\callmap\{onConsecutiveCalls, verify};
use function stubbles\reflect\annotationsOfConstructorParameter;
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
        $this->console      = new Console(
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
        assert(
                $inParamAnnotations->firstNamed('Named')->getName(),
                equals('stdin')
        );

        $outParamAnnotations = annotationsOfConstructorParameter('out', $this->console);
        assertTrue($outParamAnnotations->contain('Named'));
        assert(
                $outParamAnnotations->firstNamed('Named')->getName(),
                equals('stdout')
        );

        $errParamAnnotations = annotationsOfConstructorParameter('err', $this->console);
        assertTrue($errParamAnnotations->contain('Named'));
        assert(
                $errParamAnnotations->firstNamed('Named')->getName(),
                equals('stderr')
        );
    }

    /**
     * @test
     */
    public function usesInputStreamForRead()
    {
        $this->inputStream->mapCalls(['read' => 'foo']);
        assert($this->console->read(), equals('foo'));
    }

    /**
     * @test
     */
    public function usesInputStreamForReadLine()
    {
        $this->inputStream->mapCalls(['readLine' => 'foo']);
        assert($this->console->readLine(), equals('foo'));
    }

    /**
     * @test
     * @since  2.4.0
     */
    public function usesInputStreamForBytesLeft()
    {
        $this->inputStream->mapCalls(['bytesLeft' => 20]);
        assert($this->console->bytesLeft(), equals(20));
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
        $this->outputStream->mapCalls(['write' => 3]);
        assert($this->console->write('foo'), isSameAs($this->console));
        verify($this->outputStream, 'write')->received('foo');
        verify($this->errorStream, 'write')->wasNeverCalled();
    }

    /**
     * @test
     * @since  7.1.0
     */
    public function usesOutputStreamForWriteWithInputStream()
    {
        $inputStream = new MemoryInputStream("foo\nbar");
        $this->outputStream->mapCalls(['write' => 7]);
        assert($this->console->write($inputStream), isSameAs($this->console));
        verify($this->outputStream, 'write')->received("foo\nbar");
        verify($this->errorStream, 'write')->wasNeverCalled();
    }

    /**
     * @test
     * @since  7.1.0
     */
    public function writeThrowsInvalidArgumentExceptionForNonWriteableArgument()
    {
        expect(function() { $this->console->write(303); })
                ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function usesOutputStreamForWriteLine()
    {
        $this->outputStream->mapCalls(['writeLine' => 4]);
        assert($this->console->writeLine('foo'), isSameAs($this->console));
        verify($this->outputStream, 'writeLine')->received('foo');
        verify($this->errorStream, 'writeLine')->wasNeverCalled();
    }

    /**
     * @test
     * @since  2.4.0
     */
    public function usesOutputStreamForWriteLines()
    {
        $this->outputStream->mapCalls(['writeLines' => 12]);
        assert(
                $this->console->writeLines(['foo', 'bar', 'baz']),
                isSameAs($this->console)
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
        $this->outputStream->mapCalls(['writeLine' => 1]);
        assert($this->console->writeEmptyLine(), isSameAs($this->console));
        verify($this->outputStream, 'writeLine')->received('');
        verify($this->errorStream, 'writeLine')->wasNeverCalled();
    }

    /**
     * @test
     */
    public function usesErrorStreamForWriteError()
    {
        $this->errorStream->mapCalls(['write' => 3]);
        assert($this->console->writeError('foo'), isSameAs($this->console));
        verify($this->errorStream, 'write')->received('foo');
        verify($this->outputStream, 'write')->wasNeverCalled();
    }

    /**
     * @test
     * @since  7.1.0
     */
    public function usesErrorStreamForWriteErrorWithInputStream()
    {
        $inputStream = new MemoryInputStream("foo\nbar");
        $this->errorStream->mapCalls(['write' => 7]);
        assert($this->console->writeError($inputStream), isSameAs($this->console));
        verify($this->errorStream, 'write')->received("foo\nbar");
        verify($this->outputStream, 'write')->wasNeverCalled();
    }

    /**
     * @test
     * @since  7.1.0
     */
    public function writeErrorThrowsInvalidArgumentExceptionForNonWriteableArgument()
    {
        expect(function() { $this->console->writeError(303); })
                ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function usesErrorStreamForWriteErrorLine()
    {
        $this->errorStream->mapCalls(['writeLine' => 4]);
        assert($this->console->writeErrorLine('foo'), isSameAs($this->console));
        verify($this->errorStream, 'writeLine')->received('foo');
        verify($this->outputStream, 'writeLine')->wasNeverCalled();
    }

    /**
     * @test
     * @since  2.4.0
     */
    public function usesErrorStreamForWriteErrorLines()
    {
        $this->errorStream->mapCalls(['writeLines' => 12]);
        assert(
                $this->console->writeErrorLines(['foo', 'bar', 'baz']),
                isSameAs($this->console)
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
        $this->errorStream->mapCalls(['writeLine' => 1]);
        assert($this->console->writeEmptyErrorLine(''), isSameAs($this->console));
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
        $this->outputStream->mapCalls(['write' => 22]);
        assert(
                $this->console->prompt('Please enter a number: ')
                              ->asInt(),
                equals(303)
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
        $this->inputStream->mapCalls(['readLine' => 'some invalid input']);
        $this->outputStream->mapCalls(['write' => 23]);
        assertNull(
                $this->console->prompt('Please enter something: ', $paramErrors)
                        ->when(function() { return false; }, 'ERROR')
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
        assert($this->console->readValue()->asInt(), equals(303));
    }

    /**
     * @since  2.1.0
     * @group  issue_13
     * @test
     */
    public function readValueEnrichesParamErrors()
    {
        $paramErrors = new ParamErrors();
        $this->inputStream->mapCalls(['readLine' => 'some invalid input']);
        assertNull($this->console->readValue($paramErrors)
                ->when(function() { return false; }, 'ERROR')
        );
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
        $this->outputStream->mapCalls(['write' => 25]);
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
        $this->outputStream->mapCalls(['write' => 25]);
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
        $this->outputStream->mapCalls(['write' => 25]);
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
        $this->outputStream->mapCalls(['write' => 25]);
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
        $this->outputStream->mapCalls(['write' => 25]);
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
        $this->outputStream->mapCalls(['write' => 25]);
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
