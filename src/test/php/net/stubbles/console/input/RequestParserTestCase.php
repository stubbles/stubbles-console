<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
namespace net\stubbles\console\input;
use net\stubbles\console\ConsoleAppException;
use net\stubbles\lang\exception\Exception;
use net\stubbles\lang\reflect\annotation\Annotation;
use org\stubbles\console\test\BrokeredUserInput;
/**
 * Test for net\stubbles\console\input\RequestParser.
 *
 * @since  2.0.0
 * @group  input
 */
class RequestParserTestCase extends \PHPUnit_Framework_TestCase
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
     * request instance
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;
    /**
     * request broker
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequestBroker;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->mockOutputStream  = $this->getMock('net\\stubbles\\streams\\OutputStream');
        $this->mockRequest       = $this->getMock('net\\stubbles\\input\\Request');
        $this->mockRequestBroker = $this->getMockBuilder('net\\stubbles\\input\\broker\\RequestBrokerFacade')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->requestParser     = new RequestParser($this->mockOutputStream,
                                                     $this->mockRequest,
                                                     $this->mockRequestBroker
                                   );
    }

    /**
     * @test
     */
    public function annotationsPresentOnConstructor()
    {
        $constructor = $this->requestParser->getClass()->getConstructor();
        $this->assertTrue($constructor->hasAnnotation('Inject'));

        $parameters = $constructor->getParameters();
        $this->assertTrue($parameters[0]->hasAnnotation('Named'));
        $this->assertEquals('stdout',
                            $parameters[0]->getAnnotation('Named')->getName()
        );
    }

    /**
     * @test
     * @expectedException     net\stubbles\console\ConsoleAppException
     * @expectedExceptionCode 0
     */
    public function throwsConsoleAppExceptionWhenHelpIsRequestedWithDashH()
    {
        $this->mockRequest->expects($this->once())
                          ->method('hasParam')
                          ->will($this->returnValue(true));
        $this->mockRequestBroker->expects($this->never())
                                ->method('procure');
        $this->mockRequestBroker->expects($this->once())
                                    ->method('getAnnotations')
                                    ->will($this->returnValue(array()));
        $this->requestParser->parseTo('org\\stubbles\\console\\test\\BrokeredUserInput');
    }

    /**
     * @test
     * @expectedException     net\stubbles\console\ConsoleAppException
     * @expectedExceptionCode 0
     */
    public function throwsConsoleAppExceptionWhenHelpIsRequestedWithDashDashHelp()
    {
        $this->mockRequest->expects($this->exactly(2))
                          ->method('hasParam')
                          ->will($this->onConsecutiveCalls(false, true));
        $this->mockRequestBroker->expects($this->never())
                                ->method('procure');
        $this->mockRequestBroker->expects($this->once())
                                    ->method('getAnnotations')
                                    ->will($this->returnValue(array()));
        $this->requestParser->parseTo('org\\stubbles\\console\\test\\BrokeredUserInput');
    }

    /**
     * @test
     */
    public function helpClosureRendersHelpToOutputStream()
    {
        $this->mockRequest->expects($this->once())
                          ->method('hasParam')
                          ->will($this->returnValue(true));
        $this->mockRequestBroker->expects($this->once())
                                ->method('getAnnotations')
                                ->will($this->returnValue(array($this->createRequestAnnotation('foo', 'Set the foo option.', '-f FOO'),
                                                                $this->createRequestAnnotation('bar', 'Set the bar option.'),
                                                                $this->createRequestAnnotation('o', 'Set another option.')
                                                          )
                                       )
                                  );
        try {
            $this->requestParser->parseTo('org\\stubbles\\console\\test\\BrokeredUserInput');
            $this->fail('Excpected net\\stubbles\\console\\ConsoleAppException');
        } catch (ConsoleAppException $cae) {
            $this->mockOutputStream->expects($this->at(0))
                                   ->method('writeLine')
                                   ->with($this->equalTo('Options:'));
            $this->mockOutputStream->expects($this->at(1))
                                   ->method('writeLine')
                                   ->with($this->equalTo('   -f FOO   Set the foo option.'));
            $this->mockOutputStream->expects($this->at(2))
                                   ->method('writeLine')
                                   ->with($this->equalTo('   --bar    Set the bar option.'));
            $this->mockOutputStream->expects($this->at(3))
                                   ->method('writeLine')
                                   ->with($this->equalTo('   -o       Set another option.'));
            $this->mockOutputStream->expects($this->at(4))
                                   ->method('writeLine')
                                   ->with($this->equalTo('   -h       Prints this help.'));
            $messenger = $cae->getMessenger();
            $messenger();
        }
    }

    /**
     * creates request annotation
     *
     * @param   string  $name
     * @param   string  $description
     * @param   string  $option
     * @return  Annotation
     */
    private function createRequestAnnotation($name, $description, $option = null)
    {
        $annotation = new Annotation('Test');
        $annotation->name        = $name;
        $annotation->description = $description;
        if (null !== $option) {
            $annotation->option = $option;
        }

        return $annotation;
    }

    /**
     * @test
     */
    public function successfulParseReturnsInstance()
    {
        $this->mockRequest->expects($this->any())
                          ->method('hasParam')
                          ->will($this->returnValue(false));
        $this->mockRequestBroker->expects($this->once())
                                ->method('procure');
        $this->assertInstanceOf('org\\stubbles\\console\\test\\BrokeredUserInput',
                                $this->requestParser->parseTo('org\\stubbles\\console\\test\\BrokeredUserInput')
        );
    }

    /**
     * @test
     * @expectedException         net\stubbles\console\ConsoleAppException
     * @expectedExceptionCode     10
     */
    public function failureWhileParsingThrowsConsoleAppException()
    {
        $this->mockRequest->expects($this->any())
                          ->method('hasParam')
                          ->will($this->returnValue(false));
        $this->mockRequestBroker->expects($this->once())
                                ->method('procure')
                                ->will($this->returnCallback(function(BrokeredUserInput $userInput, $group, \Closure $onError)
                                                             {
                                                                 $onError('bar', 'Error, dude!');
                                                             }
                                       )
                                  );
        $this->requestParser->parseTo('org\\stubbles\\console\\test\\BrokeredUserInput');
    }
}
?>