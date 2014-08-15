<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\ioc;
use stubbles\console\ConsoleInputStream;
use stubbles\console\ConsoleOutputStream;
use stubbles\ioc\Binder;
use stubbles\ioc\module\BindingModule;
/**
 * Binding module for console classes.
 *
 * @deprecated  since 4.0.0, console bindings will be added by default, will be removed with 5.0.0
 */
class ConsoleBindingModule implements BindingModule
{
    /**
     * configure the binder
     *
     * @param  \stubbles\ioc\Binder  $binder
     */
    public function configure(Binder $binder)
    {
        $binder->bind('stubbles\streams\InputStream')
               ->named('stdin')
               ->toInstance(ConsoleInputStream::forIn());
        $binder->bind('stubbles\streams\OutputStream')
               ->named('stdout')
               ->toInstance(ConsoleOutputStream::forOut());
        $binder->bind('stubbles\streams\OutputStream')
               ->named('stderr')
               ->toInstance(ConsoleOutputStream::forError());
        $binder->bind('stubbles\console\Executor')
               ->to('stubbles\console\ConsoleExecutor');
    }
}
