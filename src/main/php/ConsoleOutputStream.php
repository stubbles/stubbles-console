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
use stubbles\streams\EncodingOutputStream;
use stubbles\streams\OutputStream;
use stubbles\streams\ResourceOutputStream;
/**
 * Class for console output streams.
 */
class ConsoleOutputStream extends ResourceOutputStream
{
    /**
     * holds output stream instance if created
     *
     * @type  \stubbles\streams\OutputStream
     */
    private static $out;
    /**
     * holds error stream instance if created
     *
     * @type  \stubbles\streams\OutputStream
     */
    private static $err;

    /**
     * constructor
     *
     * @param  resource  $descriptor
     */
    protected function __construct($descriptor)
    {
        $this->setHandle($descriptor);
    }

    /**
     * comfort method for getting a console output stream
     *
     * @return  \stubbles\streams\OutputStream
     */
    public static function forOut(): OutputStream
    {
        if (null === self::$out) {
            self::$out = self::create('php://stdout');
        }

        return self::$out;
    }

    /**
     * comfort method for getting a console error stream
     *
     * @return  \stubbles\streams\OutputStream
     */
    public static function forError(): OutputStream
    {
        if (null === self::$err) {
            self::$err = self::create('php://stderr');
        }

        return self::$err;
    }

    /**
     * creates output stream with respect to output encoding
     *
     * @param   string  $target
     * @return  \stubbles\streams\OutputStream
     */
    private static function create(string $target): OutputStream
    {
        $out      = new self(fopen($target, 'w'));
        $encoding = self::detectOutputEncoding();
        if ('UTF-8' !== $encoding) {
            $out = new EncodingOutputStream($out, $encoding . '//IGNORE');
        }

        return $out;
    }

    /**
     * helper method to detect correct output encoding
     *
     * @return  string
     */
    private static function detectOutputEncoding(): string
    {
        $outputEncoding = iconv_get_encoding('output_encoding');
        if ('CP1252' === $outputEncoding && DIRECTORY_SEPARATOR !== '/') {
            $outputEncoding = 'CP850';
        }

        return $outputEncoding;
    }
}
