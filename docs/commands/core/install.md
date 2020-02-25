[Joomla! CLI](../index.md) > [Core](index.md)
# core:install

Installs Joomla!.

## Synopsis
```bash
$ joomla core:install [options]
```

## Arguments
This command has no arguments.

## Options
```
  -a, --admin=ADMIN        The admin user name and password, separated by colon [default: "admin:admin"]
  -e, --email=EMAIL        The admin email address [default: "admin@localhost"]
  -t, --db-type=DB-TYPE    The database type [default: "mysqli"]
  -d, --database=DATABASE  The database connection. Format  <user>:<pass>@<host>:<port>/<database> [default: "sqladmin:sqladmin@localhost:3306/database"]
  -r, --root=ROOT          The database root password [default: "root"]
  -p, --prefix=PREFIX      The table prefix [default: "jos_"]
  -b, --basepath=BASEPATH  The root of the Joomla! installation. Defaults to the current working directory. [default: "."]
  -h, --help               Display this help message
  -q, --quiet              Do not output any message
  -V, --version            Display this application version
      --ansi               Force ANSI output
      --no-ansi            Disable ANSI output
  -n, --no-interaction     Do not ask any interactive question
  -v|vv|vvv, --verbose     Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## Description

Installs Joomla!

