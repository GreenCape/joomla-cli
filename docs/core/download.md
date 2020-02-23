[Joomla! CLI](../index.md) > [Core](core.md)
# Download

Download a Joomla! version and unpack it to the base path.

**Synopsis:**
```bash
$ joomla core:download [options] [--] [<version>]
```

**Arguments:**
```
  version                  The Joomla! version to install. [default: "latest"]
```
`version` can be any existing version number, branch name or tag. If the requested version is not found in the [official Joomla! release list](https://github.com/joomla/joomla-cms/releases), the download command looks for a matching tag in the official archive. Older versions not in Joomla!'s archive down to version 1.0.0 are provided by [GreenCape's legacy archive](https://github.com/GreenCape/joomla-legacy/releases).
 
**Options:**
```
  -b, --basepath=BASEPATH  The root of the Joomla! installation. Defaults to the current working directory. [default: "."]
  -c, --cache=CACHE        Location of the cache for Joomla! packages [default: ".cache"]
  -f, --file=FILE          Location of the version cache file [default: "/tmp/versions.json"]
```
