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
use net\stubbles\lang\exception\IllegalArgumentException;
use net\stubbles\lang\exception\IllegalStateException;
use net\stubbles\lang\exception\IOException;
use net\stubbles\lang\exception\RuntimeException;
use net\stubbles\streams\ResourceInputStream;
/**
 * Input stream to read output of an executed command.
 */
class CommandInputStream extends ResourceInputStream
{
    /**
     * original command
     *
     * @type  string
     */
    protected $command;

    /**
     * constructor
     *
     * @param   resource  $resource
     * @param   string    $command   optional
     * @throws  IllegalArgumentException
     */
    public function __construct($resource, $command = null)
    {
        if (!is_resource($resource) || get_resource_type($resource) !== 'stream') {
            throw new IllegalArgumentException('Resource must be an already opened process resource.');
        }

        $this->setHandle($resource);
        $this->command = $command;
    }

    /**
     * destructor
     */
    public function __destruct()
    {
        try {
            $this->close();
        } catch (\Exception $e) {
            // ignore exception
        }
    }

    /**
     * reads given amount of bytes
     *
     * @param   int  $length  optional  max amount of bytes to read
     * @return  string
     * @throws  IllegalStateException
     * @throws  IOException
     */
    public function read($length = 8192)
    {
        if (null === $this->handle) {
            throw new IllegalStateException('Can not read from closed input stream.');
        }

        $data = @fgets($this->handle, $length);
        if (false === $data) {
            if (!@feof($this->handle)) {
                throw new IOException('Can not read from input stream.');
            }

            return '';
        }

        return $data;
    }

    /**
     * closes the stream
     *
     * @throws  RuntimeException
     */
    public function close()
    {
        if (null === $this->handle) {
            return;
        }

        $returnCode   = pclose($this->handle);
        $this->handle = null;
        if (0 != $returnCode) {
            throw new RuntimeException('Executing command ' . $this->command . ' failed: #' . $returnCode);
        }
    }
}
?>