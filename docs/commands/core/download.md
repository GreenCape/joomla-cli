[Joomla CLI](../index.md) > [Core](index.md)
# core:download

Downloads a Joomla! version and unpacks it to the given path.

## Synopsis
```bash
$ joomla core:download [options] [--] [<version>]
```

## Arguments
```
version               The Joomla! version to install. [default: "latest"]
```

## Options
```
  -j, --joomla=JOOMLA   The root of the Joomla installation [default: "joomla"]
  -f, --file=FILE       Location of the version cache file [default: "/tmp/versions.json"]
  -c, --cache=CACHE     Location of the cache for Joomla! packages [default: ".cache"]
```

## Description

Downloads a Joomla! version and unpacks it to the given path.

`version` can be any existing version number, branch name or tag. If the
requested version is not found in the [official Joomla! release
list](https://github.com/joomla/joomla-cms/releases), the download command
looks for a matching tag in the official archive. Older versions not in
Joomla!'s archive down to version 1.0.0 are provided by [GreenCape's legacy
archive](https://github.com/GreenCape/joomla-legacy/releases).

