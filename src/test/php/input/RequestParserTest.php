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
use bovigo\callmap;
use bovigo\callmap\NewInstance;
use stubbles\console\ConsoleAppException;
use stubbles\input\ValueReader;
use stubbles\input\errors\ParamErrors;
use stubbles\streams\memory\MemoryOutputStream;
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
        $this->consoleRequest     = NewInstance::of('stubbles\input\console\ConsoleRequest');
        $this->requestBroker      = NewInstance::stub('stubbles\input\broker\RequestBroker');
        $this->paramErrorMessages = NewInstance::of('stubbles\input\errors\messages\ParamErrorMessages');
        $this->requestParser      = new RequestParser(
                $this->consoleRequest,
                $this->requestBroker,
                $this->paramErrorMessages
        );
    }

    /**
     * @test
     * @expectedException     stubbles\console\ConsoleAppException
     * @expectedExceptionCode 0
     */
    public function throwsConsoleAppExceptionWhenHelpIsRequestedWithDashH()
    {
        $this->consoleRequest->mapCalls(
                ['hasParam' => true,
                 'readEnv'  => ValueReader::forValue('bin/http')
                ]
        );
        $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput');
        callmap\verify($this->requestBroker, 'procure')->wasNeverCalled();
    }

    /**
     * @test
     * @expectedException     stubbles\console\ConsoleAppException
     * @expectedExceptionCode 0
     */
    public function throwsConsoleAppExceptionWhenHelpIsRequestedWithDashDashHelp()
    {
        $this->consoleRequest->mapCalls(
                ['hasParam' => callmap\onConsecutiveCalls(false, true),
                 'readEnv'  => ValueReader::forValue('bin/http')
                ]
        );
        $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput');
        callmap\verify($this->requestBroker, 'procure')->wasNeverCalled();
    }

    /**
     * @test
     */
    public function helpClosureRendersHelpToOutputStream()
    {
        $this->consoleRequest->mapCalls(
                ['hasParam' => true,
                 'readEnv'  => ValueReader::forValue('bin/http')
                ]
        );
        try {
            $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput');
            $this->fail('Excpected stubbles\console\ConsoleAppException');
        } catch (ConsoleAppException $cae) {
            $memoryOutputStream = new MemoryOutputStream();
            $cae->writeTo($memoryOutputStream);
            assertEquals(
                    "Real awesome command line app (c) 2012 Stubbles Development Team
Usage: bin/http [options] [application-id] [other-id]
Options:
   --verbose
   -v
   --bar1         Set the bar option.
   --bar2         Set the other bar option.
   -o WOW_LEVEL   For the wow.
   -u             Set another option.
   -h or --help   Prints this help.

",
                    (string) $memoryOutputStream
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
        assertInstanceOf(
                'org\stubbles\console\test\BrokeredUserInput',
                $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput')
        );
        callmap\verify($this->requestBroker, 'procure')->wasCalledOnce();
    }

    /**
     * @test
     * @expectedException      stubbles\console\ConsoleAppException
     * @expectedExceptionCode  10
     */
    public function failureWhileParsingThrowsConsoleAppException()
    {
        $errors = new ParamErrors();
        $errors->append('bar', 'error_id');
        $this->consoleRequest->mapCalls(
                ['hasParam' => false, 'paramErrors'  => $errors]
        );
        $this->paramErrorMessages->mapCalls(['messageFor' => 'Error, dude!']);
        $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput');
    }

    /**
     * @test
     */
    public function errorClosureRendersErrorToOutputStream()
    {
        $errors = new ParamErrors();
        $errors->append('bar', 'error_id');
        $this->consoleRequest->mapCalls(
                ['hasParam' => false, 'paramErrors'  => $errors]
        );
        $this->paramErrorMessages->mapCalls(['messageFor' => 'Error, dude!']);
        try {
            $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput');
            $this->fail('Excpected stubbles\console\ConsoleAppException');
        } catch (ConsoleAppException $cae) {
            $memoryOutputStream = new MemoryOutputStream();
            $cae->writeTo($memoryOutputStream);
            assertEquals(
                    "bar: Error, dude!\n",
                    (string) $memoryOutputStream
            );
        }
    }
}
