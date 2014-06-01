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
use stubbles\streams\DecodingInputStream;
use stubbles\streams\ResourceInputStream;
/**
 * Class for console input streams.
 */
class ConsoleInputStream extends ResourceInputStream
{
    /**
     * holds input stream instance if created
     *
     * @type  InputStream
     */
    private static $in;

    /**
     * constructor
     */
    protected function __construct()
    {
        $this->setHandle(fopen('php://stdin', 'r'));
    }

    /**
     * comfort method for getting a console output stream
     *
     * @return  InputStream
     */
    public static function forIn()
    {
        if (null === self::$in) {
            self::$in      = new self();
            $inputEncoding = iconv_get_encoding('input_encoding');
            if ('UTF-8' !== $inputEncoding) {
                self::$in = new DecodingInputStream(self::$in, $inputEncoding);
            }
        }

        return self::$in;
    }
}
