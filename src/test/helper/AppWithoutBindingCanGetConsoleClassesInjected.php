<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles/console
 */
namespace org\stubbles\console\test;
use stubbles\console\ConsoleApp;
use stubbles\console\Executor;
use stubbles\streams\InputStream;
use stubbles\streams\OutputStream;
/**
 * Description of A
 *
 * @since  4.0.0
 */
class AppWithoutBindingCanGetConsoleClassesInjected extends ConsoleApp
{
    public $in;

    public $out;

    public $err;

    public $executor;

    /**
     *
     * @param  \stubbles\streams\InputStream   $in
     * @param  \stubbles\streams\OutputStream  $out
     * @param  \stubbles\streams\OutputStream  $err
     * @param  \stubbles\console\Executor      $executor
     * @Named{in}('stdin')
     * @Named{out}('stdout')
     * @Named{err}('stderr')
     */
    public function __construct(
            InputStream $in,
            OutputStream $out,
            OutputStream $err,
            Executor $executor) {
        $this->in       = $in;
        $this->out      = $out;
        $this->err      = $err;
        $this->executor = $executor;
    }

    public function run()
    {
        // intentionally empty
    }
}
