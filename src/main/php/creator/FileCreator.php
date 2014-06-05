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
use stubbles\console\Console;
use stubbles\lang\Rootpath;
/**
 * Base class for file creation.
 */
abstract class FileCreator
{
    /**
     * stresm to read data from
     *
     * @type  Console
     */
    protected $console;
    /**
     * path to project
     *
     * @type  Rootpath
     */
    protected $rootpath;

    /**
     * constructor
     *
     * @param  Console    $console
     * @param  Rootpath   $rootpath
     * @Inject
     */
    public function __construct(Console $console, Rootpath $rootpath)
    {
        $this->console  = $console;
        $this->rootpath = $rootpath;
    }

    /**
     * creates file
     *
     * @param  string  $className
     */
    public abstract function create($className);

    /**
     * returns name of class file to create
     *
     * @param   string  $className
     * @param   string  $type
     * @return  string
     */
    protected function getClassFileName($className, $type = 'main')
    {
        return $this->rootpath->to(
                'src',
                $type,
                'php',
                str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php'
        );
    }

    /**
     * creates file of given type for given class
     *
     * @param  string  $fileName
     * @param  string  $className
     * @param  string  $template
     */
    protected function createFile($fileName, $className, $template)
    {
        $directory = dirname($fileName);
        if (!file_exists($directory . '/.')) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($fileName,
                          str_replace(['{NAMESPACE}',
                                       '{CLASS}'
                                      ],
                                      [$this->getNamespace($className),
                                       $this->getNonQualifiedClassName($className)
                                      ],
                                      file_get_contents(__DIR__ . '/' . $template)
                          )
        );
    }

    /**
     * returns namespace part of class name
     *
     * @param   string  $className
     * @return  string
     */
    private function getNamespace($className)
    {
        return substr($className, 0, strrpos($className, '\\'));
    }

    /**
     * returns non qualified part of class name
     *
     * @param   string  $className
     * @return  string
     */
    private function getNonQualifiedClassName($className)
    {
        return substr($className, strrpos($className, '\\') + 1);
    }
}
