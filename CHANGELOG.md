7.0.0 (2016-07-31)
------------------

### BC breaks

  * raised minimum required PHP version to 7.0.0
  * introduced scalar type hints and strict type checking
  * removed deprecated classes and methods:
    * `stubbles\console\Executor::executeDirect($command)`, deprecated since 6.1.0
    * `stubbles\console\ConsoleExecutor`, deprecated since 6.0.0


### Other changes

  * added `stubbles\console\input\ArgumentParser::withCliOptionParser()`


6.1.0 (2016-06-22)
------------------

### BC breaks

  * deprecated `stubbles\console\Executor::executeDirect($command)`, use `$executor->execute($command, collect($array))` or `iterator_to_array($executor->outputOf($command))` instead, will be removed with 7.0.0


### Other changes

    * added `stubbles\console\collect()`, but only available when `stubbles\console\Executor` is used


6.0.1 (2016-06-17)
------------------

 * removed reference to _bin/stubcli_ in composer.json


6.0.0 (2016-06-12)
------------------

### BC breaks

  * raised minimum required PHP version to 5.6
  * removed _bin/stubcli_
  * `stubbles\console\Executor` is not an interface any more but an implementation
    * deprecated `stubbles\console\ConsoleExecutor`, will be removed with 7.0.0
  * changed how `stubbles\console\Executor` works with output streams:
    * removed `stubbles\console\Executor::streamOutputTo()`
    * removed `stubbles\console\Executor::out()`
    * `stubbles\console\Executor::execute()` now takes a callable as optional second argument which receives each single line
    * all methods now have an optional parameter `$redirect` with which output redirection can be influenced
  * added `stubbles\console\Executor::outputOf()`
  * moved `stubbles\input\console\ConsoleRequest` from stubbles/input to `stubbles\console\input\ConsoleRequest`
  * moved `stubbles\input\console\BaseConsoleRequest` from stubbles/input to `stubbles\console\input\BaseConsoleRequest`


5.1.0 (2015-08-03)
------------------

  * added `stubbles\console\WorkingDirectory`


5.0.1 (2015-06-17)
------------------

  * fixed wrong order of command line arguments


5.0.0 (2015-05-28)
------------------

### BC breaks

  * removed `stubbles\console\ConsoleApp::createArgumentsBindingModule()`, use `stubbles\console\ConsoleApp::argumentParser()` instead, was deprecated since 4.0.0

### Other changes

  * upgraded stubbles/core to 6.0.0


4.1.0 (2015-03-07)
------------------

  * implemented issue #49: Create console app should respect PSR-4


4.0.1 (2014-08-19)
------------------

  * fixed bug #50 Create console app does not use argumentParser() but removed method instead


4.0.0 (2014-08-17)
------------------

### BC breaks

  * removed `stubbles\console\ConsoleApp::createConsoleBindingModule()`, console binding module will be added by default
  * deprecated `stubbles\console\ConsoleApp::createArgumentsBindingModule()`, use `stubbles\console\ConsoleApp::argumentParser()` instead, will be removed with 5.0.0
  * change of annotation value names in request broker for user input classes:
    * `name` must now be `paramName`
    * `group` must now be `paramGroup`
    * `description` must now be `paramDescription`
    * `option` must now be `valueDescription`
  * changed all thrown stubbles/core exceptions to those recommended with stubbles/core 5.0.0
  * removed `stubbles\console\Executor::getOutputStream()`, use `stubbles\console\Executor::out()` instead, was deprecated since 3.0.0


### Other changes

  * upgraded to stubbles/core 5.0.0 and stubbles/input 4.0.0


3.0.0 (2014-07-31)
------------------

### BC breaks

  * removed namespace prefix `net`, base namespace is now `stubbles\console` only
  * deprecated `stubbles\console\Executor::getOutputStream()`, use `stubbles\console\Executor::out()` instead, will be removed with 4.0.0

### Other changes

  * upgraded to stubbles/core 4.0.0


2.6.0 (2013-10-28)
------------------

  * implemented #40: add new method `net\stubbles\console\Console::writeEmptyLine()` and `net\stubbles\console\Console::writeEmptyErrorLine()`
  * implemented #39: show stacktrace of uncatched exceptions


2.5.0 (2013-10-26)
------------------

  * removed script for compiling console apps to phars, use https://packagist.org/packages/clue/phar-composer instead


2.4.0 (2013-10-25)
------------------

  * added `net\stubbles\console\Console::writeLines()` and `net\stubbles\console\Console::writeErrorLines()`
  * `net\stubbles\console\Console` now implements `net\stubbles\streams\InputStream` and `net\stubbles\streams\OutputStream`
  * updated templates for creating console apps


2.3.0 (2013-05-02)
------------------

  * upgraded stubbles/core to ~3.0
  * ensure scripts always pass a realpath to prevent ugly pathes within application


2.2.0 (2013-02-06)
------------------

  * added initial phar support to run stubbles-console from inside a phar
     * added bin/compile to create a phar from of a console app
  * change dependency to stubbles-core from 2.1.* to ~2.1


2.1.2 (2012-08-14)
------------------

  * implemented issue #30: help should contain better explaination for arguments
     * introduced annotation `@AppDescription` to be used on `ConsoleApp` classes


2.1.1 (2012-07-31)
------------------

  * raised stubbles-core to 2.1.*


2.1.0 (2012-07-30)
------------------

  * User input instance is now created using ioc, this allows injection. Furthermore, the app instance itself is now applicable to be the user input target.
  * implemented issue #13: use filter API for values read from command line
     * added `net\stubbles\console/\Console::prompt()`
     * added `net\stubbles\console/\Console::confirm()`
     * added `net\stubbles\console/\Console::readValue()`
  * generated scripts can now be run from vendor dir


2.0.0 (2012-05-22)
------------------

  * Initial release.
