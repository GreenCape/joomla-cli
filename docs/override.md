# Override

The override command creates a set of template and layout overrides for a Joomla! installation.
Joomla introduced template / layout overrides in [version 1.5](http://docs.joomla.org/Understanding_Output_Overrides) (components and modules),
and extended it in [version 2.5](http://docs.joomla.org/Layout_Overrides_in_Joomla) (plugins) and in [version 3](http://docs.joomla.org/J3.x:Sharing_layouts_across_views_or_extensions_with_JLayout) (layouts).

**Synopsis:**
```bash
$ joomla override [options] [--] <template>
```

**Arguments:**
```
  template                 The path to the template, relative to the base path.
```

**Options:**
```
  -f, --force              Overwrite existing overrides in the template directory.
```
