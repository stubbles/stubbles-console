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
use net\stubbles\lang\BaseObject;
use net\stubbles\streams\InputStream;
use net\stubbles\streams\OutputStream;
/**
 * Interface to read and write on command line.
 */
class Console extends BaseObject
{
    /**
     * stresm to read data from
     *
     * @type  InputStream
     */
    private $in;
    /**
     * stream to write data to
     *
     * @type  OutputStream
     */
    private $out;

    /**
     * constructor
     *
     * @param  InputStream   $in   stresm to read data from
     * @param  OutputStream  $out  stream to write data to
     * @Inject
     * @Named{in}('stdin')
     * @Named{out}('stdout')
     */
    public function __construct(InputStream $in, OutputStream $out)
    {
        $this->in  = $in;
        $this->out = $out;
    }

    /**
     * reads given amount of bytes
     *
     * @param   int  $length  max amount of bytes to read
     * @return  string
     */
    public function read($length = 8192)
    {
        return $this->in->read($length);
    }

    /**
     * reads given amount of bytes or until next line break
     *
     * @param   int  $length  max amount of bytes to read
     * @return  string
     */
    public function readLine($length = 8192)
    {
        return $this->in->readLine($length);
    }

    /**
     * writes given bytes
     *
     * @param   string  $bytes
     * @return  Console
     */
    public function write($bytes)
    {
        $this->out->write($bytes);
        return $this;
    }

    /**
     * writes given bytes and appends a line break
     *
     * @param   string  $bytes
     * @return  Console
     */
    public function writeLine($bytes)
    {
        $this->out->writeLine($bytes);
        return $this;
    }
}
?>