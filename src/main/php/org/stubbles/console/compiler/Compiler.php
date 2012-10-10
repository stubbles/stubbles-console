<?php
/**
 * This file is part of stubbles.
 *
 * @package  net\stubbles\console
 *
 * This file contains code from Composer, http://getcomposer.org/
 * Copyright (c) 2011 Nils Adermann, Jordi Boggiano
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace org\stubbles\console\compiler;
use net\stubbles\lang\ResourceLoader;
use Symfony\Component\Finder\Finder;
/**
 * The Compiler class compiles a command line app into a phar.
 */
class Compiler
{
    /**
     * root directory
     *
     * @type  string
     */
    private $dir;

    /**
     * constructor
     *
     * @param  ResourceLoader  $resourceLoader
     * @Inject
     */
    public function __construct(ResourceLoader $resourceLoader)
    {
        $this->dir = ((\Phar::running() !== '') ? (getcwd()) : ($resourceLoader->getRoot()));
    }

    /**
     * compiles given script into a single phar file
     *
     * @param   string  $bin  script to create phar file for
     * @return  PharFileCreator
     */
    public function compile($bin)
    {
        $phar = $this->createPhar($bin);
        $phar->addFiles(Finder::create()
                              ->files()
                              ->ignoreVCS(true)
                              ->in($this->dir . '/src/main')
        );
        $phar->addFiles(Finder::create()
                              ->files()
                              ->ignoreVCS(true)
                              ->exclude('Tests')
                              ->exclude('vendor/bin')
                              ->exclude('composer')
                              ->exclude('examples')
                              ->exclude('nbproject')
                              ->exclude('src/test')
                              ->notName('CHANGES')
                              ->notName('composer.*')
                              ->notName('phpdoc.dist.xml')
                              ->notName('phpunit.xml.dist')
                              ->notName('readme.*')
                              ->notName('autoload.php')
                              ->in($this->dir . '/vendor')
        );
        $phar->addFile($this->dir . '/vendor/autoload.php');
        $phar->addFile($this->dir . '/vendor/composer/autoload_namespaces.php');
        $phar->addFile($this->dir . '/vendor/composer/autoload_classmap.php');
        $phar->addFile($this->dir . '/vendor/composer/autoload_real.php');
        $phar->addFile($this->dir . '/vendor/composer/ClassLoader.php');
        $phar->addContent('bin/' . $bin, $this->getScriptContents($bin));
        $phar->addLicense(file_get_contents($this->dir . '/LICENSE'));
        $phar->setStub($this->createStub($bin));
        return $phar;
    }

    /**
     * creates a phar creator
     *
     * @param   string  $bin  script to create phar file for
     * @return  PharFileCreator
     */
    private function createPhar($bin)
    {
        $pharFileName = $this->dir . '/target/' . $bin . '.phar';
        if (file_exists($pharFileName)) {
            unlink($pharFileName);
        }

        return $this->doCreate($pharFileName, $bin);
    }

    /**
     * helper method to be able to mock out phar file creation
     *
     * @param   string  $pharFileName  complete file name of phar file to create
     * @param   string  $bin           script to create phar file for
     * @return  PharFileCreator
     */
    protected function doCreate($pharFileName, $bin)
    {
        return PharFileCreator::create($pharFileName, $bin . '.phar', $this->dir);
    }

    /**
     * retrieves contents of actual script
     *
     * @param   string  $bin
     * @return  string
     */
    private function getScriptContents($bin)
    {
        return preg_replace('{^#!/usr/bin/php\s*}',
                            '',
                            preg_replace('{^#!/usr/bin/env php\s*}',
                                         '',
                                         file_get_contents($this->dir . '/bin/' . $bin)
                            )
        );
    }

    /**
     * creates stub for phar file
     *
     * @param   string  $bin
     * @return  string
     */
    private function createStub($bin)
    {
        $stub = <<<EOF
#!/usr/bin/env php
<?php
/*
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
Phar::mapPhar('$bin.phar');
require 'phar://$bin.phar/bin/$bin';
__HALT_COMPILER();
EOF;
        return $stub;
    }
}
?>