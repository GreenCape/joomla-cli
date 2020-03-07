# Environments

## Environment Definition File

An environment can be defined in a json file like this (`build/joomla/default.json`):

```json
{
  "name": "default",
  "server": {
    "type":"nginx",
    "offset": "UTC"
  },
  "database": {
    "driver": "mysql",
    "version": "latest",
    "host": "localhost",
    "port": "3306",
    "name": "joomla_test",
    "user": "sqladmin",
    "password": "sqladmin",
    "rootPassword": "root",
    "prefix": "jos_"
  },
  "joomla": {
    "version": "latest",
    "sampleData": "",
    "cache": {
      "enabled": false,
      "time": 15,
      "handler": "file"
    },
    "debug": {
      "system": true,
      "language": true
    },
    "meta": {
      "description": "Test installation",
      "keywords": "",
      "showVersion": true,
      "showTitle": true,
      "showAuthor": true
    },
    "sef": {
      "enabled": false,
      "rewrite": false,
      "suffix": false,
      "unicode": false
    },
    "feeds": {
      "limit": 10,
      "email": "author"
    },
    "session": {
      "lifetime": "15",
      "handler": "database"
    }
  }
}
```

## Environment Variables

All values from the environment definition file can be overridden by environment variables.
These variables can be set directly or in an `.env` file. 
The `.env` file in the project's root directory is loaded automatically if present.
Example (`build/joomla/.env`):

```dotenv
JCLI_NAME=default
JCLI_SERVER_TYPE=nginx
JCLI_SERVER_OFFSET=UTC
JCLI_DATABASE_DRIVER=mysql
JCLI_DATABASE_VERSION=latest
JCLI_DATABASE_HOST=localhost
JCLI_DATABASE_PORT=3306
JCLI_DATABASE_NAME=joomla_test
JCLI_DATABASE_USER=sqladmin
JCLI_DATABASE_PASSWORD=sqladmin
JCLI_DATABASE_ROOTPASSWORD=root
JCLI_DATABASE_PREFIX=jos_
JCLI_JOOMLA_VERSION=latest
JCLI_JOOMLA_SAMPLEDATA=
JCLI_JOOMLA_CACHE_ENABLED=0
JCLI_JOOMLA_CACHE_TIME=15
JCLI_JOOMLA_CACHE_HANDLER=file
JCLI_JOOMLA_DEBUG_SYSTEM=1
JCLI_JOOMLA_DEBUG_LANGUAGE=1
JCLI_JOOMLA_META_DESCRIPTION=Test installation
JCLI_JOOMLA_META_KEYWORDS=
JCLI_JOOMLA_META_SHOWVERSION=1
JCLI_JOOMLA_META_SHOWTITLE=1
JCLI_JOOMLA_META_SHOWAUTHOR=1
JCLI_JOOMLA_SEF_ENABLED=0
JCLI_JOOMLA_SEF_REWRITE=0
JCLI_JOOMLA_SEF_SUFFIX=0
JCLI_JOOMLA_SEF_UNICODE=0
JCLI_JOOMLA_FEEDS_LIMIT=10
JCLI_JOOMLA_FEEDS_EMAIL=author
JCLI_JOOMLA_SESSION_LIFETIME=15
JCLI_JOOMLA_SESSION_HANDLER=database
```
