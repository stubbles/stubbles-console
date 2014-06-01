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
use stubbles\streams\EncodingOutputStream;
use stubbles\streams\ResourceOutputStream;
/**
 * Class for console output streams.
 */
class ConsoleOutputStream extends ResourceOutputStream
{
    /**
     * holds output stream instance if created
     *
     * @type  OutputStream
     */
    private static $out;
    /**
     * holds error stream instance if created
     *
     * @type  OutputStream
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
     * @return  OutputStream
     */
    public static function forOut()
    {
        if (null === self::$out) {
            self::$out      = new self(fopen('php://stdout', 'w'));
            $outputEncoding = self::detectOutputEncoding();
            if ('UTF-8' !== $outputEncoding) {
                self::$out = new EncodingOutputStream(self::$out, $outputEncoding . '//IGNORE');
            }
        }

        return self::$out;
    }

    /**
     * comfort method for getting a console error stream
     *
     * @return  OutputStream
     */
    public static function forError()
    {
        if (null === self::$err) {
            self::$err      = new self(fopen('php://stderr', 'w'));
            $outputEncoding = self::detectOutputEncoding();
            if ('UTF-8' !== $outputEncoding) {
                self::$err = new EncodingOutputStream(self::$err, $outputEncoding . '//IGNORE');
            }
        }

        return self::$err;
    }

    /**
     * helper method to detect correct output encoding
     *
     * @return  string
     */
    private static function detectOutputEncoding()
    {
        $outputEncoding = iconv_get_encoding('output_encoding');
        if ('CP1252' === $outputEncoding && DIRECTORY_SEPARATOR !== '/') {
            $outputEncoding = 'CP850';
        }

        return $outputEncoding;
    }
}
