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
    public function __construct(string $original = null)
    {
        $this->current = $this->original = empty($original) ? getcwd() : $original;
    }

    /**
     * returns current working directory
     *
     * @return  string
     */
    public function current(): string
    {
        return $this->current;
    }

    /**
     * change current working directory to given target
     *
     * Please note that this really has a side effect - it doesn't just change
     * a value inside this object, it really changed the global current working
     * directory for the whole PHP application!
     *
     * @param   string  $target  directory to change current working directory to
     * @return  bool  whether change was successful
     */
    public function changeTo(string $target): bool
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
    public function restoreOriginal(): bool
    {
        return $this->changeTo($this->original);
    }
}
