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
use stubbles\input\ValueReader;
use stubbles\input\broker\RequestBroker;
use stubbles\input\errors\ParamErrors;
use stubbles\input\errors\messages\ParamErrorMessages;

use function bovigo\assert\assert;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\callmap\onConsecutiveCalls;
use function bovigo\callmap\verify;
/**
 * Test for stubbles\console\input\RequestParser.
 *
 * @since  2.0.0
 * @group  input
 */
class RequestParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  RequestParser
     */
    private $requestParser;
    /**
     * console request instance
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $consoleRequest;
    /**
     * request broker
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestBroker;
    /**
     * mocked param error messages list
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $paramErrorMessages;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->consoleRequest     = NewInstance::of(ConsoleRequest::class);
        $this->requestBroker      = NewInstance::stub(RequestBroker::class);
        $this->paramErrorMessages = NewInstance::of(ParamErrorMessages::class);
        $this->requestParser      = new RequestParser(
                $this->consoleRequest,
                $this->requestBroker,
                $this->paramErrorMessages
        );
    }

    /**
     * @test
     * @expectedException     stubbles\console\input\HelpScreen
     * @expectedExceptionCode 0
     */
    public function throwsHelpScreenWhenHelpIsRequestedWithDashH()
    {
        $this->consoleRequest->mapCalls(
                ['hasParam' => true,
                 'readEnv'  => ValueReader::forValue('bin/http')
                ]
        );
        $this->requestParser->parseTo(BrokeredUserInput::class);
        verify($this->requestBroker, 'procure')->wasNeverCalled();
    }

    /**
     * @test
     * @expectedException     stubbles\console\input\HelpScreen
     * @expectedExceptionCode 0
     */
    public function throwsHelpScreenWhenHelpIsRequestedWithDashDashHelp()
    {
        $this->consoleRequest->mapCalls(
                ['hasParam' => onConsecutiveCalls(false, true),
                 'readEnv'  => ValueReader::forValue('bin/http')
                ]
        );
        $this->requestParser->parseTo(BrokeredUserInput::class);
        verify($this->requestBroker, 'procure')->wasNeverCalled();
    }

    /**
     * @test
     */
    public function helpscreenContainsUsageInfo()
    {
        $this->consoleRequest->mapCalls(
                ['hasParam' => true,
                 'readEnv'  => ValueReader::forValue('bin/http')
                ]
        );
        try {
            $this->requestParser->parseTo(BrokeredUserInput::class);
            fail('Excpected ' . HelpScreen::class . ', got none');
        } catch (HelpScreen $helpscreen) {
            assert(
                    $helpscreen->getMessage(),
                    equals("Real awesome command line app (c) 2012 Stubbles Development Team
Usage: bin/http [options] [application-id] [other-id]
Options:
   --verbose
   -v
   --bar1         Set the bar option.
   --bar2         Set the other bar option.
   -o WOW_LEVEL   For the wow.
   -u             Set another option.
   -h or --help   Prints this help.
")
            );
        }
    }

    /**
     * @test
     */
    public function successfulParseReturnsInstance()
    {
        $this->consoleRequest->mapCalls(
                ['hasParam'     => false,
                 'paramErrors'  => new ParamErrors()
                ]
        );
        assert(
                $this->requestParser->parseTo(BrokeredUserInput::class),
                isInstanceOf(BrokeredUserInput::class)
        );
        verify($this->requestBroker, 'procure')->wasCalledOnce();
    }

    /**
     * @test
     * @expectedException      stubbles\console\input\InvalidOptionValue
     * @expectedExceptionMessage  bar: Error, dude
     */
    public function failureWhileParsingThrowsInvalidOptionValue()
    {
        $errors = new ParamErrors();
        $errors->append('bar', 'error_id');
        $this->consoleRequest->mapCalls(
                ['hasParam' => false, 'paramErrors'  => $errors]
        );
        $this->paramErrorMessages->mapCalls(['messageFor' => 'Error, dude!']);
        $this->requestParser->parseTo(BrokeredUserInput::class);
    }
}
