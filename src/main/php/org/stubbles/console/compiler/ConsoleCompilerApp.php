<?php
/**
 * Your license or something other here.
 *
 * @package  org\stubbles\console\compiler
 */
namespace org\stubbles\console\compiler;
use net\stubbles\console\ConsoleApp;
use net\stubbles\streams\OutputStream;
/**
 * Command line app to create phar files from Stubbles Console scripts.
 */
class ConsoleCompilerApp extends ConsoleApp
{
    /**
     * phar file compiler
     *
     * @type  Compiler
     */
    private $compiler;
    /**
     * error output stream
     *
     * @type  OutputStream
     */
    private $err;
    /**
     * the script to compile
     *
     * @type  string
     */
    private $bin;

    /**
     * returns list of bindings used for this application
     *
     * @param   string  $projectPath
     * @return  \net\stubbles\ioc\module\BindingModule[]
     */
    public static function __bindings($projectPath)
    {
        return array(self::createArgumentsBindingModule(),
                     self::createConsoleBindingModule(),
                     self::createPropertiesBindingModule($projectPath)
        );
    }

    /**
     * constructor
     *
     * @param  Compiler      $compiler
     * @param  OutputStream  $err
     * @Inject
     * @Named{err}('stderr')
     */
    public function __construct(Compiler $compiler, OutputStream $err)
    {
        $this->compiler = $compiler;
        $this->err      = $err;
    }

    /**
     * sets the script to be compiled into a phar
     *
     * @param   string  $bin
     * @return  ConsoleCompilerApp
     * @Inject(optional=true)
     * @Named('argv.0')
     */
    public function setScript($bin)
    {
        $this->bin = $bin;
        return $this;
    }

    /**
     * runs the command and returns an exit code
     *
     * @return  int
     */
    public function run()
    {
        if (null == $this->bin) {
            $this->err->writeLine('No script passed. Please call with the script from bin to compile to a phar as argument.');
            return 30;
        }

        $this->compiler->compile($this->bin)->save();
        return 0;
    }
}
?>