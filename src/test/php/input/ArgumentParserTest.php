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
namespace stubbles\console\input;
use bovigo\callmap\NewCallable;
use bovigo\callmap\NewInstance;
use org\stubbles\console\test\BrokeredUserInput;
use stubbles\input\Request;
use stubbles\input\broker\param\ParamBroker;
use stubbles\ioc\Binder;
use stubbles\ioc\Injector;
use stubbles\streams\InputStream;
use stubbles\streams\OutputStream;

use function bovigo\assert\{
    assert,
    assertFalse,
    assertTrue,
    predicate\equals,
    predicate\isInstanceOf,
    predicate\isSameAs
};
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
    private $argumentParser;
    /**
     * @type  \bovigo\callmap\FunctionProxy
     */
    private $getopt;
    /**
     * backup of $_SERVER['argv']
     *
     * @type  array
     */
    private $argvBackup;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->argvBackup     = $_SERVER['argv'];
        $this->getopt         = NewCallable::stub('getopt');
        $this->argumentParser = (new ArgumentParser())
                ->withCliOptionParser($this->getopt);
    }

    /**
     * clean up test environment
     */
    public function tearDown()
    {
        $_SERVER['argv'] = $this->argvBackup;
    }

    public function boundNonOptionArguments(): array
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
    public function argumentsAreBoundWhenNoOptionsDefined($expected, string $constantName)
    {
        $_SERVER['argv'] = ['foo.php', 'bar', 'baz'];
        assert(
                $this->bindArguments()->getConstant($constantName),
                equals($expected)
        );
    }

    public function boundOptionArguments(): array
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
    public function argumentsAreBoundAfterParsingWhenOptionsDefined($expected, string $constantName)
    {
        $_SERVER['argv'] = ['foo.php', '-n', 'example', '--verbose', 'install'];
        $this->getopt->mapCall(['n' => 'example', 'verbose' => false]);
        $this->argumentParser->withOptions('n:f::')->withLongOptions(['verbose']);
        assert(
                $this->bindArguments()->getConstant($constantName),
                equals($expected)
        );
        verify($this->getopt)->received('n:f::', ['verbose']);
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
    public function bindsConsoleRequestToConsoleRequest()
    {
        assert(
                $this->bindArguments()->getInstance(ConsoleRequest::class),
                isInstanceOf(ConsoleRequest::class)
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

    protected function bindArguments(): Injector
    {
        $binder = new Binder();
        $this->argumentParser->configure($binder);
        return $binder->getInjector();
    }

    private function bindRequest(): Request
    {
        return $this->bindArguments()->getInstance(Request::class);
    }

    public function requestArgumentsWhenNoOptionsDefined(): array
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
    public function argumentsAvailableViaRequestWhenNoOptionsDefined(
            string $expected,
            string $paramName
    ) {
        $_SERVER['argv'] = ['foo', 'bar', 'baz'];
        assert(
                $this->bindRequest()->readParam($paramName)->unsecure(),
                equals($expected)
        );
    }

    public function requestArgumentsWhenOptionsDefined(): array
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
    public function argumentsAvailableViaRequestAfterParsingWhenOptionsDefined(
            $expected,
            string $paramName
    ) {
        $_SERVER['argv'] = ['foo.php', '-n', 'example', '--verbose', 'bar'];
        $this->getopt->mapCall(['n' => 'example', 'verbose' => false]);
        $this->argumentParser->withOptions('n:f::')->withLongOptions(['verbose']);
        assert(
                $this->bindRequest()->readParam($paramName)->unsecure(),
                equals($expected)
        );
        verify($this->getopt)->received('n:f::', ['verbose']);
    }

    /**
     * @test
     * @expectedException  RuntimeException
     */
    public function invalidOptionsThrowConfigurationException()
    {
        $this->getopt->mapCall(false);
        $this->argumentParser->withOptions('//');
        $this->bindArguments();
    }

    /**
     * @test
     */
    public function bindsNoUserInputByDefault()
    {
        assertFalse($this->bindArguments()->hasConstant(
                'stubbles.console.input.class'
        ));
    }

    /**
     * @test
     */
    public function bindsUserInputIfSet()
    {
        $this->argumentParser->withUserInput(BrokeredUserInput::class);
        $this->getopt->mapCall([]);
        $injector = $this->bindArguments();
        assertTrue($injector->hasConstant('stubbles.console.input.class'));
        assertTrue($injector->hasBinding(BrokeredUserInput::class));
        verify($this->getopt)->received(
                'vo:u:h',
                ['verbose', 'bar1:', 'bar2:', 'help']
        );
    }

    /**
     * @since  2.1.0
     * @test
     */
    public function bindsUserInputAsSingleton()
    {
        $this->argumentParser->withUserInput(BrokeredUserInput::class);
        $this->getopt->mapCall(['bar2' => 'foo', 'o' => 'baz']);
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
        verify($this->getopt)->received(
                'vo:u:h',
                ['verbose', 'bar1:', 'bar2:', 'help']
        );
    }
}
