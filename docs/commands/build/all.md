[Joomla CLI](../index.md) > [Build](index.md)
# build:all

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
  -s, --source[=SOURCE]      The source directory
  -b, --basepath[=BASEPATH]  The path of the project root, defaults to current directory
  -l, --logs=LOGS            The logs directory [default: "build/logs"]
```

## Description

Performs all tests and generates documentation and the quality report.

A valid manifest file is required in the base path. Its name and location
are defined in the `project.json` file.

