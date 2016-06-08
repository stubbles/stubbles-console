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
use org\stubbles\console\test\BrokeredUserInput;

use function bovigo\assert\assert;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isSameAs;
use function stubbles\reflect\annotationsOfConstructorParameter;
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
        $annotations = annotationsOfConstructorParameter(
                'userInputClass',
                $this->userInputProvider
        );
        assertTrue($annotations->contain('Named'));
        assert(
                $annotations->firstNamed('Named')->getName(),
                equals('stubbles.console.input.class')
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
        assert(
                $this->userInputProvider->get('main'),
                isSameAs($brokeredUserInput)
        );
    }
}
