<?php
/**
 * Your license or something other here.
 *
 * @package  org\stubbles\console\compiler
 */
namespace org\stubbles\console\compiler;
use org\bovigo\vfs\vfsStream;
/**
 * Test for org\stubbles\console\compiler\Compiler.
 */
class CompilerTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  Compiler
     */
    private $compiler;
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
    private $mockPharFileCreator;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->root = vfsStream::setup('root',
                                       null,
                                       array('target'  => array(),
                                             'src'     => array('main' => array()),
                                             'vendor'  => array('autoload.php' => 'autoloader',
                                                                'composer'     => array()
                                                          ),
                                             'bin'     => array('test' => 'some script'),
                                             'LICENSE' => 'some license stuff'
                                       )
                      );
        $mockResourceLoader = $this->getMock('net\stubbles\lang\ResourceLoader');
        $mockResourceLoader->expects($this->once())
                           ->method('getRoot')
                           ->will($this->returnValue(vfsStream::url('root')));
        $this->compiler = $this->getMock('org\stubbles\console\compiler\Compiler',
                                         array('doCreate'),
                                         array($mockResourceLoader)
                          );
        $this->mockPharFileCreator = $this->getMockBuilder('org\stubbles\console\compiler\PharFileCreator')
                                          ->disableOriginalConstructor()
                                          ->getMock();
        $this->compiler->expects($this->once())
                       ->method('doCreate')
                       ->will($this->returnValue($this->mockPharFileCreator));
    }

    /**
     * @test
     */
    public function removesAlreadyExistingPhar()
    {
        vfsStream::newFile('test.phar')->at($this->root->getChild('target'));
        $this->compiler->compile('test');
        $this->assertFileNotExists(vfsStream::url('root/target/test.phar'));
    }

    /**
     * @test
     */
    public function removesShebangEnvLineFromScript()
    {
        file_put_contents(vfsStream::url('root/bin/test'), "#!/usr/bin/env php
<?php
some code here
?>");
        $this->mockPharFileCreator->expects($this->once())
                                  ->method('addContent')
                                  ->with($this->equalTo('bin/test'), $this->equalTo("<?php
some code here
?>"));
        $this->compiler->compile('test');
    }

    /**
     * @test
     */
    public function removesShebangLineFromScript()
    {
        file_put_contents(vfsStream::url('root/bin/test'), "#!/usr/bin/php
<?php
some code here
?>");
        $this->mockPharFileCreator->expects($this->once())
                                  ->method('addContent')
                                  ->with($this->equalTo('bin/test'), $this->equalTo("<?php
some code here
?>"));
        $this->compiler->compile('test');
    }

    /**
     * @test
     */
    public function addsLicenseFromFile()
    {
        $this->mockPharFileCreator->expects($this->once())
                                  ->method('addLicense')
                                  ->with($this->equalTo('some license stuff'));
        $this->compiler->compile('test');
    }

    /**
     * @test
     */
    public function createsStubAccordingToScript()
    {
        $this->mockPharFileCreator->expects($this->once())
                                  ->method('setStub')
                                  ->with($this->equalTo("#!/usr/bin/env php
<?php
/*
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
Phar::mapPhar('test.phar');
require 'phar://test.phar/bin/test';
__HALT_COMPILER();"));
        $this->compiler->compile('test');
    }
}
?>