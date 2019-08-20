# Joomla Command Line Interface

`joomla-cli` is a tool for managing Joomla! from the command line.
It works for all Joomla! versions since 1.5.0, although it needs a current PHP installation.

Available commands are

- [download](download.md) - download a specific Joomla! version
- [install](install.md) - install an extension
- [override](override.md) - create template overrides
- [version](version.md) - get version information for a Joomla! installation

## Common Options

All sub-commands have these options in common:

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
