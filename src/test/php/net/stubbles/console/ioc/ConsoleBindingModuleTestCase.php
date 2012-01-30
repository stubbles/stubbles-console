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
use net\stubbles\ioc\Binder;
use net\stubbles\ioc\Injector;
/**
 * Test for net\stubbles\console\ioc\ConsoleBindingModule.
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
        $injector = new Injector();
        $this->consoleBindingModule->configure(new Binder($injector));
        return $injector;
    }

    /**
     * @test
     */
    public function bindingsConfiguredForInputStream()
    {
        $injector = $this->configure();
        $this->assertTrue($injector->hasBinding('net\\stubbles\\streams\\InputStream', 'stdin'));
        $this->assertInstanceOf('net\\stubbles\\streams\\InputStream',
                                $injector->getInstance('net\\stubbles\\streams\\InputStream', 'stdin')
        );
    }

    /**
     * @test
     */
    public function bindingsConfiguredForOutputStream()
    {
        $injector = $this->configure();
        $this->assertTrue($injector->hasBinding('net\\stubbles\\streams\\OutputStream', 'stdout'));
        $this->assertInstanceOf('net\\stubbles\\streams\\OutputStream',
                                $injector->getInstance('net\\stubbles\\streams\\OutputStream', 'stdout')
        );
    }

    /**
     * @test
     */
    public function bindingsConfiguredForErrorStream()
    {
        $injector = $this->configure();
        $this->assertTrue($injector->hasBinding('net\\stubbles\\streams\\OutputStream', 'stderr'));
        $this->assertInstanceOf('net\\stubbles\\streams\\OutputStream',
                                $injector->getInstance('net\\stubbles\\streams\\OutputStream', 'stderr')
        );
    }

    /**
     * @test
     */
    public function bindingsConfiguredForExecutor()
    {
        $injector = $this->configure();
        $this->assertTrue($injector->hasBinding('net\\stubbles\\console\\Executor'));
        $this->assertInstanceOf('net\\stubbles\\console\\Executor',
                                $injector->getInstance('net\\stubbles\\console\\Executor')
        );
    }

    /**
     * @test
     */
    public function inputStreamIsSingleton()
    {
        $injector = $this->configure();
        $this->assertSame($injector->getInstance('net\\stubbles\\streams\\InputStream', 'stdin'),
                          $injector->getInstance('net\\stubbles\\streams\\InputStream', 'stdin')
        );
    }

    /**
     * @test
     */
    public function outputStreamIsSingleton()
    {
        $injector = $this->configure();
        $this->assertSame($injector->getInstance('net\\stubbles\\streams\\OutputStream', 'stdout'),
                          $injector->getInstance('net\\stubbles\\streams\\OutputStream', 'stdout')
        );
    }

    /**
     * @test
     */
    public function errorStreamIsSingleton()
    {
        $injector = $this->configure();
        $this->assertSame($injector->getInstance('net\\stubbles\\streams\\OutputStream', 'stderr'),
                          $injector->getInstance('net\\stubbles\\streams\\OutputStream', 'stderr')
        );
    }
}
?>