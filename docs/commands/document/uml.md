[Joomla CLI](../index.md) > [Document](index.md)
# document:uml

Generates UML diagrams.

## Synopsis
```bash
$ joomla document:uml [options]
```

## Arguments
This command has no arguments.

## Options
```
  -s, --source[=SOURCE]          The source directory
  -J, --jar=JAR                  Path to the PlantUML jar file [default: "/home/nibra/Development/GreenCape/joomla-cli/build/plantuml/plantuml.jar"]
  -c, --classmap[=CLASSMAP]      Path to the Joomla! classmap file [default: "joomla/libraries/classmap.php"]
  -p, --predefined[=PREDEFINED]  Path to predefined diagrams [default: "build/uml"]
  -S, --skin=SKIN                Name ('bw', 'bw-gradient' or 'default') of or path to the skin [default: "default"]
  -o, --output=OUTPUT            Output directory [default: "build/report/uml"]
      --no-svg                   Do not create .svg files, keep .puml files instead
```

## Description

Generates UML diagrams

