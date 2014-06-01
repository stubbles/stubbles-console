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
use stubbles\lang;
use stubbles\lang\reflect\annotation\Annotation;
require_once __DIR__ . '/BrokeredUserInput.php';
use org\stubbles\console\test\BrokeredUserInput;
/**
 * Test for stubbles\console\input\RequestParser.
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
     * set up test environment
     */
    public function setUp()
    {
        $this->mockOutputStream   = $this->getMock('stubbles\streams\OutputStream');
        $this->mockConsoleRequest = $this->getMock('stubbles\input\console\ConsoleRequest');
        $this->mockRequestBroker  = $this->getMockBuilder('stubbles\input\broker\RequestBrokerFacade')
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->requestParser      = new RequestParser($this->mockOutputStream,
                                                      $this->mockConsoleRequest,
                                                      $this->mockRequestBroker
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
        $this->mockRequestBroker->expects($this->once())
                                    ->method('getAnnotations')
                                    ->will($this->returnValue([]));
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
        $this->mockRequestBroker->expects($this->once())
                                    ->method('getAnnotations')
                                    ->will($this->returnValue([]));
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
        $argv1 = $this->createRequestAnnotation('argv.1', 'HttpUri', null, 'HttpUri');
        $argv1->required = true;
        $this->mockRequestBroker->expects($this->once())
                                ->method('getAnnotations')
                                ->will($this->returnValue([$this->createRequestAnnotation('foo', 'Set the foo option.', '-f FOO'),
                                                           $this->createRequestAnnotation('bar', 'Set the bar option.'),
                                                           $this->createRequestAnnotation('o', 'Set another option.'),
                                                           $argv1,
                                                           $this->createRequestAnnotation('argv.2', 'Request method', null, 'String')
                                                          ]
                                       )
                                  );
        try {
            $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput');
            $this->fail('Excpected stubbles\console\ConsoleAppException');
        } catch (ConsoleAppException $cae) {
            $this->mockOutputStream->expects($this->at(0))
                                   ->method('writeLine')
                                   ->with($this->equalTo("Real awesome command line app (c) 2012 Stubbles Development Team"));
            $this->mockOutputStream->expects($this->at(1))
                                   ->method('write')
                                   ->with($this->equalTo('Usage: bin/http [options]'));
            $this->mockOutputStream->expects($this->at(2))
                                   ->method('write')
                                   ->with($this->equalTo(' HttpUri'));
            $this->mockOutputStream->expects($this->at(3))
                                   ->method('write')
                                   ->with($this->equalTo(' [Request method]'));
            $this->mockOutputStream->expects($this->at(4))
                                   ->method('writeLine')
                                   ->with($this->equalTo(''));
            $this->mockOutputStream->expects($this->at(5))
                                   ->method('writeLine')
                                   ->with($this->equalTo('Options:'));
            $this->mockOutputStream->expects($this->at(6))
                                   ->method('writeLine')
                                   ->with($this->equalTo('   -f FOO         Set the foo option.'));
            $this->mockOutputStream->expects($this->at(7))
                                   ->method('writeLine')
                                   ->with($this->equalTo('   --bar          Set the bar option.'));
            $this->mockOutputStream->expects($this->at(8))
                                   ->method('writeLine')
                                   ->with($this->equalTo('   -o             Set another option.'));
            $this->mockOutputStream->expects($this->at(9))
                                   ->method('writeLine')
                                   ->with($this->equalTo('   -h or --help   Prints this help.'));
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
    private function createRequestAnnotation($name, $description, $option = null, $type = 'Test')
    {
        $annotation = new Annotation($type);
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
        $this->mockConsoleRequest->expects($this->any())
                                 ->method('hasParam')
                                 ->will($this->returnValue(false));
        $this->mockRequestBroker->expects($this->once())
                                ->method('procure');
        $this->assertInstanceOf('org\stubbles\console\test\BrokeredUserInput',
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
        $this->mockRequestBroker->expects($this->once())
                                ->method('procure')
                                ->will($this->returnCallback(function(BrokeredUserInput $userInput, $group, \Closure $onError)
                                                             {
                                                                 $onError('bar', 'Error, dude!');
                                                             }
                                       )
                                  );
        $this->requestParser->parseTo('org\stubbles\console\test\BrokeredUserInput');
    }
}
