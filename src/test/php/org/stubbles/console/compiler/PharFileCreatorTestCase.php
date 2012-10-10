<?php
/**
 * Your license or something other here.
 *
 * @package  org\stubbles\console\compiler
 */
namespace org\stubbles\console\compiler;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Finder\Finder;
/**
 * Test for org\stubbles\console\compiler\PharFileCreator.
 */
class PharFileCreatorTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  PharFileCreator
     */
    private $pharFileCreator;
    /**
     * root path
     *
     * @type  org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;
    /**
     * mocked phar to create
     *
     * @type  \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPhar;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->root = vfsStream::setup();
        $this->mockPhar = $this->getMockBuilder('\Phar')
                               ->disableOriginalConstructor()
                               ->getMock();

        $this->pharFileCreator = new PharFileCreator($this->mockPhar, vfsStream::url('root/test.phar'), vfsStream::url('root'));
        $this->removePhar();
    }

    /**
     * clean up test environment
     */
    public function tearDown()
    {
        $this->removePhar();
    }

    /**
     * remove phar if it exists
     */
    private function removePhar()
    {
        if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'test.phar')) {
            unlink(__DIR__ . DIRECTORY_SEPARATOR . 'test.phar');
        }
    }

    /**
     * @test
     */
    public function canAddContentToPhar()
    {
        $this->mockPhar->expects($this->once())
                       ->method('addFromString')
                       ->with($this->equalTo('foo.txt'),
                              $this->equalTo('some foo content')
                         );
        $this->pharFileCreator->addContent('foo.txt', 'some foo content');
    }

    /**
     * @test
     */
    public function canAddPhpFileToPhar()
    {
        $this->mockPhar->expects($this->once())
                       ->method('addFromString')
                       ->with($this->equalTo('src/foo.php'),
                              $this->equalTo('<?php echo $stuff; ?>')
                         );
        vfsStream::newFile('foo.php')
                 ->withContent('<?php echo $stuff; /* some coment */?>')
                 ->at(vfsStream::newDirectory('src')
                               ->at($this->root)
                   );
        $this->pharFileCreator->addFile(vfsStream::url('root/src/foo.php'));
    }

    /**
     * @test
     */
    public function canAddOtherFileToPhar()
    {
        $this->mockPhar->expects($this->once())
                       ->method('addFromString')
                       ->with($this->equalTo('src/foo.txt'),
                              $this->equalTo('some content here')
                         );
        vfsStream::newFile('foo.txt')
                 ->withContent('some content here')
                 ->at(vfsStream::newDirectory('src')
                               ->at($this->root)
                   );
        $this->pharFileCreator->addFile(vfsStream::url('root/src/foo.txt'));
    }

    /**
     * @test
     */
    public function canAddFilesFromFinderToPhar()
    {
        $this->pharFileCreator = new PharFileCreator($this->mockPhar, vfsStream::url('root/test.phar'), __DIR__);
        $this->mockPhar->expects($this->at(0))
                       ->method('addFromString')
                       ->with($this->equalTo('CompilerTestCase.php'));
        $this->mockPhar->expects($this->at(1))
                       ->method('addFromString')
                       ->with($this->equalTo('ConsoleCompilerAppTestCase.php'));
        $this->mockPhar->expects($this->at(2))
                       ->method('addFromString')
                       ->with($this->equalTo('PharFileCreatorTestCase.php'));
        $this->pharFileCreator->addFiles(Finder::create()
                                               ->files()
                                               ->ignoreVCS(true)
                                               ->in(__DIR__)
        );
    }

    /**
     * @test
     */
    public function canAddLicenseToPhar()
    {
        $this->mockPhar->expects($this->once())
                       ->method('addFromString')
                       ->with($this->equalTo('LICENSE'),
                              $this->equalTo("\nsome foo content\n")
                         );
        $this->pharFileCreator->addLicense('some foo content');
    }

    /**
     * @test
     */
    public function canSetStubOnPhar()
    {
        $this->mockPhar->expects($this->once())
                       ->method('setStub')
                       ->with($this->equalTo('some foo content'));
        $this->pharFileCreator->setStub('some foo content');
    }

    /**
     * @test
     */
    public function saveDoesNotCreatePharWhenNothingAdded()
    {
        PharFileCreator::create(__DIR__ . DIRECTORY_SEPARATOR . 'test.phar', 'test.phar', __DIR__)
                       ->save();
        $this->assertFileNotExists(__DIR__ . DIRECTORY_SEPARATOR . 'test.phar');
    }

    /**
     * @test
     */
    public function saveDoesCreatePharWhenSomethingAdded()
    {
        PharFileCreator::create(__DIR__ . DIRECTORY_SEPARATOR . 'test.phar', 'test.phar', __DIR__)
                       ->addContent('foo.txt', 'some content')
                       ->save();
        $this->assertFileExists(__DIR__ . DIRECTORY_SEPARATOR . 'test.phar');
        $this->assertTrue(is_executable(__DIR__ . DIRECTORY_SEPARATOR . 'test.phar'));
    }
}
?>