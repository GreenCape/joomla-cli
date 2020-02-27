# Joomla CLI

![SensioLabsInsight](https://insight.sensiolabs.com/projects/c2895e80-cc5a-4f4c-906f-3efe53bd6ff4/mini.png)
[![Code Climate](https://codeclimate.com/github/GreenCape/joomla-cli/badges/gpa.svg)](https://codeclimate.com/github/GreenCape/joomla-cli)
[![Test Coverage](https://codeclimate.com/github/GreenCape/joomla-cli/badges/coverage.svg)](https://codeclimate.com/github/GreenCape/joomla-cli/coverage)
[![Latest Stable Version](https://poser.pugx.org/greencape/joomla-cli/v/stable.png)](https://packagist.org/packages/greencape/joomla-cli)
[![Build Status](https://api.travis-ci.org/GreenCape/joomla-cli.svg?branch=develop)](https://travis-ci.org/greencape/joomla-cli)

`joomla-cli` is a tool for managing Joomla! from the command line.

## Installation

### Composer

Simply add a dependency on `greencape/joomla-cli` to your project's `composer.json` file if you use
[Composer](http://getcomposer.org/) to manage the dependencies of your project. Here is a minimal example of a
`composer.json` file that just defines a dependency on Joomla CLI:

    {
        "require": {
            "greencape/joomla-cli": "*@dev"
        }
    }

For a system-wide installation via Composer, you can run:

    composer global require 'greencape/joomla-cli=*'

Make sure you have `~/.composer/vendor/bin/` in your path.

## Documentation

The [documentation for the available commands](docs/commands/index.md) can be found in the [`docs` directory](docs).

## Development

This project uses Phing for task handling.

```text
Default target:
-------------------------------------------------------------------------------
 build     Builds the PHAR executable after proper preparation

Main targets:
-------------------------------------------------------------------------------
 build     Builds the PHAR executable after proper preparation
 clean     Remove vendor, composer.lock for clean re-install; remove phar artifacts
 composer  (Re-) install dependencies with Composer
 phar      Create PHAR archive of Joomla CLI and all of its dependencies
 phpcs     Find coding standard violations using PHP_CodeSniffer
 phpunit   Run unit tests with PHPUnit
```

### Testing

```bash
$ phing phpunit -Dbasedir=.
```

### Building the PHAR Archive

```bash
$ phing build -Dbasedir=.
```

The generated `joomla-cli-x.x.x.phar` file is located in the `build/` directory.
