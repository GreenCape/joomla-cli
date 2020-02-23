# Joomla Command Line Interface

Joomla CLI (`joomla-cli`) is a tool for managing Joomla! from the command line.
It works for all Joomla! versions since 1.0.0, although it needs a current PHP installation.

Available commands are

- [build](build/build.md) - perform all tests and generate documentation and the quality report
- [dist]() - generate the distribution
- [document]() - generate API documentation using the specified generator
- [help]() - display help for a command
- [list]() - list all available commands
- [quality]() - generate a quality report using CodeBrowser
- [test](test/test.md) - run all tests locally and in the test containers

**Core**
- [core:download](core/download.md) - download a specific Joomla! version and unpack it to the base path
- [core:version](core/version.md) - get version information for a Joomla! installation

**Distribution**
- [dist:clean]() - cleanup distribution directory
- [dist:prepare]() - create and populate distribution directory

**Docker**
- [docker:build]() - generate the contents and prepare the test containers
- [docker:remove]() - remove the content of test containers
- [docker:start]() - start the test containers, building them only if not existing
- [docker:stop]() - stop and removes the test containers
- [docker:up]() - starts the test containers after rebuilding them

**Document**
- [document:api]() - generate API documentation using the specified generator
- [document:changelog]() - generate CHANGELOG.md from the git commit history
- [document:clean]() - clean the API doc directory
- [document:uml]() - generate UML diagrams

**Extension**
- [extension:install](extension/install.md) - install a Joomla! extension

**Patch**
- [patch:create]() - create a patch set ready to drop into an existing installation

**Quality**
- [quality:check-style]() - generate `checkstyle.xml` using PHP CodeSniffer
- [quality:cs]() - alias for `quality:check-style`
- [quality:code-browser]() - aggregate the results from all the measurement tools
- [quality:cb]() - alias for `quality:code-browser`
- [quality:copy-paste-detect]() - generate `pmd-cpd.xml` using PHP CopyPasteDetector
- [quality:cpd]() - alias for `quality:copy-paste-detector`
- [quality:depend]() - generate `depend.xml` and software metrics charts using PHP Depend
- [quality:mess-detect]() - generate `pmd.xml` using PHP MessDetector
- [quality:md]() - alias for `quality:mess-detect`

**Self**
- [self:update]() - update Joomla!CLI

**Template**
- [template:override](template/override.md) - create template overrides and layout overrides (Joomla! 1.5+)

**Test**
- [test:coverage]() - create a consolidated HTML coverage report
- [test:integration]() - run integration tests on all test installations
- [test:system]() - run system tests on all test installations
- [test:targets]() - list the test targets
- [test:unit]() - run local unit tests

## Common Options

All commands have these options in common:

Options:
```
  -b, --basepath=BASEPATH  The root of the Joomla! installation. Defaults to the current working directory. [default: "."]
  -h, --help               Display this help message
  -q, --quiet              Do not output any message
  -V, --version            Display this application version
      --ansi               Force ANSI output
      --no-ansi            Disable ANSI output
  -n, --no-interaction     Do not ask any interactive question
  -v|vv|vvv, --verbose     Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```
