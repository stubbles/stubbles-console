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
use stubbles\lang\ResourceLoader;
use stubbles\lang\Rootpath;
/**
 * Test for stubbles\console\creator\ClassFileCreator.
 *
 * @group  scripts
 */
class ClassFileCreatorTest extends \PHPUnit_Framework_TestCase
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
     * @type  Rootpath
     */
    private $rootpath;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->rootpath         = new Rootpath(vfsStream::setup()->url());
        $this->mockConsole      = $this->getMockBuilder('stubbles\console\Console')
                                       ->disableOriginalConstructor()
                                       ->getMock();
        $this->classFileCreator = new ClassFileCreator($this->mockConsole, $this->rootpath, new ResourceLoader());
    }

    /**
     * @test
     */
    public function createsClassIfDoesNotExist()
    {
        $this->mockConsole->expects($this->once())
                          ->method('writeLine')
                          ->with($this->equalTo('Class example\console\ExampleConsoleApp created at ' . $this->rootpath->to('src/main/php/example/console/ExampleConsoleApp.php')));
        $this->classFileCreator->create('example\console\ExampleConsoleApp');
        $this->assertTrue(file_exists($this->rootpath->to('src/main/php/example/console/ExampleConsoleApp.php')));
        $this->assertEquals(
                '<?php
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
     * @return  \stubbles\ioc\module\BindingModule[]
     */
    public static function __bindings()
    {
        return [self::argumentParser()];
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
                file_get_contents($this->rootpath->to('src/main/php/example/console/ExampleConsoleApp.php'))
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
        $this->assertFalse(file_exists($this->rootpath->to('src/main/php/stubbles/console/creator/ClassFileCreator.php')));
    }
}
