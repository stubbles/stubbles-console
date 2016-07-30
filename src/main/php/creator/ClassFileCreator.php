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
/**
 * Creates a class file for given console class name.
 */
class ClassFileCreator extends FileCreator
{
    /**
     * creates file
     *
     * @param  string  $className
     */
    public function create(string $className)
    {
        if (!class_exists($className)) {
            $classFileName = $this->fileNameforClass($className);
            $this->createFile($classFileName, $className, 'class.tmpl');
            $this->console->writeLine(
                    'Class ' . $className . ' created at ' . $classFileName
            );
        } else {
            $this->console->writeLine(
                    'Class ' . $className
                    . ' already exists, skipped creating the class'
            );
        }
    }
}
