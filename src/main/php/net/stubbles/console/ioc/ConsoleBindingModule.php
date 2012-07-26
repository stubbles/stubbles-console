<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
namespace net\stubbles\console\ioc;
use net\stubbles\console\ConsoleInputStream;
use net\stubbles\console\ConsoleOutputStream;
use net\stubbles\ioc\Binder;
use net\stubbles\ioc\module\BindingModule;
use net\stubbles\lang\BaseObject;
/**
 * Binding module for console classes.
 */
class ConsoleBindingModule extends BaseObject implements BindingModule
{
    /**
     * configure the binder
     *
     * @param  Binder  $binder
     */
    public function configure(Binder $binder)
    {
        $binder->bind('net\stubbles\streams\InputStream')
               ->named('stdin')
               ->toInstance(ConsoleInputStream::forIn());
        $binder->bind('net\stubbles\streams\OutputStream')
               ->named('stdout')
               ->toInstance(ConsoleOutputStream::forOut());
        $binder->bind('net\stubbles\streams\OutputStream')
               ->named('stderr')
               ->toInstance(ConsoleOutputStream::forError());
        $binder->bind('net\stubbles\console\Executor')
               ->to('net\stubbles\console\ConsoleExecutor');
    }
}
?>