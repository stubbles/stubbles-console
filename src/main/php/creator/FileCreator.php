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
use stubbles\console\Console;
use stubbles\values\ResourceLoader;
use stubbles\values\Rootpath;
/**
 * Base class for file creation.
 */
abstract class FileCreator
{
    /**
     * stresm to read data from
     *
     * @type  \stubbles\console\Console
     */
    protected $console;
    /**
     * path to project
     *
     * @type  \stubbles\values\Rootpath
     */
    protected $rootpath;
    /**
     * access to resources
     *
     * @type  \stubbles\values\ResourceLoader
     */
    private $resourceLoader;

    /**
     * constructor
     *
     * @param  \stubbles\console\Console        $console
     * @param  \stubbles\values\Rootpath        $rootpath
     * @param  \stubbles\values\ResourceLoader  $resourceLoader
     */
    public function __construct(Console $console, Rootpath $rootpath, ResourceLoader $resourceLoader)
    {
        $this->console        = $console;
        $this->rootpath       = $rootpath;
        $this->resourceLoader = $resourceLoader;
    }

    /**
     * creates file
     *
     * @param  string  $className
     */
    public abstract function create(string $className);

    /**
     * returns name of class file to create
     *
     * @param   string  $className
     * @param   string  $type
     * @return  string
     */
    protected function fileNameforClass(string $className, string $type = 'main'): string
    {
        if (file_exists($this->rootpath->to('composer.json'))) {
            $composer = json_decode(
                    file_get_contents($this->rootpath->to('composer.json')),
                    true
            );
            if (isset($composer['autoload']['psr-4'])) {
                return $this->fileNameForPsr4(
                        $composer['autoload']['psr-4'],
                        $className,
                        $type
                );
            }
        }

        // assume psr-0 with standard stubbles pathes
        return $this->rootpath->to(
                'src',
                $type,
                'php',
                str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php'
        );
    }

    /**
     * retrieve psr-4 compatible file name
     *
     * @param   array   $psr4Pathes  map of psr-4 pathes from composer.json
     * @param   string  $className   name of class to retrieve file name for
     * @param   string  $type        whether it a normal class or a test class
     * @return  string
     * @throws  \UnexpectedValueException
     */
    private function fileNameForPsr4(array $psr4Pathes, string $className, string $type): string
    {
        foreach ($psr4Pathes as $prefix => $path) {
            if (substr($className, 0, strlen($prefix)) === $prefix) {
                return $this->rootpath->to(
                        str_replace('main', $type, $path),
                        str_replace(
                                '\\',
                                DIRECTORY_SEPARATOR,
                                str_replace($prefix, '', $className)
                        ) . '.php'
                );
            }
        }

        throw new \UnexpectedValueException(
                'No PSR-4 path for class ' . $className . ' found in composer.json'
        );
    }

    /**
     * creates file of given type for given class
     *
     * @param  string  $fileName
     * @param  string  $className
     * @param  string  $template
     */
    protected function createFile(string $fileName, string $className, string $template)
    {
        $directory = dirname($fileName);
        if (!file_exists($directory . '/.')) {
            mkdir($directory, 0755, true);
        }

        file_put_contents(
                $fileName,
                str_replace(
                        ['{NAMESPACE}', '{CLASS}'],
                        [$this->namespaceOf($className),
                         $this->nonQualifiedClassNameOf($className)
                        ],
                        $this->resourceLoader->load($this->pathForTemplate($template))
                )
        );
    }

    /**
     * finds absolute path for given template file
     *
     * @param   string $template
     * @return  string
     * @throws  \RuntimeException
     */
    private function pathForTemplate(string $template): string
    {
        $pathes = $this->resourceLoader->availableResourceUris('creator/' . $template);
        if (!isset($pathes[0])) {
            throw new \RuntimeException('Could not load template ' . $template);
        }

        return $pathes[0];
    }

    /**
     * returns namespace part of class name
     *
     * @param   string  $className
     * @return  string
     */
    private function namespaceOf(string $className): string
    {
        return substr($className, 0, strrpos($className, '\\'));
    }

    /**
     * returns non qualified part of class name
     *
     * @param   string  $className
     * @return  string
     */
    private function nonQualifiedClassNameOf(string $className): string
    {
        return substr($className, strrpos($className, '\\') + 1);
    }
}
