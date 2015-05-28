<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles/console
 */
namespace org\stubbles\console\test;
use stubbles\console\ConsoleApp;
/**
 * Helper class to test binding module creations.
 *
 * @since  2.0.0
 */
class ConsoleAppUsingBindingModule extends ConsoleApp
{
    /**
     * creates mode binding module
     *
     * @return  \stubbles\console\ioc\ArgumentParser
     */
    public static function returnArgumentParser()
    {
        return self::argumentParser();
    }

    /**
     * runs the command
     */
    public function run() { }
}
