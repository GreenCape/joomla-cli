[Joomla! CLI](../index.md) > [Document](index.md)
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
  -j, --jar=JAR                  Path to the PlantUML jar file [default: "/home/nibra/Development/GreenCape/joomla-cli/build/plantuml/plantuml.jar"]
  -c, --classmap[=CLASSMAP]      Path to the Joomla! classmap file [default: "joomla/libraries/classmap.php"]
  -p, --predefined[=PREDEFINED]  Path to predefined diagrams [default: "build/uml"]
  -s, --skin=SKIN                Name ('bw', 'bw-gradient' or 'default') of or path to the skin [default: "default"]
  -o, --output=OUTPUT            Output directory [default: "build/report/uml"]
      --no-svg                   Do not create .svg files, keep .puml files instead
  -b, --basepath=BASEPATH        The root of the Joomla! installation. Defaults to the current working directory. [default: "."]
  -h, --help                     Display this help message
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi                     Force ANSI output
      --no-ansi                  Disable ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## Description

Generates UML diagrams

