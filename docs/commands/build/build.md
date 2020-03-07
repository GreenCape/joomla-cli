[Joomla CLI](../index.md) > [Build](index.md)
# build

Performs all tests and generates documentation and the quality report.

## Synopsis
```bash
$ joomla build:all [options]
$ joomla build
```

## Arguments
This command has no arguments.

## Options
```
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

Performs all tests and generates documentation and the quality report.

A valid manifest file is required in the base path. Its name and location
are defined in the `project.json` file.

