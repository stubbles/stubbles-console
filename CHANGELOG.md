4.0.0 (2014-08-??)
------------------

### BC breaks

  * removed `stubbles\console\ConsoleApp::createConsoleBindingModule()`, console binding module will be added by default
  * deprecated `stubbles\console\ConsoleApp::createArgumentsBindingModule()`, use `stubbles\console\ConsoleApp::bindArguments()` instead, will be removed with 5.0.0
  * change of annotation value names in request broker for user input classes: `name` must now be `paramName`, group must now be `paramGroup`
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
