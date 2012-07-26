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
use org\stubbles\console\test\BrokeredUserInput;
/**
 * Test for net\stubbles\console\input\UserInputProvider.
 *
 * @since  2.0.0
 * @group  input
 */
class UserInputProviderTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  UserInputProvider
     */
    private $userInputProvider;
    /**
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequestParser;
    /**
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockInjector;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->mockRequestParser = $this->getMockBuilder('net\stubbles\console\input\RequestParser')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->mockInjector      = $this->getMockBuilder('net\stubbles\ioc\Injector')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->userInputProvider = new UserInputProvider($this->mockRequestParser,
                                                         $this->mockInjector,
                                                         'org\stubbles\console\test\BrokeredUserInput'
                                   );
    }

    /**
     * @test
     */
    public function annotationsPresentOnConstructor()
    {
        $constructor = $this->userInputProvider->getClass()->getConstructor();
        $this->assertTrue($constructor->hasAnnotation('Inject'));

        $parameters = $constructor->getParameters();
        $this->assertTrue($parameters[2]->hasAnnotation('Named'));
        $this->assertEquals('net.stubbles.console.input.class',
                            $parameters[2]->getAnnotation('Named')->getName()
        );
    }

    /**
     * @test
     */
    public function createsUserInputInstance()
    {
        $brokeredUserInput = new BrokeredUserInput();
        $this->mockInjector->expects($this->once())
                           ->method('getInstance')
                           ->with($this->equalTo('org\stubbles\console\test\BrokeredUserInput'),
                                  $this->equalTo('net.stubbles.console.input.instance')
                             )
                           ->will($this->returnValue($brokeredUserInput));
        $this->mockRequestParser->expects($this->once())
                                ->method('parseInto')
                                ->with($this->equalTo($brokeredUserInput),
                                       $this->equalTo('main')
                                  )
                                ->will($this->returnValue($brokeredUserInput));
        $this->assertSame($brokeredUserInput,
                          $this->userInputProvider->get('main')
        );
    }
}
?>