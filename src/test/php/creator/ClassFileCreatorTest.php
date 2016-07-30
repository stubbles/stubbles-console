<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console\creator;
use bovigo\callmap\NewInstance;
use org\bovigo\vfs\vfsStream;
use stubbles\console\Console;
use stubbles\values\ResourceLoader;
use stubbles\values\Rootpath;

use function bovigo\assert\{
    assert,
    assertFalse,
    assertTrue,
    expect,
    predicate\equals
};
use function bovigo\callmap\verify;
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
        $this->console          = NewInstance::stub(Console::class);
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
    }

    /**
     * @test
     */
    public function createsClassFileWithCorrectContent()
    {
        $this->classFileCreator->create('example\console\ExampleConsoleApp');
        assert(
                file_get_contents($this->rootpath->to('src/main/php/example/console/ExampleConsoleApp.php')),
                equals('<?php
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
')
        );
        verify($this->console, 'writeLine')
                ->received('Class example\console\ExampleConsoleApp created at ' . $this->rootpath->to('src/main/php/example/console/ExampleConsoleApp.php'));
    }

    /**
     * @test
     */
    public function skipsClassCreationIfClassAlreadyExists()
    {
        $this->classFileCreator->create(ClassFileCreator::class);
        assertFalse(
                file_exists($this->rootpath->to('src/main/php/stubbles/console/creator/ClassFileCreator.php'))
        );
        verify($this->console, 'writeLine')
                ->received('Class stubbles\console\creator\ClassFileCreator already exists, skipped creating the class');
    }

    /**
     * @test
     * @group  issue_49
     * @since  4.1.0
     */
    public function throwsConfigurationExceptionWhenNoPsr4PathDefinedForNamespace()
    {
        vfsStream::newFile('composer.json')
                 ->withContent('{"autoload": { "psr-4": { "stubbles\\\foo\\\": "src/main/php" } }}')
                 ->at($this->root);
        expect(function() {
                $this->classFileCreator->create('example\console\ExampleConsoleApp');
        })
                ->throws(\UnexpectedValueException::class);
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
    }

    /**
     * @test
     * @since  4.1.0
     * @group  issue_49
     */
    public function createsPsr4ClassFileWithCorrectContent()
    {
        vfsStream::newFile('composer.json')
                 ->withContent('{"autoload": { "psr-4": { "example\\\console\\\": "src/main/php" } }}')
                 ->at($this->root);
        $this->classFileCreator->create('example\console\ExampleConsoleApp');
        assert(
                file_get_contents($this->rootpath->to('src/main/php/ExampleConsoleApp.php')),
                equals('<?php
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
')
        );
    }
}
