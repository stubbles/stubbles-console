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
