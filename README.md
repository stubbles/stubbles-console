stubbles/console
================

Support for command line applications.


Build status
------------

[![Build Status](https://secure.travis-ci.org/stubbles/stubbles-console.png)](http://travis-ci.org/stubbles/stubbles-console)
[![Coverage Status](https://coveralls.io/repos/stubbles/stubbles-console/badge.png?branch=master)](https://coveralls.io/r/stubbles/stubbles-console?branch=master)

[![Latest Stable Version](https://poser.pugx.org/stubbles/console/version.png)](https://packagist.org/packages/stubbles/console)
[![Latest Unstable Version](https://poser.pugx.org/stubbles/console/v/unstable.png)](//packagist.org/packages/stubbles/console)


Installation
------------

_stubbles/console_ is distributed as [Composer](https://getcomposer.org/)
package. To install it as a dependency of your package use the following
command:

    composer require "stubbles/console": "^5.1"


Requirements
------------

_stubbles/console_ requires at least PHP 5.6.


Console app class
-----------------

The main point for command line applications are their Console app classes. They
must extend `stubbles\console\ConsoleApp`, and have two purposes:

1. Provide a list of binding modules for the Stubbles IoC mechanism that tells
   it how to wire the object graph for the whole application.
2. Get the main entry of the logic injected and run it within its `run()` method.
   Depending on the outcome of the logic it should return a proper exit code,
   which means 0 for a successful execution and any other exit code for in case
   an error happened.

For details about the IoC part check the docs about [Apps in stubbles/core](https://github.com/stubbles/stubbles-core/wiki/Apps-binding-modules).
Generally, understanding the Inversion of Control functionality in [stubbles/core](https://github.com/stubbles/stubbles-core/wiki)
will help a lot in regard on how to design the further classes in your command
line app.

For an example of how a console app class might look like check
[ConsoleAppCreator](https://github.com/stubbles/stubbles-console/blob/master/src/main/php/creator/ConsoleAppCreator.php),
the Console app class behind the `createConsoleApp` script explained below.

## Exit codes

The Console app class' `run()` method should return a proper exit code. It
should be 0 if the run was successful, and a non-zero exit code in case an error
occurred.

It is recommended to use exit code between 21 and 255 for errors. Exit codes 1
to 20 are reserved by Stubbles Console and should not be used, whereas the upper
limit of 255 is a restriction by some operating systems.

### Reserved exit codes

* 10 Returned when one of the command line options or arguments for the app
  contains an error.
* 20 Returned when an uncatched exception occurred during the run of the console
  app class.


How to create a command line app
--------------------------------

In order to ease creation of console apps stubbles/console provides a helper
script that can create the skeleton of a Console app class, the fitting command
line script to call this Console app, and a unit test skeleton for this console
app class.

When in the root directory of your project and stubbles/console is installed,
simply type

    vendor/bin/createConsoleApp

First it will ask you for the full qualified name of the console app class to
create. Type it into the prompt and confirm with enter. There is no need to
escape the namespace separator. Second, it will ask you for the name of the
command line script which should be created. Type it's name into the prompt and
confirm with enter.

Once the script finishes you will find three new files in your application:

* a class in _src/main/php_ with the class name you entered, extending the
  `stubbles\console\ConsoleApp` class.
* a script in the _bin_ folder with the name you typed in second.
* a unit test for the app class in _src/test/php_ with the class name you entered.

At this point the app class, the script and the unit test are already fully
functional - they can be run but of course will do nothing.

From this point you can start and extend the generated class and unit test with
the functionality you want to implement.

### FAQ

**What happens if the entered class already exists?**

The creating script will check if a class with the given name already exists within the project. This includes all classes that can be loaded via the autoload functionality. If it does exist, creation of the app class is skipped.

**What happens if the script to be created already exists?**

Creation of the script will be skipped.

**What happens if a unit test in _src/test/php_ with this class name already exists?**

Creation of the unit test will be skipped.

**Can I use the createConsoleApp script to generate a script or a unit test for an already existing app?**

Yes, this is possible. Just enter the name of the existing app class. As the class already exists, it's creation will be skipped, but the script and unit test will still be created if they don't exist yet.


Provided binding modules
------------------------

When compiling the list of binding modules in the `__bindings()` method of your
console app class, you can make use of binding modules provided by
stubbles/console. The `stubbles\console\ConsoleApp` class which your own console
app class should extend provides static helper methods to create those binding
modules:

### `argumentParser()`

Creates a binding module that parses any arguments passed via command line and
adds them as bindings to become available for injection. See below for more
details about parsing command line arguments.


Parsing command line arguments
------------------------------

Most times a command line app needs arguments to be passed by the caller of the
script. When the argument binding module is added to the list of binding modules
(see above) stubbles/console will make those arguments available for injection
into the app's classes.

## Without special parsing

By default all arguments given via command line are made available as an array
and as single values. Suppose the command line call was `./exampleScript foo bar baz`
then the following values will be available for injection:

* `@Named('argv')`: the whole array of input values: `array('foo', 'bar', 'baz')`.
* `@Named('argv.0')`: the first value that is not the name of the called script, in this case _foo_.
* `@Named('argv.1')`: the second value that is not the name of the called script, in this case _bar_.
* `@Named('argv.2')`: the third value that is not the name of the called script, in this case _baz_.

Requesting a value that was not passed, e.g. `@Named('argv.3')` in this example,
will result in a `stubbles\ioc\BindingException`.

## With special parsing

In some cases you need more sophisticated argument parsing. This is also
possible:

```php
self::argumentParser()
        ->withOptions($options)
        ->withLongOptions(array $options)
```

Using this we can parse arguments like this:

`./exampleScript -n foo -d bar --other baz -f --verbose`

To get a proper parsing for this example the arguments binding module must be
configured as follows:

```php
self::argumentParser()
        ->withOptions('fn:d:')
        ->withLongOptions(array('other:', 'verbose'))
```

For more details about the grammar for the options check PHP's manual on [getopt()](http://php.net/getopt).

If arguments are parsed like this they become available for injection with the
following names:

* `@Named('argv')`: the whole array of input values: `array('n' => 'foo', 'd'=> 'bar', 'other' => 'baz', 'f' => false, 'verbose' => false)`.
* `@Named('argv.n')`: value of the option _-n_, in this case _foo_.
* `@Named('argv.d')`: value of the option _-d_, in this case _bar_.
* `@Named('argv.other')`: value of the option _--other_, in this case _baz_.
* `@Named('argv.f')`: value of the option _-f_, in this case `false`.
* `@Named('argv.verbose')`: value of the option _--verbose_, in this case `false`.

Any value being `false` is due to the fact that PHP's `getopt()` function sets
the values for arguments without values to `false`.

Requesting a value that was not passed, e.g. `@Named('argv.invalid')` in this
example, will result in a `stubbles\ioc\BindingException`.


Console app runner script
-------------------------

For each console app there should be a runner script that can be used to execute
the console app. When using the `createConsoleApp` script (see above) such a
script will be created automatically.


Testing Console apps
--------------------

Having all code required in an app in an app class has a huge advantage: you can
create a unit test that makes sure that the whole application with all
dependencies can be created. This means you can have a unit test like this:

```php
    /**
     * @test
     */
    public function canCreateInstance()
    {
        $this->assertInstanceOf(
                MyConsoleApp::class,
                MyConsoleApp::create(new Rootpath())
        );
    }
```

This test makes sure that all dependencies are bound and that an instance of the
app can be created. If you also have unit tests for all the logic you created
and you run those tests you can be pretty sure that the application will work.


### Tests for apps created with `createConsoleApp`

The unit test created with `createConsoleApp` will already provide two tests:

* A test that makes sure that the `run()` method returns with exit code 0 after
  a successful run.
* And finally a test that makes sure that an instance of the app can be created
  (see above for how this looks like).

From this point on it should be fairly easy to extend this unit test with tests
for the logic you implement in your app class.


Reading from command line
-------------------------

In order to read user input from the command line one can use the
`stubbles\console\ConsoleInputStream`. It is a normal [input stream](https://github.com/stubbles/stubbles-core/wiki/Stream-interfaces)
from which can be read.

If you want to get such an input stream injected in your class it is recommended
to typehint against `stubbles\streams\InputStream` and add a `@Named('stdin')`
annotation for this parameter:

```php
    /**
     * receive input stream to read from command line
     *
     * @param  InputStream  $in
     * @Named('stdin')
     */
    public function __construct(InputStream $in)
    {
        $this->in = $in;
    }
```


Writing to command line
-----------------------

To write to the command line there are two possibilities: either write directly
to standard out, or write to the error out. Both ways are implemented as an
[output stream](https://github.com/stubbles/stubbles-core/wiki/Stream-interfaces).

If you want to get such an output stream injected in your class it is
recommended to typehint against stubbles\streams\OutputStream and add a
`@Named('stout')` or `@Named('sterr')` respectively annotation for these
parameters:

```php
    /**
     * receive streams for standard and error out
     *
     * @param  OutputStream  $out
     * @param  OutputStream  $err
     * @Named{out}('stdout')
     * @Named{err}('stderr')
     */
    public function __construct(OutputStream $out, OutputStream $err)
    {
        $this->out = $out;
        $this->err = $err;
    }
```


Reading from and writing to command line
----------------------------------------

Sometimes there are situations when you need to read from and to write to
command line at the same time. That's where `stubbles\console\Console` comes
into play. It provides a facade to stdin input stream, stdout and stderr output
streams so you have a direct dependency to one class only instead of three. The
class provides methods to read and write:

### `prompt($message, $paramErrors = null)`
_Available since release 2.1.0._

Writes a message to stdout and returns a value reader similar to
[reading request parameters](https://github.com/stubbles/stubbles-input/wiki/ReadingRequestParameters).
In case you need access to error messages that may happen during value
validation you need to supply `stubbles\input\ParamErrors`, errors will be
accumulated therein under the param name stdin.

### `readValue($paramErrors = null)`
_Available since release 2.1.0._

Similar to `prompt()`, but without a message

### `confirm($message, $default = null)`
_Available since release 2.1.0._

Asks the user to confirm something. Repeats the message until user enters _y_ or
_n_ (case insensitive). In case a default is given and the users enters nothing
this default will be used - if the default is _y_ it will return `true`, and
`false` otherwise.

### `read($length = 8192)`
Reads input from stdin.

### `readLine($length = 8192)`
Reads input from stdin with line break stripped.

### `write($bytes)`
Write message to stdout.

### `writeLine($bytes)`
Write a line to stdout.

### `writeEmptyLine()`
_Available since release 2.6.0._
Write an empty line to stdout.

### `writeError($bytes)`
Write error message to stderr.

### `writeErrorLine($bytes)`
Write an error message line to stderr.

### `writeEmptyErrorLine()`
_Available since release 2.6.0._
Write an empty error message line to stderr.


Command line executor
---------------------

From time to time it is necessary to run another command line program from within
your application. Stubbles Console provides a convenient way to do this via the
`stubbles\console\Executor` interface. It is recommended to not create the
executor instance yourself but to get one injected.

It provides three different ways to run a command line program:

1. `execute($command, OutputStream  $out = null)`: This will simply execute the
   given command. If the executor receives an [output stream](https://github.com/stubbles/stubbles-core/wiki/Stream-interfaces)
   any output of the command is written to this stream.
2. `executeAsync($command)`: This will execute the command, but reading the
   output of the command can be done later via the returned `CommandInputStream`
   instance which is a normal [input stream](https://github.com/stubbles/stubbles-core/wiki/Stream-interfaces).
3. `executeDirect($command)`: The will execute the given command, and return its
   output as array, where one entry resembles one line of the output.

If the executed command returns an exit code other than 0 this is considered as
failure, resulting in a `\RuntimeException`.

### Redirecting output

If you want to redirect the output of the command to execute you can provide a
redirect option  via `redirectTo($redirect)`.

### Examples

Running a command, discarding its output:

```php
$executor->execute('git clone git://github.com/stubbles/stubbles-console.git');
```

Running a command and retrieve the output:

```php
$executor->execute('git clone git://github.com/stubbles/stubbles-console.git', $myOutputStream);
```

Running a command asynchronously:

```php
    $inputStream = $executor->executeAsync('git clone git://github.com/stubbles/stubbles-console.git');
    // ... do some other work here ...
    while (!$inputStream->eof()) {
        echo $inputStream->readLine();
    }
```

Directly receive command output:

```php
    $lines = $executor->executeDirect('git clone git://github.com/stubbles/stubbles-console.git');
    foreach ($lines as $line) {
        echo $line;
    }
```
