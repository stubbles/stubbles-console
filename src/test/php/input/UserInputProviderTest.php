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
use stubbles\lang\reflect;
use org\stubbles\console\test\BrokeredUserInput;
/**
 * Test for stubbles\console\input\UserInputProvider.
 *
 * @since  2.0.0
 * @group  input
 */
class UserInputProviderTest extends \PHPUnit_Framework_TestCase
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
        $this->mockRequestParser = $this->getMockBuilder('stubbles\console\input\RequestParser')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->mockInjector      = $this->getMockBuilder('stubbles\ioc\Injector')
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->userInputProvider = new UserInputProvider(
                $this->mockRequestParser,
                $this->mockInjector,
                'org\stubbles\console\test\BrokeredUserInput'
        );
    }

    /**
     * @test
     */
    public function annotationsPresentOnConstructor()
    {
        $this->assertTrue(
                reflect\constructorAnnotationsOf($this->userInputProvider)
                        ->contain('Inject')
        );

        $annotations = reflect\annotationsOfConstructorParameter(
                'userInputClass',
                $this->userInputProvider
        );
        $this->assertTrue($annotations->contain('Named'));
        $this->assertEquals(
                'stubbles.console.input.class',
                $annotations->firstNamed('Named')->getName()
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
                                  $this->equalTo('stubbles.console.input.instance')
                             )
                           ->will($this->returnValue($brokeredUserInput));
        $this->mockRequestParser->expects($this->once())
                                ->method('parseInto')
                                ->with($this->equalTo($brokeredUserInput),
                                       $this->equalTo('main')
                                  )
                                ->will($this->returnValue($brokeredUserInput));
        $this->assertSame(
                $brokeredUserInput,
                $this->userInputProvider->get('main')
        );
    }
}
