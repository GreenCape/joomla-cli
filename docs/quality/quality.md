[Joomla! CLI](../index.md)
# Quality

The `quality` command generates a quality report using CodeBrowser.

It is a shortcut for calling

```bash
$ quality:depend
$ quality:mess-detect
$ quality:copy-paste-detect
$ quality:check-style
$ quality:code-browser
```

The report is located at `build/report/code-browser/index.html`.

**Synopsis:**
```bash
$ joomla quality [options]
```

**Arguments:**

None.
 
**Options:**
```
  -b, --basepath=BASEPATH  The root of the Joomla! installation. Defaults to the current working directory. [default: "."]
```
