[Joomla! CLI](../index.md): [Installation](index.md)
# Joomla Installation

## Joomla as the Main Project

```bash
$ joomla core:install [-b <path>] [<version>]
```

If no Joomla code is found (i.e., the Joomla version can not be identified) at the provided location (`<path>`, defaults to current directory), the download of the specified (default: latest stable) Joomla version is started.

## Joomla for Quick Exploration Testing

### Joomla in a Subdirectory

```bash
$ joomla core:install -b joomla
```

### Joomla in Docker Containers

```bash
$ joomla core:install --docker
```
