<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\console
 */
namespace org\stubbles\console\test;
/**
 * Helper class for the test.
 *
 * @AppDescription("Real awesome command line app (c) 2012 Stubbles Development Team")
 */
class BrokeredUserInput
{
    /**
     * test property
     *
     * @type  string
     */
    private $bar = null;
    /**
     * test property
     *
     * @type  string
     */
    private $baz = null;
    /**
     * verbosity switch
     *
     * @type  bool
     */
    private $verbose = false;

    /**
     * test method without parameter
     *
     * @Request[Bool](paramName='verbose', paramGroup='noparam')
     */
    public function enableVerbose()
    {
        $this->verbose = true;
    }

    /**
     * test method without parameter
     *
     * @Request[Bool](paramName='v', paramGroup='noparam')
     */
    public function enableVerboseDifferently()
    {
        $this->verbose = true;
    }

    /**
     * test method
     *
     * @return  bool
     */
    public function isVerbose()
    {
        return $this->verbose;
    }

    /**
     * test method
     *
     * @Request[String](paramName='argv.1', paramGroup='arg', paramDescription='other-id')
     * @param  string  $arg
     */
    public function setOtherArgument($arg)
    {
        $this->bar = $arg;
    }

    /**
     * test method
     *
     * @Request[String](paramName='argv.0', paramGroup='arg', paramDescription='application-id')
     * @param  string  $arg
     */
    public function setArgument($arg)
    {
        $this->bar = $arg;
    }

    /**
     * test method
     *
     * @Request[String](paramName='bar1', paramGroup='other', paramDescription='Set the bar option.')
     * @param  string  $bar
     */
    public function setBar($bar)
    {
        $this->bar = $bar;
    }

    /**
     * test method
     *
     * @Request[String](paramName='bar2', required=true, paramGroup='main', paramDescription='Set the other bar option.')
     * @param  string  $bar
     */
    public function setOtherBar($bar)
    {
        $this->bar = $bar;
    }

    /**
     * test method
     *
     * @return  string
     */
    public function getBar()
    {
        return $this->bar;
    }

    /**
     * test method
     *
     * @Request[Mock](paramName='o', valueDescription="WOW_LEVEL" required=true, paramGroup='main', paramDescription='For the wow.')
     * @param  string  $baz
     */
    public function setMainBaz($baz)
    {
        $this->baz = $baz;
    }

    /**
     * test method
     *
     * @Request[Mock](paramName='u', paramGroup='other', paramDescription='Set another option.')
     * @param  string  $baz
     */
    public function setBaz($baz)
    {
        $this->baz = $baz;
    }

    /**
     * test method
     *
     * @return  string
     */
    public function getBaz()
    {
        return $this->baz;
    }
}
