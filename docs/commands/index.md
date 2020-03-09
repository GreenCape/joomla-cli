Joomla CLI 0.2.0
# Joomla Command Line Interface

Joomla CLI (`joomla-cli`) is a tool for managing Joomla from the command line.
It works for all Joomla versions since 1.0.0, although it needs a current PHP installation.

Available commands are

- [build](build/build.md) - Performs all tests and generates documentation and the quality report
- [dist](dist/dist.md) - Generates the distribution
- [document](document/document.md) - Generates API documentation using the specified generator
- [help](help/help.md) - Displays help for a command
- [list](list/list.md) - Lists commands
- [quality](quality/quality.md) - Generates a quality report using CodeBrowser
- [test](test/test.md) - Runs all tests locally and in the test containers

**[Build](build/index.md)**
- [build:all](build/all.md) - Performs all tests and generates documentation and the quality report

**[Core](core/index.md)**
- [core:download](core/download.md) - Downloads a Joomla! version and unpacks it to the base path
- [core:version](core/version.md) - Reports the version of the Joomla! installation at the base path

**[Dist](dist/index.md)**
- [dist:clean](dist/clean.md) - Cleanup distribution directory
- [dist:prepare](dist/prepare.md) - Create and populate distribution directory

**[Docker](docker/index.md)**
- [docker:build](docker/build.md) - Generates the contents and prepares the test containers
- [docker:remove](docker/remove.md) - Removes the content of test containers
- [docker:start](docker/start.md) - Starts the test containers, building them only if not existing
- [docker:stop](docker/stop.md) - Stops and removes the test containers
- [docker:up](docker/up.md) - Starts the test containers after rebuilding them

**[Document](document/index.md)**
- [document:api](document/api.md) - Generates API documentation using the specified generator
- [document:changelog](document/changelog.md) - Generates CHANGELOG.md from the git commit history
- [document:clean](document/clean.md) - Cleans the API doc directory
- [document:uml](document/uml.md) - Generates UML diagrams

**[Extension](extension/index.md)**
- [extension:install](extension/install.md) - Installs a Joomla! extension

**[Patch](patch/index.md)**
- [patch:create](patch/create.md) - Creates a patch set ready to drop into an existing installation

**[Quality](quality/index.md)**
- [quality:cb](quality/cb.md) - Aggregates the results from all the measurement tools
- [quality:cpd](quality/cpd.md) - Generates pmd-cpd.xml using PHP CopyPasteDetector
- [quality:cs](quality/cs.md) - Checks the code style using PHP CodeSniffer
- [quality:depend](quality/depend.md) - Generates depend.xml and software metrics charts using PHP Depend
- [quality:md](quality/md.md) - Generates pmd.xml using PHP MessDetector

**[Self](self/index.md)**
- [self:update](self/update.md) - Updates the build environment

**[Template](template/index.md)**
- [template:override](template/override.md) - Creates template and layout overrides (Joomla! 1.5+)

**[Test](test/index.md)**
- [test:coverage](test/coverage.md) - Creates an consolidated HTML coverage report
- [test:integration](test/integration.md) - Runs integration tests on all test installations
- [test:system](test/system.md) - Runs system tests on all test installations
- [test:targets](test/targets.md) - Lists the test targets
- [test:unit](test/unit.md) - Runs local unit tests

## Common Options

All commands have these options in common:

Options:
```
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```
