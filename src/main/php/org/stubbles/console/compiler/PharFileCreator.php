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
use Symfony\Component\Finder\Finder;
/**
 * Facade to create a phar file.
 */
class PharFileCreator
{
    /**
     * actual phar
     *
     * @type  \Phar
     */
    private $phar;
    /**
     * path to phar file
     *
     * @type  string
     */
    private $path;
    /**
     * base directory to strip from path of files to add
     *
     * @type  string
     */
    private $baseDir;

    /**
     * constructor
     *
     * @param  Phar    $phar     phar file to add content to
     * @param  string  $path     complete path of phar file
     * @param  string  $baseDir  base directory to strip from path of files to add
     */
    public function __construct(\Phar $phar, $path, $baseDir)
    {
        $this->phar = $phar;
        $this->phar->setSignatureAlgorithm(\Phar::SHA1);
        $this->phar->startBuffering();
        $this->baseDir = $baseDir;
        $this->path    = $path;
    }

    /**
     * static constructor
     *
     * @param   string  $path
     * @param   string  $alias
     * @param   string  $baseDir
     * @return  PharFileCreator
     */
    public static function create($path, $alias, $baseDir)
    {
        return new self(new \Phar($path, 0, $alias), $path, $baseDir);
    }

    /**
     * add given content as file
     *
     * @param   string  $file
     * @param   string  $content
     * @return  PharFileCreator
     */
    public function addContent($file, $content)
    {
        $this->phar->addFromString($file, $content);
        return $this;
    }

    /**
     * add file to phar
     *
     * @param   string  $filename
     * @return  PharFileCreator
     */
    public function addFile($filename)
    {
        $path     = str_replace($this->baseDir . DIRECTORY_SEPARATOR, '', $filename);
        $contents = file_get_contents($filename);
        if (substr($path, -4) === '.php') {
            $contents = $this->stripWhitespace($contents);
        }

        $this->phar->addFromString($path, $contents);
        return $this;
    }

    /**
     * add a list of files provided by finder
     *
     * @param   Finder  $finder
     * @return  PharFileCreator
     */
    public function addFiles(Finder $finder)
    {
        foreach ($finder as $file) {
            $this->addFile($file->getRealPath());
        }

        return $this;
    }

    /**
     * add a license to phar
     *
     * @param   string  $licence
     * @return  PharFileCreator
     */
    public function addLicense($licence)
    {
        $this->addContent('LICENSE', "\n" . $licence . "\n");
        return $this;
    }

    /**
     * add a stub for the phar
     *
     * @param   string  $stub
     * @return  PharFileCreator
     */
    public function setStub($stub)
    {
        $this->phar->setStub($stub);
        return $this;
    }

    /**
     * flush contents of phar file to disc and make it executable
     */
    public function save()
    {
        $this->phar->stopBuffering();
        if (file_exists($this->path)) {
            chmod($this->path, 0755);
        }
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param   string  $source A PHP string
     * @return  string  The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                $output .= $this->trimTrailingSpaces($this->normalizeNewLines($this->reduceSpaces($token[1])));
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    /**
     * reduces 2 or more spaces in given content to one space
     *
     * @param   string  $content
     * @return  string
     */
    private function reduceSpaces($content)
    {
        return preg_replace('{[ \t]+}', ' ', $content);
    }

    /**
     * converts all new lines to \n
     *
     * @param   string  $content
     * @return  string
     */
    private function normalizeNewLines($content)
    {
        return preg_replace('{(?:\r\n|\r|\n)}', "\n", $content);
    }

    /**
     * trims trailing spaces from content
     *
     * @param   string  $content
     * @return  string
     */
    private function trimTrailingSpaces($content)
    {
        return preg_replace('{\n +}', "\n", $content);
    }
}
?>