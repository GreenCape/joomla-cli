Document
# UML

Joomla! CLI can produce UML diagrams using [PlantUML](http://plantuml.com).

**Synopsis:**
```bash
$ joomla document:uml [options]
```

**Options:**
```
  -j, --jar=JAR                  Path to the PlantUML jar file [default: "/home/nibra/Development/GreenCape/joomla-cli-test/vendor/greencape/joomla-cli/build/plantuml/plantuml.jar"]
  -c, --classmap[=CLASSMAP]      Path to the Joomla! classmap file [default: "joomla/libraries/classmap.php"]
  -p, --predefined[=PREDEFINED]  Path to predefined diagrams [default: "build/uml"]
  -s, --skin=SKIN                Name ('bw', 'bw-gradient' or 'default') of or path to the skin [default: "default"]
  -o, --output=OUTPUT            Output directory [default: "build/report/uml"]
      --no-svg                   Do not create .svg files, keep .puml files instead
```

## Diagram Types

### Class Diagrams

`document:uml` creates inheritance diagrams for all classes in the source directory. These diagrams are named `class-<classname>.svg`, their sources `class-<classname>.puml` (all lowercase).

### Annotations

`document:uml` extracts UML diagrams embedded into docblock comments for methods.. These diagrams are named `annotation-<classname>-<method>.svg`, their sources `annotation-<classname>-<method>.puml` (all lowercase).

## Joomla! Classes

To make diagrams as expressive as possible, information about the underlying system should be included.
The `document:uml` command allows to include predefined diagrams from a specific directory, usually `build/uml`.
Assuming the Joomla! source is located in the directory `joomla`, the following command will produce the predefined diagrams:

```bash
$ joomla document:uml --basepath=joomla --predefined=php --output=build/uml --no-svg 
``` 

* `--basepath=joomla`: this directory contains the sources to generate the diagrams from.
* `--predefined=php`: include the bundled diagrams for classes provided by PHP.
* `--output=build/uml`: store the diagrams in `build/uml`.
* `--no-svg`: don't create .svg files, keep the PlantUML sources instead for further inclusion.
