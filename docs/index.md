Joomla CLI
# Joomla Command Line Interface

Joomla CLI (`joomla-cli`) is a tool for managing Joomla! from the command line.
It works for all Joomla! versions since 1.0.0, although it needs a current PHP installation.

Available commands are

**Core**
- [download](core/download.md) - download a specific Joomla! version
- [install](core/install.md) - install Joomla!
- [version](core/version.md) - get version information for a Joomla! installation

**Extension**
- [install](extension/install.md) - install an extension

**Template**
- [override](template/override.md) - create template overrides

## Common Options

All commands have these options in common:

Options:
```
  -b, --basepath=BASEPATH  The root of the Joomla! installation. Defaults to the current working directory. [default: "."]
  -h, --help               Display this help message
  -q, --quiet              Do not output any message
  -V, --version            Display this application version
      --ansi               Force ANSI output
      --no-ansi            Disable ANSI output
  -n, --no-interaction     Do not ask any interactive question
  -v|vv|vvv, --verbose     Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```
