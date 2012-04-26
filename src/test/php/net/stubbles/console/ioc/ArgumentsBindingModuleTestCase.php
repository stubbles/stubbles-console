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
        $binder = new Binder();
        $this->argumentsBindingModule->configure($binder);
        return $binder->getInjector();
    }

    /**
     * @test
     */
    public function argumentsAreBoundAsRecievedWhenNoOptionsDefined()
    {
        $_SERVER['argv'] = array('foo', 'bar', 'baz');
        $injector        = $this->bindArguments();
        $this->assertTrue($injector->hasConstant('argv.0'));
        $this->assertTrue($injector->hasConstant('argv.1'));
        $this->assertEquals('bar', $injector->getConstant('argv.0'));
        $this->assertEquals('baz', $injector->getConstant('argv.1'));
    }

    /**
     * @test
     * @group  issue_1
     */
    public function argumentsAreBoundAsListWhenNoOptionsDefined()
    {
        $_SERVER['argv'] = array('foo', 'bar', 'baz');
        $injector        = $this->bindArguments();
        $this->assertTrue($injector->hasConstant('argv'));
        $this->assertEquals(array('bar', 'baz'), $injector->getConstant('argv'));
    }

    /**
     * @test
     */
    public function argumentsAreBoundAfterParsingWhenOptionsDefined()
    {
        $_SERVER['argv'] = array('foo.php', '-n', 'example', '--verbose', 'bar');
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('n:f::'), $this->equalTo(array('verbose')))
                                     ->will($this->returnValue(array('n' => 'example', 'verbose' => false)));
        $this->argumentsBindingModule->withOptions('n:f::')
                                     ->withLongOptions(array('verbose'));
        $injector = $this->bindArguments();
        $this->assertTrue($injector->hasConstant('argv.0'));
        $this->assertTrue($injector->hasConstant('argv.1'));
        $this->assertTrue($injector->hasConstant('argv.2'));
        $this->assertTrue($injector->hasConstant('argv.3'));
        $this->assertTrue($injector->hasConstant('argv.n'));
        $this->assertTrue($injector->hasConstant('argv.verbose'));
        $this->assertEquals('-n', $injector->getConstant('argv.0'));
        $this->assertEquals('example', $injector->getConstant('argv.1'));
        $this->assertEquals('--verbose', $injector->getConstant('argv.2'));
        $this->assertEquals('bar', $injector->getConstant('argv.3'));
        $this->assertEquals('example', $injector->getConstant('argv.n'));
        $this->assertFalse($injector->getConstant('argv.verbose'));
    }

    /**
     * @test
     * @group  issue_1
     */
    public function argumentsAreBoundAsListAfterParsingWhenOptionsDefined()
    {
        $_SERVER['argv'] = array('foo.php', '-n', 'example', '--verbose', 'bar');
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('n:f::'), $this->equalTo(array('verbose')))
                                     ->will($this->returnValue(array('n' => 'example', 'verbose' => false)));
        $this->argumentsBindingModule->withOptions('n:f::')
                                     ->withLongOptions(array('verbose'));
        $injector = $this->bindArguments();
        $this->assertTrue($injector->hasConstant('argv'));
        $this->assertEquals(array(0         => '-n',
                                  1         => 'example',
                                  2         => '--verbose',
                                  3         => 'bar',
                                  'n'       => 'example',
                                  'verbose' => false
                            ),
                            $injector->getConstant('argv')
        );
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

    /**
     * @test
     */
    public function optionsContainCIfStubCliEnabled()
    {
        $this->argumentsBindingModule = $this->getMock('net\\stubbles\\console\\ioc\\ArgumentsBindingModule',
                                                       array('getopt'),
                                                       array(true)
                                        );
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('n:f::c:'), $this->equalTo(array('verbose')))
                                     ->will($this->returnValue(array('n' => 'example', 'verbose' => false)));
        $this->argumentsBindingModule->withOptions('n:f::')
                                     ->withLongOptions(array('verbose'));
        $this->bindArguments();
    }

    /**
     * @test
     */
    public function optionsContainCIfStubCliEnabledAndOnlyLongOptionsSet()
    {
        $this->argumentsBindingModule = $this->getMock('net\\stubbles\\console\\ioc\\ArgumentsBindingModule',
                                                       array('getopt'),
                                                       array(true)
                                        );
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('c:'), $this->equalTo(array('verbose')))
                                     ->will($this->returnValue(array('verbose' => false)));
        $this->argumentsBindingModule->withLongOptions(array('verbose'));
        $this->bindArguments();
    }

    /**
     * @test
     */
    public function optionsContainCIfStubCliEnabledAndLongOptionsSetFirst()
    {
        $this->argumentsBindingModule = $this->getMock('net\\stubbles\\console\\ioc\\ArgumentsBindingModule',
                                                       array('getopt'),
                                                       array(true)
                                        );
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('n:f::c:'), $this->equalTo(array('verbose')))
                                     ->will($this->returnValue(array('n' => 'example', 'verbose' => false)));
        $this->argumentsBindingModule->withLongOptions(array('verbose'))
                                     ->withOptions('n:f::');
        $this->bindArguments();
    }

    /**
     * @test
     */
    public function bindsRequestIfAvailable()
    {
        $this->assertTrue($this->bindArguments()->hasBinding('net\\stubbles\\input\\Request'));
    }

    /**
     * @test
     */
    public function bindsRequestToConsoleRequestIfAvailable()
    {
        $this->assertInstanceOf('net\\stubbles\\input\\console\\ConsoleRequest',
                                $this->bindArguments()->getInstance('net\\stubbles\\input\\Request')
        );
    }

    /**
     * @test
     */
    public function bindsConsoleRequestAsSingleton()
    {
        $injector = $this->bindArguments();
        $this->assertSame($injector->getInstance('net\\stubbles\\input\\Request'),
                          $injector->getInstance('net\\stubbles\\input\\Request')
        );
    }

    /**
     * @test
     */
    public function bindsNoUserInputByDefault()
    {
        $injector = $this->bindArguments();
        $this->assertFalse($injector->hasConstant('net.stubbles.console.input.class'));
    }

    /**
     * @test
     */
    public function bindsNoUserInputIfSet()
    {
        $this->argumentsBindingModule->withUserInput('org\\stubbles\\console\\test\\BrokeredUserInput');
        $this->argumentsBindingModule->expects($this->once())
                                     ->method('getopt')
                                     ->with($this->equalTo('o::'), $this->equalTo(array('bar::')))
                                     ->will($this->returnValue(array()));
        $injector = $this->bindArguments();
        $this->assertTrue($injector->hasConstant('net.stubbles.console.input.class'));
        $this->assertTrue($injector->hasBinding('org\\stubbles\\console\\test\\BrokeredUserInput'));
    }
}
?>