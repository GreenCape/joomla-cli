# The Configuration File `project.json`

Most of the information needed by the Joomla! CLI tool is provided in a single file `project.json`.

It can describe a single extension

```json
{
  "project": {
    "name": "Example Component",
    "version": "1.0.0",
    "paths": {
      "source": "source"
    }
  },
  "package": {
    "name": "com_example",
    "type": "component",
    "manifest": "manifest.xml"
  }
}
```

or a whole package with multiple extensions:

```json
{
  "project": {
    "name": "Example Package",
    "version": "1.0.0",
    "paths": {
      "source": "source"
    }
  },
  "package": {
    "name": "pkg_example",
    "manifest": "manifest.xml",
    "extensions": [
      {
        "name": "com_example",
        "type": "component",
        "manifest": "administrator/manifest.xml"
      },
      {
        "name": "mod_example",
        "type": "module",
        "manifest": "modules/mod_example/manifest.xml"
      },
      {
        "name": "plg_example",
        "type": "plugin",
        "group": "system",
        "manifest": "plugins/system/plg_example/manifest.xml"
      }
    ]
  }
}
```
