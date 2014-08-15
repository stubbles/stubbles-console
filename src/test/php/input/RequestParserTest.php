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
require_once __DIR__ . '/BrokeredUserInput.php';
use org\stubbles\console\test\BrokeredUserInput;
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
     * Console
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockOutputStream;
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
        $this->mockOutputStream       = $this->getMock('stubbles\streams\OutputStream');
        $this->mockConsoleRequest     = $this->getMock('stubbles\input\console\ConsoleRequest');
        $this->mockRequestBroker      = $this->getMock('stubbles\input\broker\RequestBroker');
        $this->mockParamErrorMessages = $this->getMock('stubbles\input\errors\messages\ParamErrorMessages');
        $this->requestParser          = new RequestParser(
                $this->mockOutputStream,
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
        $constructor = lang\reflectConstructor($this->requestParser);
        $this->assertTrue($constructor->hasAnnotation('Inject'));

        $parameters = $constructor->getParameters();
        $this->assertTrue($parameters[0]->hasAnnotation('Named'));
        $this->assertEquals('stdout',
                            $parameters[0]->getAnnotation('Named')->getName()
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
        $this->markTestIncomplete();
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
            var_dump($memoryOutputStream->buffer());
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
     * @expectedException         stubbles\console\ConsoleAppException
     * @expectedExceptionCode     10
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
}
