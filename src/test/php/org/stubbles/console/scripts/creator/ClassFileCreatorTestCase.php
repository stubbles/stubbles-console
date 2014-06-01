<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\creator;
use org\bovigo\vfs\vfsStream;
/**
 * Test for stubbles\console\creator\ClassFileCreator.
 *
 * @group  scripts
 */
class ClassFileCreatorTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  ClassFileCreator
     */
    private $classFileCreator;
    /**
     * mocked console
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockConsole;
    /**
     * root directory
     *
     * @type  \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->root             = vfsStream::setup();
        $this->mockConsole      = $this->getMockBuilder('stubbles\console\Console')
                                       ->disableOriginalConstructor()
                                       ->getMock();
        $this->classFileCreator = new ClassFileCreator($this->mockConsole, vfsStream::url('root'));
    }

    /**
     * @test
     */
    public function createsClassIfDoesNotExist()
    {
        $this->mockConsole->expects($this->once())
                          ->method('writeLine')
                          ->with($this->equalTo('Class example\console\ExampleConsoleApp created at ' . vfsStream::url('root/src/main/php/example/console/ExampleConsoleApp.php')));
        $this->classFileCreator->create('example\console\ExampleConsoleApp');
        $this->assertTrue($this->root->hasChild('src/main/php/example/console/ExampleConsoleApp.php'));
        $this->assertEquals('<?php
/**
 * Your license or something other here.
 *
 * @package  example\console
 */
namespace example\console;
use stubbles\console\ConsoleApp;
/**
 * Your own console app.
 *
 * @AppDescription(\'Description of what the app does\')
 */
class ExampleConsoleApp extends ConsoleApp
{
    /**
     * returns list of bindings used for this application
     *
     * @param   string  $projectPath
     * @return  \stubbles\ioc\module\BindingModule[]
     */
    public static function __bindings($projectPath)
    {
        return [self::createModeBindingModule($projectPath),
                self::createArgumentsBindingModule(),
                self::createConsoleBindingModule()
        ];
    }

    /**
     * constructor
     *
     * @Inject
     */
    public function __construct()
    {
        // TODO add your constructor parameters and code
    }

    /**
     * runs the command and returns an exit code
     *
     * @return  int
     */
    public function run()
    {
        // TODO add your application code
        return 0;
    }
}
',
                            $this->root->getChild('src/main/php/example/console/ExampleConsoleApp.php')
                                       ->getContent()
        );
    }

    /**
     * @test
     */
    public function skipsClassCreationIfClassAlreadyExists()
    {
        $this->mockConsole->expects($this->once())
                          ->method('writeLine')
                          ->with($this->equalTo('Class stubbles\console\creator\ClassFileCreator already exists, skipped creating the class'));
        $this->classFileCreator->create('stubbles\console\creator\ClassFileCreator');
        $this->assertFalse($this->root->hasChild('src/main/php/stubbles/console/creator/ClassFileCreator.php'));
    }
}
