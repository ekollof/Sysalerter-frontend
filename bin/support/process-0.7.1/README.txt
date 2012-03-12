process.py -- a Python module for process control
=================================================

Download the latest process.py packages from here:
    (source) http://trentm.com/downloads/process/0.7.1/process-0.7.1.zip


Home            : http://trentm.com/projects/process/
License         : MIT (see LICENSE.txt)
Platforms       : Windows, Linux, Mac OS X, Solaris, other Un*x
Current Version : 0.7.1
Dev Status      : fairly mature, heavily used in a commercial product, not
                  perfect though
Requirements    : Python >= 2.3 (http://www.activestate.com/ActivePython/),
                  which.py >= 1.0 (http://trentm.com/projects/which/)


What's new?
-----------

I have moved hosting of `process.py` from my old [Starship
pages](http://starship.python.net/~tmick/) to this site. The current
version includes a number of fixes that we at ActiveState had made to
[Komodo's](http://www.activestate.com/Products/Komodo/) private copy of
`process.py`.

**WARNING**: If you upgrade my [which.py](../which/) to v1.1.0 and use
this `process.py`, you must use v0.7.1 or greater. This is because of a
slight `_version_/__version__` semantic change in `which.py`.


Why process.py?
---------------

`process.py` is a (rather large) Python module to make process control
easier and more consistent on Windows, Linux, and Mac OS X (and other
Un*ces). The current mechanisms (`os.popen*`, `os.system`, `os.exec*`,
`os.spawn*`) all have limitations.

A quick list of some reasons to use `process.py`:

- You don't have to handle quoting the arguments of your command line.
  You can pass in a command string or an argv.
- You can specify the current working directory (cwd) and the
  environment (env) for the started process.
- On Windows you can spawn a process without a console window opening.
- You can wait for process termination or kill the running process
  without having to worry about weird platform issues. (Killing on
  Windows should first give the process a chance to shutdown cleanly.
  Killing on Linux will not work from a different thread than the
  process that created it.)
- The `ProcessProxy` class allows you to interact in a
  pseudo-event-based way with the spawned process. I.e., you can pass
  in file-like object for any of stdin, stdout, or stderr to handle
  interaction with the process.

Note that since I developed `process.py`, Python has grown (in version
2.4) the new
[subprocess module](http://docs.python.org/lib/module-subprocess.html).
I haven't yet had the chance to do a feature comparison of `process.py`
and `subprocess` but the latter is definitely more capable than the
older Python process control mechanisms. If you are considering using
`process.py` you should definitely checkout `subprocess` as well.


Install Notes
-------------

Download the latest `process.py` source package, unzip it, and run
`setup.py install`:

    unzip process-0.7.1.zip
    cd process-0.7.1
    python setup.py install

If your install fails then please visit [the Troubleshooting
FAQ](http://trentm.com/faq.html#troubleshooting-python-package-installation).


Getting Started
---------------

Currently the best intro to `process.py` is its module documentation.
Either install `process.py` and run:

    pydoc process
    
or just take a look at `process.py` in your editor or
[here](process.py).


Change Log
----------

### v0.7.1
- Fix a mistake in porting Komodo change 73508 (changing _joinArgv())
  to process.py in revision 326 that made process.py useless.

### v0.7.0
- Change version attributes and semantics. Before: had a _version_
  tuple. After: __version__ is a string, __version_info__ is a tuple.

TODO: recover changelog from before this version

