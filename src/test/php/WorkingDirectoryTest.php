<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace stubbles\console;
/**
 * Test for stubbles\console\WorkingDirectory.
 *
 * @group  console
 * @since  5.1.0
 */
class WorkingDirectoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function currentIsGivenOriginalWhenNotChanged()
    {
        $workingDirectory = new WorkingDirectory(__DIR__);
        assertEquals(__DIR__, $workingDirectory->current());
    }

    /**
     * @test
     */
    public function currentIsGetCwdWhenNoOriginalGivenAndNotChanged()
    {
        $workingDirectory = new WorkingDirectory();
        assertEquals(getcwd(), $workingDirectory->current());
    }

    /**
     * @test
     */
    public function changeToReturnsFalseWhenChangeFails()
    {
        $workingDirectory = new WorkingDirectory();
        assertFalse($workingDirectory->changeTo('/doesNotExist'));
    }

    /**
     * @test
     */
    public function changeToDoesNotChangeCurrentIfChangeFails()
    {
        $workingDirectory = new WorkingDirectory(__DIR__);
        $workingDirectory->changeTo('/doesNotExist');
        assertEquals(__DIR__, $workingDirectory->current());
    }

    /**
     * @test
     */
    public function changeToReturnsTrueWhenChangeSucceeds()
    {
        $workingDirectory = new WorkingDirectory();
        assertTrue($workingDirectory->changeTo(__DIR__));
    }

    /**
     * @test
     */
    public function changeToChangesCurrentOnSuccess()
    {
        $workingDirectory = new WorkingDirectory();
        $workingDirectory->changeTo(__DIR__);
        assertEquals(__DIR__, $workingDirectory->current());
    }

    /**
     * @test
     */
    public function restoreChangesCurrentToOriginal()
    {
        $workingDirectory = new WorkingDirectory(__DIR__);
        $workingDirectory->changeTo(__DIR__ . '/..');
        $workingDirectory->restoreOriginal();
        assertEquals(__DIR__, $workingDirectory->current());
    }
}