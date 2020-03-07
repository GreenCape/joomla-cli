[Joomla CLI](../index.md) > [Core](index.md)
# core:download

Downloads a Joomla! version and unpacks it to the base path.

## Synopsis
```bash
$ joomla core:download [options] [--] [<version>]
```

## Arguments
```
version                        The Joomla! version to install. [default: "latest"]
```

## Options
```
  -f, --file=FILE                Location of the version cache file [default: "/tmp/versions.json"]
  -c, --cache=CACHE              Location of the cache for Joomla! packages [default: ".cache"]
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

Downloads a Joomla! version and unpacks it to the base path.

`version` can be any existing version number, branch name or tag. If the
requested version is not found in the [official Joomla! release
list](https://github.com/joomla/joomla-cms/releases), the download command
looks for a matching tag in the official archive. Older versions not in
Joomla!'s archive down to version 1.0.0 are provided by [GreenCape's legacy
archive](https://github.com/GreenCape/joomla-legacy/releases).

