[![Latest Stable Version](https://poser.pugx.org/greencape/joomla-cli/v/stable.png)](https://packagist.org/packages/greencape/joomla-cli)
[![Build Status](https://travis-ci.org/greencape/joomla-cli.png?branch=master)](https://travis-ci.org/greencape/joomla-cli)

# Joomla CLI

`joomla-cli` is a tool for managing Joomla! from the command line.

## Installation

### PHP Archive (PHAR)

The easiest way to obtain Joomla CLI is to download a [PHP Archive (PHAR)](http://php.net/phar) that has all required
dependencies of Joomla CLI bundled in a single file:

    wget https://phar.greencape.com/joomla-cli.phar
    chmod +x joomla-cli.phar
    mv joomla-cli.phar /usr/local/bin/joomla

You can also immediately use the PHAR after you have downloaded it, of course:

    wget https://phar.greencape.com/joomla-cli.phar
    php joomla-cli.phar

### Composer

Simply add a dependency on `greencape/joomla-cli` to your project's `composer.json` file if you use
[Composer](http://getcomposer.org/) to manage the dependencies of your project. Here is a minimal example of a
`composer.json` file that just defines a dependency on Joomla CLI:

    {
        "require": {
            "greencape/joomla-cli": "*"
        }
    }

For a system-wide installation via Composer, you can run:

    composer global require 'greencape/joomla-cli=*'

Make sure you have `~/.composer/vendor/bin/` in your path.

## Usage Examples

