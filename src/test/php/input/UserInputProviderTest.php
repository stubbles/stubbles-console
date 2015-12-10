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
use stubbles\ioc\Injector;
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
    private $requestParser;
    /**
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $injector;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->requestParser = NewInstance::stub(RequestParser::class);
        $this->injector      = NewInstance::stub(Injector::class);
        $this->userInputProvider = new UserInputProvider(
                $this->requestParser,
                $this->injector,
                BrokeredUserInput::class
        );
    }

    /**
     * @test
     */
    public function annotationsPresentOnConstructor()
    {
        $annotations = reflect\annotationsOfConstructorParameter(
                'userInputClass',
                $this->userInputProvider
        );
        assertTrue($annotations->contain('Named'));
        assertEquals(
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
        $this->injector->mapCalls(['getInstance' => $brokeredUserInput]);
        $this->requestParser->mapCalls(['parseInto' => $brokeredUserInput]);
        assertSame(
                $brokeredUserInput,
                $this->userInputProvider->get('main')
        );
    }
}
