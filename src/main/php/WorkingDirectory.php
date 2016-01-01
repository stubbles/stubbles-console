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
/**
 * Provides access to the current working directory.
 *
 * @since  5.1.0
 */
class WorkingDirectory
{
    /**
     * current working directory
     *
     * @type  string
     */
    private $current;
    /**
     * original working directory when script was started
     *
     * @type  string
     */
    private $original;

    /**
     * constructor
     *
     * When no original is given the result of getcwd() will be used.
     *
     * @param  string  $original  working directory when script was started  optional
     * @Named('stubbles.cwd')
     */
    public function __construct($original = null)
    {
        $this->current = $this->original = empty($original) ? getcwd() : $original;
    }

    /**
     * returns current working directory
     *
     * @return  string
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * change current working directory to given target
     *
     * @param   string  $target  directory to change current working directory to
     * @return  bool  whether change was successful
     */
    public function changeTo($target)
    {
        if (@chdir($target) === true) {
            $this->current = $target;
            return true;
        }

        return false;
    }

    /**
     * restores current working directory back to the original working directory
     *
     * @return  bool  whether restoring was successful
     */
    public function restoreOriginal()
    {
        return $this->changeTo($this->original);
    }
}
