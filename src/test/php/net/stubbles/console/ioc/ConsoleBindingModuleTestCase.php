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
use stubbles\ioc\Binder;
use stubbles\ioc\Injector;
/**
 * Test for stubbles\console\ioc\ConsoleBindingModule.
 *
 * @group  console
 * @group  console_ioc
 */
class ConsoleBindingModuleTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ConsoleBindingModule
     */
    protected $consoleBindingModule;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->consoleBindingModule = new ConsoleBindingModule();
    }

    /**
     * helper method
     *
     * @return  Injector
     */
    protected function configure()
    {
        $binder = new Binder();
        $this->consoleBindingModule->configure($binder);
        return $binder->getInjector();
    }

    /**
     * @test
     */
    public function bindingsConfiguredForInputStream()
    {
        $injector = $this->configure();
        $this->assertTrue($injector->hasBinding('stubbles\streams\InputStream', 'stdin'));
        $this->assertInstanceOf('stubbles\streams\InputStream',
                                $injector->getInstance('stubbles\streams\InputStream', 'stdin')
        );
    }

    /**
     * @test
     */
    public function bindingsConfiguredForOutputStream()
    {
        $injector = $this->configure();
        $this->assertTrue($injector->hasBinding('stubbles\streams\OutputStream', 'stdout'));
        $this->assertInstanceOf('stubbles\streams\OutputStream',
                                $injector->getInstance('stubbles\streams\OutputStream', 'stdout')
        );
    }

    /**
     * @test
     */
    public function bindingsConfiguredForErrorStream()
    {
        $injector = $this->configure();
        $this->assertTrue($injector->hasBinding('stubbles\streams\OutputStream', 'stderr'));
        $this->assertInstanceOf('stubbles\streams\OutputStream',
                                $injector->getInstance('stubbles\streams\OutputStream', 'stderr')
        );
    }

    /**
     * @test
     */
    public function bindingsConfiguredForExecutor()
    {
        $injector = $this->configure();
        $this->assertTrue($injector->hasBinding('stubbles\console\Executor'));
        $this->assertInstanceOf('stubbles\console\Executor',
                                $injector->getInstance('stubbles\console\Executor')
        );
    }

    /**
     * @test
     */
    public function inputStreamIsSingleton()
    {
        $injector = $this->configure();
        $this->assertSame($injector->getInstance('stubbles\streams\InputStream', 'stdin'),
                          $injector->getInstance('stubbles\streams\InputStream', 'stdin')
        );
    }

    /**
     * @test
     */
    public function outputStreamIsSingleton()
    {
        $injector = $this->configure();
        $this->assertSame($injector->getInstance('stubbles\streams\OutputStream', 'stdout'),
                          $injector->getInstance('stubbles\streams\OutputStream', 'stdout')
        );
    }

    /**
     * @test
     */
    public function errorStreamIsSingleton()
    {
        $injector = $this->configure();
        $this->assertSame($injector->getInstance('stubbles\streams\OutputStream', 'stderr'),
                          $injector->getInstance('stubbles\streams\OutputStream', 'stderr')
        );
    }
}
