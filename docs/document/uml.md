Document
# UML


**Synopsis:**
```bash
$ joomla document:uml [options] [--]
```

**Arguments:**
```
```

**Options:**
```
```

## Joomla! Classes

To make diagrams as expressive as possible, information about the underlying system should be included.
The `document:uml` command allows to include predefined diagrams from a specific directory, usually `build/uml`.

```bash
$ joomla document:uml --basepath=joomla --predefined=php --output=build/uml 
``` 

## PHP Classes

Joomla! CLI comes with predefined class diagrams for classes provided by PHP.
To include these diagrams, use the option `--predefined=php`.

