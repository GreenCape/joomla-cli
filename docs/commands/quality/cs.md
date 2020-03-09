[Joomla CLI](../index.md) > [Quality](index.md)
# quality:cs

Checks the code style using PHP CodeSniffer.

## Synopsis
```bash
$ joomla quality:check-style [options]
$ joomla quality:cs
```

## Arguments
This command has no arguments.

## Options
```
  -s, --source=SOURCE            The source directory [default: "source"]
  -l, --logs=LOGS                The logs directory [default: "build/logs"]
  -b, --basepath=BASEPATH        The root of the project [default: "."]
  -e, --environment=ENVIRONMENT  The environment definition [default: ""]
  -h, --help                     Display this help message
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi                     Force ANSI output
      --no-ansi                  Disable ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## Description

Checks the code style using PHP CodeSniffer

