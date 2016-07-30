<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\input;
use bovigo\callmap\NewInstance;
use org\stubbles\console\test\BrokeredUserInput;
use stubbles\input\Request;
use stubbles\input\broker\param\ParamBroker;
use stubbles\ioc\Binder;
use stubbles\ioc\Injector;
use stubbles\streams\InputStream;
use stubbles\streams\OutputStream;

use function bovigo\assert\assert;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
use function bovigo\callmap\verify;
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
        $this->argumentParser = NewInstance::of(ArgumentParser::class);
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
        assert(
                $this->bindArguments()->getConstant($constantName),
                equals($expected)
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
        assert(
                $this->bindArguments()->getConstant($constantName),
                equals($expected)
        );
        verify($this->argumentParser, 'getopt')->received('n:f::', ['verbose']);
    }

    /**
     * @test
     */
    public function bindsRequest()
    {
        assertTrue($this->bindArguments()->hasBinding(Request::class));
    }

    /**
     * @test
     */
    public function bindsConsoleRequest()
    {
        assertTrue($this->bindArguments()->hasBinding(ConsoleRequest::class));
    }

    /**
     * @test
     */
    public function bindsRequestToConsoleRequest()
    {
        assert(
                $this->bindArguments()->getInstance(Request::class),
                isInstanceOf(ConsoleRequest::class)
        );
    }

    /**
     * @test
     */
    public function bindsRequestToBaseConsoleRequest()
    {
        assert(
                $this->bindArguments()->getInstance(Request::class),
                isInstanceOf(BaseConsoleRequest::class)
        );
    }

    /**
     * @test
     */
    public function bindsConsoleRequestToBaseConsoleRequest()
    {
        assert(
                $this->bindArguments()->getInstance(ConsoleRequest::class),
                isInstanceOf(BaseConsoleRequest::class)
        );
    }

    /**
     * @test
     */
    public function bindsRequestAsSingleton()
    {
        $injector = $this->bindArguments();
        assert(
                $injector->getInstance(Request::class),
                isSameAs($injector->getInstance(Request::class))
        );
    }

    /**
     * @test
     */
    public function bindsConsoleRequestAsSingleton()
    {
        $injector = $this->bindArguments();
        assert(
                $injector->getInstance(Request::class),
                isSameAs($injector->getInstance(ConsoleRequest::class))
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
        return $this->bindArguments()->getInstance(Request::class);
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
        assert(
                $this->bindRequest()->readParam($paramName)->unsecure(),
                equals($expected)
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
        assert(
                $this->bindRequest()->readParam($paramName)->unsecure(),
                equals($expected)
        );
        verify($this->argumentParser, 'getopt')->received('n:f::', ['verbose']);
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
        $this->argumentParser->withUserInput(BrokeredUserInput::class);
        $this->argumentParser->mapCalls(['getopt' => []]);
        $injector = $this->bindArguments();
        assertTrue($injector->hasConstant('stubbles.console.input.class'));
        assertTrue($injector->hasBinding(BrokeredUserInput::class));
        verify($this->argumentParser, 'getopt')
                ->received('vo:u:h', ['verbose', 'bar1:', 'bar2:', 'help']);
    }

    /**
     * @since  2.1.0
     * @test
     */
    public function bindsUserInputAsSingleton()
    {
        $this->argumentParser->withUserInput(BrokeredUserInput::class);
        $this->argumentParser->mapCalls(['getopt' => ['bar2' => 'foo', 'o' => 'baz']]);
        $binder = new Binder();
        $this->argumentParser->configure($binder);
        $binder->bind(OutputStream::class)
               ->named('stdout')
               ->toInstance(NewInstance::of(OutputStream::class));
        $binder->bind(OutputStream::class)
               ->named('stderr')
               ->toInstance(NewInstance::of(OutputStream::class));
        $binder->bind(InputStream::class)
               ->named('stdin')
               ->toInstance(NewInstance::of(InputStream::class));
        $binder->bindMap(ParamBroker::class)
               ->withEntry('Mock', NewInstance::of(ParamBroker::class));
        $injector = $binder->getInjector();
        assert(
                $injector->getInstance(BrokeredUserInput::class),
                isSameAs($injector->getInstance(BrokeredUserInput::class))
        );
        verify($this->argumentParser, 'getopt')
                ->received('vo:u:h', ['verbose', 'bar1:', 'bar2:', 'help']);
    }
}
