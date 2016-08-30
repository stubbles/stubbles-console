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
use stubbles\input\{Param, ValueReader, errors\ParamErrors};
use stubbles\streams\{InputStream, OutputStream};
use stubbles\values\Value;

use function stubbles\streams\copy;
use function stubbles\values\typeOf;
/**
 * Interface to read and write on command line.
 */
class Console implements InputStream
{
    /**
     * stresm to read data from
     *
     * @type  \stubbles\streams\InputStream
     */
    private $in;
    /**
     * stream to write default data to
     *
     * @type  \stubbles\streams\OutputStream
     */
    private $out;
    /**
     * stream to write error data to
     *
     * @type  \stubbles\streams\OutputStream
     */
    private $err;

    /**
     * constructor
     *
     * @param  \stubbles\streams\InputStream   $in   stresm to read data from
     * @param  \stubbles\streams\OutputStream  $out  stream to write default data to
     * @param  \stubbles\streams\OutputStream  $err  stream to write error data to
     * @Named{in}('stdin')
     * @Named{out}('stdout')
     * @Named{err}('stderr')
     */
    public function __construct(InputStream $in, OutputStream $out, OutputStream $err)
    {
        $this->in  = $in;
        $this->out = $out;
        $this->err = $err;
    }

    /**
     * prompt user for entering a value
     *
     * Echhos given message to stdout and expects the user to enter a value on
     * stdin. In case you need access to error messages that may happen during
     * value validation you need to supply <ParamErrors>, errors will be
     * accumulated therein under the param name stdin.
     *
     * @api
     * @param   string                              $message      message to show before requesting user input
     * @param   \stubbles\input\errors\ParamErrors  $paramErrors  collection to add any errors to
     * @return  \stubbles\input\ValueReader
     * @since   2.1.0
     */
    public function prompt(string $message, ParamErrors $paramErrors = null): ValueReader
    {
        $this->out->write($message);
        return $this->readValue($paramErrors);
    }

    /**
     * ask the user to confirm something
     *
     * Repeats the message until user enters <y> or <n> (case insensitive). In
     * case a default is given and the users enters nothing this default will
     * be used - if the default is <y> it will return <true>, and <false>
     * otherwise.
     *
     * @api
     * @param   string  $message  message to show before requesting user input
     * @param   string  $default  default selection if user enters nothing
     * @return  bool
     * @since   2.1.0
     */
    public function confirm(string $message, string $default = null): bool
    {
        $result = null;
        while (null === $result) {
            $result = $this->prompt($message)->ifIsOneOf(['y', 'Y', 'n', 'N', '']);
            if ('' === $result) {
                $result = ((null !== $default) ? ($default) : (null));
            }
        }

        return strtolower($result) === 'y';
    }

    /**
     * reads a value from command line input
     *
     * Read a value entered on stdin. Returns a <ValueReader> which can be used
     * to get a typed value. In case you need access to error messages that may
     * happen during value validation you need to supply <ParamErrors>, errors
     * will be accumulated therein under the param name stdin.
     *
     * @api
     * @param   \stubbles\input\errors\ParamErrors  $paramErrors  collection to add any errors to
     * @return  \stubbles\input\ValueReader
     * @since   2.1.0
     */
    public function readValue(ParamErrors $paramErrors = null): ValueReader
    {
        if (null === $paramErrors) {
            return ValueReader::forValue($this->in->readLine());
        }

        return new ValueReader($paramErrors, 'stdin', Value::of($this->in->readLine()));
    }

    /**
     * reads given amount of bytes
     *
     * @api
     * @param   int  $length  max amount of bytes to read
     * @return  string
     */
    public function read(int $length = 8192): string
    {
        return $this->in->read($length);
    }

    /**
     * reads given amount of bytes or until next line break
     *
     * @api
     * @param   int  $length  max amount of bytes to read
     * @return  string
     */
    public function readLine(int $length = 8192): string
    {
        return $this->in->readLine($length);
    }

    /**
     * returns the amount of byted left to be read
     *
     * @return  int
     * @since   2.4.0
     */
    public function bytesLeft(): int
    {
        return $this->in->bytesLeft();
    }

    /**
     * returns true if the stream pointer is at EOF
     *
     * @return  bool
     * @since   2.4.0
     */
    public function eof(): bool
    {
        return $this->in->eof();
    }

    /**
     * writes given bytes
     *
     * In case $bytes are an instance of InputStream all bytes from the input
     * stream starting at its current offset until its end will be copied to
     * the standard output stream.
     *
     * @api
     * @param   string|InputStream  $bytes
     * @return  \stubbles\console\Console
     * @throws  \InvalidArgumentException
     */
    public function write($bytes): self
    {
        if (is_string($bytes)) {
            $this->out->write($bytes);
        } elseif ($bytes instanceof InputStream) {
            copy($bytes)->to($this->out);
        } else {
            throw new \InvalidArgumentException(
                    'Given bytes are neither a string nor an instance of '
                    . InputStream::class . ' but of type ' . typeOf($bytes)
            );
        }

        return $this;
    }

    /**
     * writes given bytes and appends a line break
     *
     * @api
     * @param   string  $bytes
     * @return  \stubbles\console\Console
     */
    public function writeLine(string $bytes): self
    {
        $this->out->writeLine($bytes);
        return $this;
    }

    /**
     * writes given lines and appends a line break after each line
     *
     * @param   string[]  $lines
     * @return  \stubbles\console\Console
     * @since   2.4.0
     */
    public function writeLines(array $lines): self
    {
        $this->out->writeLines($lines);
        return $this;
    }

    /**
     * writes empty line and appends a line break
     *
     * @return  \stubbles\console\Console
     * @since   2.6.0
     */
    public function writeEmptyLine(): self
    {
        $this->writeLine('');
        return $this;
    }

    /**
     * writes given bytes
     *
     * In case $bytes are an instance of InputStream all bytes from the input
     * stream starting at its current offset until its end will be copied to
     * the error output stream.
     *
     * @api
     * @param   string|InputStream  $bytes
     * @return  \stubbles\console\Console
     * @throws  \InvalidArgumentException
     */
    public function writeError($bytes): self
    {
        if (is_string($bytes)) {
            $this->err->write($bytes);
        } elseif ($bytes instanceof InputStream) {
            copy($bytes)->to($this->err);
        } else {
            throw new \InvalidArgumentException(
                    'Given bytes are neither a string nor an instance of '
                    . InputStream::class . ' but of type ' . typeOf($bytes)
            );
        }

        return $this;
    }

    /**
     * writes given bytes and appends a line break
     *
     * @api
     * @param   string  $bytes
     * @return  \stubbles\console\Console
     */
    public function writeErrorLine(string $bytes): self
    {
        $this->err->writeLine($bytes);
        return $this;
    }

    /**
     * writes given lines and appends a line break after each line
     *
     * @param   string[]  $lines
     * @return  \stubbles\console\Console
     * @since   2.4.0
     */
    public function writeErrorLines(array $lines): self
    {
        $this->err->writeLines($lines);
        return $this;
    }

    /**
     * writes empty line and appends a line break
     *
     * @return  \stubbles\console\Console
     * @since   2.6.0
     */
    public function writeEmptyErrorLine(): self
    {
        $this->writeErrorLine('');
        return $this;
    }

    /**
     * closes all underlying streams
     *
     * @since  2.4.0
     */
    public function close()
    {
        $this->in->close();
        $this->out->close();
        $this->err->close();
    }
}
