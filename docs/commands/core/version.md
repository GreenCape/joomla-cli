[Joomla CLI](../index.md) > [Core](index.md)
# core:version

Reports the version of the Joomla installation at the base path.

## Synopsis
```bash
$ joomla core:version [options]
```

## Arguments
This command has no arguments.

## Options
```
  -l, --long               The long version info, eg. Joomla x.y.z Stable [ Codename ] DD-Month-YYYY HH:ii GMT (default).
  -s, --short              The short version info, eg. x.y.z
  -r, --release            The release info, eg. x.y
  -b, --basepath=BASEPATH  The root of the Joomla installation. Defaults to the current working directory. [default: "."]
  -h, --help               Display this help message
  -q, --quiet              Do not output any message
  -V, --version            Display this application version
      --ansi               Force ANSI output
      --no-ansi            Disable ANSI output
  -n, --no-interaction     Do not ask any interactive question
  -v|vv|vvv, --verbose     Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## Description

Reports the version of the Joomla installation at the base path

