<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
namespace org\stubbles\console\scripts\creator;
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
    public function create($className)
    {
        if (!class_exists($className)) {
            $classFileName = $this->getClassFileName($className);
            $this->createFile($classFileName, $className, 'class.tmpl');
            $this->console->writeLine('Class ' . $className . ' created at ' . $classFileName);
        } else {
            $this->console->writeLine('Class ' . $className . ' already exists, skipped creating the class');
        }
    }
}
?>