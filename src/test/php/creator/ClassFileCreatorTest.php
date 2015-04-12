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
use bovigo\callmap;
use bovigo\callmap\NewInstance;
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
    private $console;
    /**
     * root directory
     *
     * @type  Rootpath
     */
    private $rootpath;
    /**
     * @type  \org\bovigo\vfs\vfsDirectory
     */
    private $root;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->root             = vfsStream::setup();
        $this->rootpath         = new Rootpath($this->root->url());
        $this->console          = NewInstance::stub('stubbles\console\Console');
        $this->classFileCreator = new ClassFileCreator(
                $this->console,
                $this->rootpath,
                new ResourceLoader()
        );
    }

    /**
     * @test
     */
    public function createsClassIfDoesNotExist()
    {
        $this->classFileCreator->create('example\console\ExampleConsoleApp');
        assertTrue(
                file_exists($this->rootpath->to('src/main/php/example/console/ExampleConsoleApp.php'))
        );
        assertEquals(
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
        callmap\verify($this->console, 'writeLine')
                ->received('Class example\console\ExampleConsoleApp created at ' . $this->rootpath->to('src/main/php/example/console/ExampleConsoleApp.php'));
    }

    /**
     * @test
     */
    public function skipsClassCreationIfClassAlreadyExists()
    {
        $this->classFileCreator->create('stubbles\console\creator\ClassFileCreator');
        assertFalse(
                file_exists($this->rootpath->to('src/main/php/stubbles/console/creator/ClassFileCreator.php'))
        );
        callmap\verify($this->console, 'writeLine')
                ->received('Class stubbles\console\creator\ClassFileCreator already exists, skipped creating the class');
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\ConfigurationException
     * @since  4.1.0
     * @group  issue_49
     */
    public function throwsConfigurationExceptionWhenNoPsr4PathDefinedForNamespace()
    {
        vfsStream::newFile('composer.json')
                 ->withContent('{"autoload": { "psr-4": { "stubbles\\\foo\\\": "src/main/php" } }}')
                 ->at($this->root);;
        $this->classFileCreator->create('example\console\ExampleConsoleApp');
    }

    /**
     * @test
     * @since  4.1.0
     * @group  issue_49
     */
    public function createsClassInPsr4PathIfDoesNotExist()
    {
        vfsStream::newFile('composer.json')
                 ->withContent('{"autoload": { "psr-4": { "example\\\console\\\": "src/main/php" } }}')
                 ->at($this->root);
        $this->classFileCreator->create('example\console\ExampleConsoleApp');
        assertTrue(
                file_exists($this->rootpath->to('src/main/php/ExampleConsoleApp.php'))
        );
        assertEquals(
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
                file_get_contents($this->rootpath->to('src/main/php/ExampleConsoleApp.php'))
        );
    }
}
