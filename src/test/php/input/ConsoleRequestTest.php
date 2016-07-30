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
use stubbles\input\ValueReader;
use stubbles\input\ValueValidator;
use stubbles\input\errors\ParamErrors;

use function bovigo\assert\{
    assert,
    assertFalse,
    assertTrue,
    predicate\equals,
    predicate\isInstanceOf
};
/**
 * Tests for stubbles\console\input\ConsoleRequest.
 *
 * @since  2.0.0
 * @group  input
 */
class ConsoleRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ConsoleRequest
     */
    private $consoleRequest;
    /**
     * backup of $_SERVER['argv']
     *
     * @type array
     */
    private $serverBackup;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->serverBackup       = $_SERVER;
        $this->consoleRequest = new ConsoleRequest(
                ['foo' => 'bar', 'roland' => 'TB-303'],
                ['SCRIPT_NAME' => 'example.php', 'PHP_SELF'    => 'example.php']
        );
    }

    /**
     * clean up test environment
     */
    public function tearDown()
    {
        $_SERVER = $this->serverBackup;
    }

    /**
     * @test
     */
    public function requestMethodIsAlwaysCli()
    {
        assert($this->consoleRequest->method(), equals('cli'));
    }

    /**
     * @test
     */
    public function returnsListOfParamNames()
    {
        assert($this->consoleRequest->paramNames(), equals(['foo', 'roland']));
    }

    /**
     * @test
     */
    public function createFromRawSourceUsesServerArgsForParams()
    {
        $_SERVER['argv'] = ['foo' => 'bar', 'roland' => 'TB-303'];
        assert(
                ConsoleRequest::fromRawSource()->paramNames(),
                equals(['foo', 'roland'])
        );
    }

    /**
     * @test
     */
    public function returnsListOfEnvNames()
    {
        assert(
                $this->consoleRequest->envNames(),
                equals(['SCRIPT_NAME', 'PHP_SELF'])
        );
    }

    /**
     * @test
     */
    public function returnsEnvErrors()
    {
        assert(
                $this->consoleRequest->envErrors(),
                isInstanceOf(ParamErrors::class)
        );
    }

    /**
     * @test
     */
    public function returnsFalseOnCheckForNonExistingEnv()
    {
        assertFalse($this->consoleRequest->hasEnv('baz'));
    }

    /**
     * @test
     */
    public function returnsTrueOnCheckForExistingEnv()
    {
        assertTrue($this->consoleRequest->hasEnv('SCRIPT_NAME'));
    }

    /**
     * @test
     */
    public function validateEnvReturnsValueValidator()
    {
        assert(
                $this->consoleRequest->validateEnv('SCRIPT_NAME'),
                isInstanceOf(ValueValidator::class)
        );
    }

    /**
     * @test
     */
    public function validateEnvReturnsValueValidatorForNonExistingParam()
    {
        assert(
                $this->consoleRequest->validateEnv('baz'),
                isInstanceOf(ValueValidator::class)
        );
    }

    /**
     * @test
     */
    public function readEnvReturnsValueReader()
    {
        assert(
                $this->consoleRequest->readEnv('SCRIPT_NAME'),
                isInstanceOf(ValueReader::class)
        );
    }

    /**
     * @test
     */
    public function readEnvReturnsValueReaderForNonExistingParam()
    {
        assert(
                $this->consoleRequest->readEnv('baz'),
                isInstanceOf(ValueReader::class)
        );
    }

    /**
     * @test
     */
    public function createFromRawSourceUsesServerForEnv()
    {
        $_SERVER = [
                'argv'        => ['foo' => 'bar', 'roland' => 'TB-303'],
                'SCRIPT_NAME' => 'example.php'
        ];
        assert(
                ConsoleRequest::fromRawSource()->envNames(),
                equals(['argv', 'SCRIPT_NAME'])
        );
    }
}
