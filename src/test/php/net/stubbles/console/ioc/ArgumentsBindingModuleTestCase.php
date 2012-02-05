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
 * Test for net\stubbles\console\ioc\ArgumentsBindingModule.
 *
 * @group  console
 * @group  console_ioc
 */
class ArgumentsBindingModuleTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ArgumentsBindingModule
     */
    protected $argumentsBindingModule;
    /**
     * backup of $_SERVER['argv']
     *
     * @type  array
     */
    protected $argvBackup;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->argumentsBindingModule = $this->getMock('net\\stubbles\\console\\ioc\\ArgumentsBindingModule',
                                                       array('getopt')
                                        );
        $this->argvBackup             = $_SERVER['argv'];
    }

    /**
     * clean up test environment
     */
    public function tearDown()
    {
        $_SERVER['argv'] = $this->argvBackup;
    }

    /**
     * binds arguments
     *
     * @return  Injector
     */
    protected function bindArguments()
    {
        $injector = new Injector();
        $this->argumentsBindingModule->configure(new Binder($injector));
        return $injector;
    }

    /**
     * @test
     */
    public function argumentsAreBoundAsRecievedWhenNoOptionsDefined()
    {
        $_SERVER['argv'] = array('foo', 'bar', 'baz');
        $injector  = $this->bindArguments();
        $this->assertTrue($injector->hasConstant('argv.0'));
        $this->assertTrue($injector->hasConstant('argv.1'));
        $this->assertEquals('bar', $injector->getConstant('argv.0'));
        $this->assertEquals('baz', $injector->getConstant('argv.1'));
    }

    /**
     * @test
     */
    public function argumentsAreBoundAfterParsingWhenOptionsDefined()
    {
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('n:f::'), $this->equalTo(array('verbose')))
                                     ->will($this->returnValue(array('n' => 'example', 'verbose' => false)));
        $this->argumentsBindingModule->withOptions('n:f::')
                                     ->withLongOptions(array('verbose'));
        $injector = $this->bindArguments();
        $this->assertTrue($injector->hasConstant('argv.n'));
        $this->assertTrue($injector->hasConstant('argv.verbose'));
        $this->assertEquals('example', $injector->getConstant('argv.n'));
        $this->assertFalse($injector->getConstant('argv.verbose'));
    }

    /**
     * @test
     * @expectedException  net\stubbles\lang\exception\ConfigurationException
     */
    public function invalidOptionsThrowConfigurationException()
    {
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('//'), $this->equalTo(array()))
                                     ->will($this->returnValue(false));
        $this->argumentsBindingModule->withOptions('//');
        $this->bindArguments();
    }
}
?>