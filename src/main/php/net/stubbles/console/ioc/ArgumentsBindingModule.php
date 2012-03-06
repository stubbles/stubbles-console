<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles\console
 */
namespace net\stubbles\console\ioc;
use net\stubbles\ioc\Binder;
use net\stubbles\ioc\module\BindingModule;
use net\stubbles\lang\BaseObject;
use net\stubbles\lang\exception\ConfigurationException;
/**
 * Binding module to configure the binder with arguments.
 */
class ArgumentsBindingModule extends BaseObject implements BindingModule
{
    /**
     * switch whether stubcli was used to run the command
     *
     * @type  bool
     */
    private $stubcliUsed;
    /**
     * options to be used for parsing the arguments
     *
     * @type  string
     */
    protected $options   = null;
    /**
     * long options to be used for parsing the arguments
     *
     * @type  string[]
     */
    protected $longopts  = array();

    /**
     * constructor
     *
     * @param  bool  $stubcliUsed  switch whether stubcli was used to run the command
     */
    public function __construct($stubcliUsed = false)
    {
        $this->stubcliUsed = $stubcliUsed;
    }

    /**
     * sets the options to be used for parsing the arguments
     *
     * @api
     * @param   string  $options
     * @return  ArgumentsBindingModule
     */
    public function withOptions($options)
    {
        if ($this->stubcliUsed && !strstr($options, 'c')) {
            $options .= 'c:';
        }

        $this->options = $options;
        return $this;
    }

    /**
     * sets the long options to be used for parsing the arguments
     *
     * @api
     * @param   string[]  $options
     * @return  ArgumentsBindingModule
     */
    public function withLongOptions(array $options)
    {
        if ($this->stubcliUsed && null === $this->options) {
            $this->options = 'c:';
        }

        $this->longopts = $options;
        return $this;
    }

    /**
     * configure the binder
     *
     * @param  Binder  $binder
     */
    public function configure(Binder $binder)
    {
        $args = $this->getArgs();
        $binder->bindConstant()
               ->named('argv')
               ->to($args);
        foreach ($args as $position => $value) {
            $binder->bindConstant()
                   ->named('argv.' . $position)
                   ->to($value);
        }
    }

    /**
     * returns parsed arguments
     *
     * @return  array
     * @throws  ConfigurationException
     */
    protected function getArgs()
    {
        if (null === $this->options) {
            $vars = $_SERVER['argv'];
            array_shift($vars); // script name
        } else {
            $vars = $this->getopt($this->options, $this->longopts);
            if (false === $vars) {
                throw new ConfigurationException('Error parsing "' . join(' ', $_SERVER['argv']) . '" with ' . $this->options . ' and ' . join(' ', $this->longopts));
            }
        }

        return $vars;
    }

    /**
     * helper method to enable proper testing
     *
     * @param   string    $options   options to be used for parsing the arguments
     * @param   string[]  $longopts  long options to be used for parsing the arguments
     * @return  array
     */
    protected function getopt($options, array $longopts)
    {
        return getopt($options, $longopts);
    }
}
?>