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
use bovigo\callmap\NewInstance;
use stubbles\ioc\Binder;
use stubbles\ioc\Injector;
/**
 * Test for stubbles\console\ioc\ArgumentParser.
 *
 * @group  console
 * @group  console_ioc
 */
class ArgumentParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  \stubbles\console\ioc\ArgumentParser
     */
    protected $argumentParser;
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
        $this->argumentParser = NewInstance::of('stubbles\console\ioc\ArgumentParser');
        $this->argvBackup     = $_SERVER['argv'];
    }

    /**
     * clean up test environment
     */
    public function tearDown()
    {
        $_SERVER['argv'] = $this->argvBackup;
    }

    /**
     * @return  array
     */
    public function boundNonOptionArguments()
    {
        return [
            ['bar', 'argv.0'],
            ['baz', 'argv.1'],
            [['argv.0' => 'bar', 'argv.1' => 'baz'], 'argv'],
        ];
    }

    /**
     * @test
     * @dataProvider  boundNonOptionArguments
     */
    public function argumentsAreBoundWhenNoOptionsDefined($expected, $constantName)
    {
        $_SERVER['argv'] = ['foo.php', 'bar', 'baz'];
        assertEquals(
                $expected,
                $this->bindArguments()->getConstant($constantName)
        );
    }

    /**
     * @return  array
     */
    public function boundOptionArguments()
    {
        return [
            ['example', 'argv.n'],
            [false, 'argv.verbose'],
            ['install', 'argv.0'],
            [['n' => 'example', 'verbose' => false, 'argv.0' => 'install'], 'argv']
        ];
    }

    /**
     * @test
     * @dataProvider  boundOptionArguments
     */
    public function argumentsAreBoundAfterParsingWhenOptionsDefined($expected, $constantName)
    {
        $_SERVER['argv'] = ['foo.php', '-n', 'example', '--verbose', 'install'];
        $this->argumentParser->mapCalls(['getopt' => ['n' => 'example', 'verbose' => false]]);
        $this->argumentParser->withOptions('n:f::')->withLongOptions(['verbose']);
        assertEquals(
                $expected,
                $this->bindArguments()->getConstant($constantName)
        );
        assertEquals(
                ['n:f::', ['verbose']],
                $this->argumentParser->argumentsReceivedFor('getopt')
        );
    }

    /**
     * @test
     */
    public function bindsRequest()
    {
        assertTrue(
                $this->bindArguments()->hasBinding('stubbles\input\Request')
        );
    }

    /**
     * @test
     */
    public function bindsConsoleRequest()
    {
        assertTrue(
                $this->bindArguments()->hasBinding('stubbles\input\console\ConsoleRequest')
        );
    }

    /**
     * @test
     */
    public function bindsRequestToConsoleRequest()
    {
        assertInstanceOf(
                'stubbles\input\console\ConsoleRequest',
                 $this->bindArguments()->getInstance('stubbles\input\Request')
        );
    }

    /**
     * @test
     */
    public function bindsRequestToBaseConsoleRequest()
    {
        assertInstanceOf(
                'stubbles\input\console\BaseConsoleRequest',
                $this->bindArguments()->getInstance('stubbles\input\Request')
        );
    }

    /**
     * @test
     */
    public function bindsConsoleRequestToBaseConsoleRequest()
    {
        assertInstanceOf(
                'stubbles\input\console\BaseConsoleRequest',
                $this->bindArguments()->getInstance('stubbles\input\console\ConsoleRequest')
        );
    }

    /**
     * @test
     */
    public function bindsRequestAsSingleton()
    {
        $injector = $this->bindArguments();
        assertSame(
                $injector->getInstance('stubbles\input\Request'),
                $injector->getInstance('stubbles\input\Request')
        );
    }

    /**
     * @test
     */
    public function bindsConsoleRequestAsSingleton()
    {
        $injector = $this->bindArguments();
        assertSame(
                $injector->getInstance('stubbles\input\Request'),
                $injector->getInstance('stubbles\input\console\ConsoleRequest')
        );
    }

    /**
     * binds arguments
     *
     * @return  Injector
     */
    protected function bindArguments()
    {
        $binder = new Binder();
        $this->argumentParser->configure($binder);
        return $binder->getInjector();
    }

    /**
     * binds request
     *
     * @return  stubbles\input\Request
     */
    private function bindRequest()
    {
        return $this->bindArguments()->getInstance('stubbles\input\Request');
    }

    /**
     * @return  array
     */
    public function requestArgumentsWhenNoOptionsDefined()
    {
        return [
            ['bar', 'argv.0'],
            ['baz', 'argv.1']
        ];
    }

    /**
     * @test
     * @dataProvider  requestArgumentsWhenNoOptionsDefined
     */
    public function argumentsAvailableViaRequestWhenNoOptionsDefined($expected, $paramName)
    {
        $_SERVER['argv'] = ['foo', 'bar', 'baz'];
        assertEquals(
                $expected,
                $this->bindRequest()->readParam($paramName)->unsecure()
        );
    }

    /**
     * @return  array
     */
    public function requestArgumentsWhenOptionsDefined()
    {
        return [
            ['example', 'n'],
            [false, 'verbose'],
            ['bar', 'argv.0']
        ];
    }

    /**
     * @test
     * @dataProvider  requestArgumentsWhenOptionsDefined
     */
    public function argumentsAvailableViaRequestAfterParsingWhenOptionsDefined($expected, $paramName)
    {
        $_SERVER['argv'] = ['foo.php', '-n', 'example', '--verbose', 'bar'];
        $this->argumentParser->mapCalls(['getopt' => ['n' => 'example', 'verbose' => false]]);
        $this->argumentParser->withOptions('n:f::')->withLongOptions(['verbose']);
        assertEquals(
                $expected,
                $this->bindRequest()->readParam($paramName)->unsecure()
        );
        assertEquals(
                ['n:f::', ['verbose']],
                $this->argumentParser->argumentsReceivedFor('getopt')
        );
    }

    /**
     * @test
     * @expectedException  RuntimeException
     */
    public function invalidOptionsThrowConfigurationException()
    {
        $this->argumentParser->mapCalls(['getopt' => false]);
        $this->argumentParser->withOptions('//');
        $this->bindArguments();
    }

    /**
     * @test
     */
    public function optionsContainCIfStubCliEnabled()
    {
        $this->argumentParser = NewInstance::of(
                'stubbles\console\ioc\ArgumentParser',
                [true]
        );
        $this->argumentParser->mapCalls(['getopt' => ['n' => 'example', 'verbose' => false]]);
        $this->argumentParser->withOptions('n:f::')->withLongOptions(['verbose']);
        $this->bindArguments();
        assertEquals(
                ['n:f::c:', ['verbose']],
                $this->argumentParser->argumentsReceivedFor('getopt')
        );
    }

    /**
     * @test
     */
    public function optionsContainCIfStubCliEnabledAndOnlyLongOptionsSet()
    {
        $this->argumentParser = NewInstance::of(
                'stubbles\console\ioc\ArgumentParser',
                [true]
        );
        $this->argumentParser->mapCalls(['getopt' => ['verbose' => false]]);
        $this->argumentParser->withLongOptions(['verbose']);
        $this->bindArguments();
        assertEquals(
                ['c:', ['verbose']],
                $this->argumentParser->argumentsReceivedFor('getopt')
        );
    }

    /**
     * @test
     */
    public function optionsContainCIfStubCliEnabledAndLongOptionsSetFirst()
    {
        $this->argumentParser = NewInstance::of(
                'stubbles\console\ioc\ArgumentParser',
                [true]
        );
        $this->argumentParser->mapCalls(['getopt' => ['n' => 'example', 'verbose' => false]]);
        $this->argumentParser->withLongOptions(['verbose'])->withOptions('n:f::');
        $this->bindArguments();
        assertEquals(
                ['n:f::c:', ['verbose']],
                $this->argumentParser->argumentsReceivedFor('getopt')
        );
    }

    /**
     * @test
     */
    public function bindsNoUserInputByDefault()
    {
        assertFalse(
                $this->bindArguments()->hasConstant('stubbles.console.input.class')
        );
    }

    /**
     * @test
     */
    public function bindsUserInputIfSet()
    {
        $this->argumentParser->withUserInput('org\stubbles\console\test\BrokeredUserInput');
        $this->argumentParser->mapCalls(['getopt' => []]);
        $injector = $this->bindArguments();
        assertTrue($injector->hasConstant('stubbles.console.input.class'));
        assertTrue($injector->hasBinding('org\stubbles\console\test\BrokeredUserInput'));#
        assertEquals(
                ['vo:u:h', ['verbose', 'bar1:', 'bar2:', 'help']],
                $this->argumentParser->argumentsReceivedFor('getopt')
        );
    }

    /**
     * @since  2.1.0
     * @test
     */
    public function bindsUserInputAsSingleton()
    {
        $this->argumentParser->withUserInput('org\stubbles\console\test\BrokeredUserInput');
        $this->argumentParser->mapCalls(['getopt' => ['bar2' => 'foo', 'o' => 'baz']]);
        $binder = new Binder();
        $this->argumentParser->configure($binder);
        $binder->bind('stubbles\streams\OutputStream')
               ->named('stdout')
               ->toInstance(NewInstance::of('stubbles\streams\OutputStream'));
        $binder->bind('stubbles\streams\OutputStream')
               ->named('stderr')
               ->toInstance(NewInstance::of('stubbles\streams\OutputStream'));
        $binder->bind('stubbles\streams\InputStream')
               ->named('stdin')
               ->toInstance(NewInstance::of('stubbles\streams\InputStream'));
        $binder->bindMap('stubbles\input\broker\param\ParamBroker')
               ->withEntry('Mock', NewInstance::of('stubbles\input\broker\param\ParamBroker'));
        $injector = $binder->getInjector();
        assertSame(
                $injector->getInstance('org\stubbles\console\test\BrokeredUserInput'),
                $injector->getInstance('org\stubbles\console\test\BrokeredUserInput')
        );
        assertEquals(
                ['vo:u:h', ['verbose', 'bar1:', 'bar2:', 'help']],
                $this->argumentParser->argumentsReceivedFor('getopt')
        );
    }
}
