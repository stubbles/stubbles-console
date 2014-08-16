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
use stubbles\console\ConsoleAppException;
use stubbles\input\ValueReader;
use stubbles\input\errors\ParamError;
use stubbles\input\errors\ParamErrors;
use stubbles\lang;
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
    private $mockConsoleRequest;
    /**
     * request broker
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequestBroker;
    /**
     * mocked param error messages list
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockParamErrorMessages;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->mockConsoleRequest     = $this->getMock('stubbles\input\console\ConsoleRequest');
        $this->mockRequestBroker      = $this->getMock('stubbles\input\broker\RequestBroker');
        $this->mockParamErrorMessages = $this->getMock('stubbles\input\errors\messages\ParamErrorMessages');
        $this->requestParser          = new RequestParser(
                $this->mockConsoleRequest,
                $this->mockRequestBroker,
                $this->mockParamErrorMessages
        );
    }

    /**
     * @test
     */
    public function annotationsPresentOnConstructor()
    {
        $this->assertTrue(
                lang\reflectConstructor($this->requestParser)->hasAnnotation('Inject')
        );
    }

    /**
     * @test
     * @expectedException     stubbles\console\ConsoleAppException
     * @expectedExceptionCode 0
     */
    public function throwsConsoleAppExceptionWhenHelpIsRequestedWithDashH()
    {
        $this->mockConsoleRequest->expects($this->once())
                                 ->method('hasParam')
                                 ->will($this->returnValue(true));
        $this->mockConsoleRequest->expects($this->once())
                                 ->method('readEnv')
                                 ->will($this->returnValue(ValueReader::forValue('bin/http')));
        $this->mockRequestBroker->expects($this->never())
                                ->method('procure');
        $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput');
    }

    /**
     * @test
     * @expectedException     stubbles\console\ConsoleAppException
     * @expectedExceptionCode 0
     */
    public function throwsConsoleAppExceptionWhenHelpIsRequestedWithDashDashHelp()
    {
        $this->mockConsoleRequest->expects($this->exactly(2))
                                 ->method('hasParam')
                                 ->will($this->onConsecutiveCalls(false, true));
        $this->mockConsoleRequest->expects($this->once())
                                 ->method('readEnv')
                                 ->will($this->returnValue(ValueReader::forValue('bin/http')));
        $this->mockRequestBroker->expects($this->never())
                                ->method('procure');
        $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput');
    }

    /**
     * @test
     */
    public function helpClosureRendersHelpToOutputStream()
    {
        $this->mockConsoleRequest->expects($this->once())
                                 ->method('hasParam')
                                 ->will($this->returnValue(true));
        $this->mockConsoleRequest->expects($this->once())
                                 ->method('readEnv')
                                 ->will($this->returnValue(ValueReader::forValue('bin/http')));
        try {
            $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput');
            $this->fail('Excpected stubbles\console\ConsoleAppException');
        } catch (ConsoleAppException $cae) {
            $memoryOutputStream = new MemoryOutputStream();
            $cae->writeTo($memoryOutputStream);
            $this->assertEquals(
                    "Real awesome command line app (c) 2012 Stubbles Development Team
Usage: bin/http [options] [application-id]
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
        $this->mockConsoleRequest->expects($this->any())
                                 ->method('hasParam')
                                 ->will($this->returnValue(false));
        $this->mockConsoleRequest->expects($this->once())
                                 ->method('paramErrors')
                                 ->will($this->returnValue(new ParamErrors()));
        $this->mockRequestBroker->expects($this->once())
                                ->method('procure');
        $this->assertInstanceOf(
                'org\stubbles\console\test\BrokeredUserInput',
                $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput')
        );
    }

    /**
     * @test
     * @expectedException      stubbles\console\ConsoleAppException
     * @expectedExceptionCode  10
     */
    public function failureWhileParsingThrowsConsoleAppException()
    {
        $this->mockConsoleRequest->expects($this->any())
                                 ->method('hasParam')
                                 ->will($this->returnValue(false));
        $errors = new ParamErrors();
        $errors->append('bar', 'error_id');
        $this->mockConsoleRequest->expects($this->exactly(2))
                                 ->method('paramErrors')
                                 ->will($this->returnValue($errors));
        $this->mockParamErrorMessages->expects($this->once())
                                     ->method('messageFor')
                                     ->with($this->equalTo(new ParamError('error_id')))
                                     ->will($this->returnValue('Error, dude!'));
        $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput');
    }

    /**
     * @test
     */
    public function errorClosureRendersErrorToOutputStream()
    {
        $this->mockConsoleRequest->expects($this->any())
                                 ->method('hasParam')
                                 ->will($this->returnValue(false));
        $errors = new ParamErrors();
        $errors->append('bar', 'error_id');
        $this->mockConsoleRequest->expects($this->any())
                                 ->method('paramErrors')
                                 ->will($this->returnValue($errors));
        $this->mockParamErrorMessages->expects($this->any())
                                     ->method('messageFor')
                                     ->with($this->equalTo(new ParamError('error_id')))
                                     ->will($this->returnValue('Error, dude!'));
        try {
            $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput');
            $this->fail('Excpected stubbles\console\ConsoleAppException');
        } catch (ConsoleAppException $cae) {
            $memoryOutputStream = new MemoryOutputStream();
            $cae->writeTo($memoryOutputStream);
            $this->assertEquals(
                    "bar: Error, dude!\n",
                    (string) $memoryOutputStream
            );
        }
    }
}
