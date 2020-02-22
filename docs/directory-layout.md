# Directory Layout

## The `build` directory
## The `docs` directory
## The `source` directory

```text
source/
  ├─ component/
  ├─ modules/
  ├─ plugins/
  ├─ templates/
  └─ manifest.xml
```
A project can only contain one component, but several modules, plugins and / or templates.

### The `component` directory

If the project contains a component, its sources are stored in the `component` directory.
The layout of the `component` directory is the same as at runtime.

```text
component/
  ├─ administrator/
  │    └─ components/
  │         └─ com_mycomponent/
  │              ├─ controllers/
  │              │    └─ myentity.php
  │              ├─ helpers/
  │              │    └─ mycomponent.php
  │              ├─ language/
  │              │    └─ en-GB/
  │              │         ├─ en-GB.com_mycomponent.ini
  │              │         └─ en-GB.com_mycomponent.sys.ini
  │              ├─ models/
  │              │    └─ myentity.php
  │              ├─ sql/
  │              │    ├─ install/
  │              │    │    └─ mysql/
  │              │    │         └─ mysql.sql
  │              │    └─ updates/
  │              │         └─ mysql/
  │              │              └─ 1.0.sql
  │              ├─ tables/
  │              │    └─ myentity.php
  │              ├─ views/
  │              │    └─ myentity/
  │              │         └─ tmpl/
  │              │              └─ default.php
  │              ├─ access.xml
  │              ├─ config.xml
  │              ├─ controller.php
  │              └─ mycomponent.php
  ├─ components/
  │    └─ com_mycomponent/
  │         ├─ controllers/
  │         │    └─ myentity.php
  │         ├─ language/
  │         │    └─ en-GB/
  │         │         └─ en-GB.com_mycomponent.ini
  │         ├─ layouts/
  │         │    └─ myentity/
  │         │         └─ page.php
  │         ├─ models/
  │         │    └─ myentity.php
  │         ├─ views/
  │         │    └─ myentity/
  │         │         ├─ tmpl/
  │         │         │    ├─ default.php
  │         │         │    └─ default.xml
  │         │         └─ view.html.php
  │         ├─ controller.php
  │         └─ mycomponent.php
  ├─ media/
  │    └─ com_mycomponent/
  │         ├─ css/
  │         │    ├─ style.css
  │         │    └─ style-uncompressed.css
  │         ├─ images/
  │         │    └─ joomla_powered_sm.png
  │         └─ js/
  │              ├─ script.js
  │              └─ script-uncompressed.js
  ├─ mycomponent.xml
  └─ script.php
```

### The `modules` directory

### The `plugins` directory

### The `templates` directory

## The `tests` directory

Although literature knows a myriad of different test categories, we actually only need to differentiate three types of tests:

- **Unit tests:**

  Tests that do not need a particular setup.
- **Integration tests (edge-to-edge):**

  Tests that need access to a Joomla! installation.
- **System (Acceptance) tests (end-to-end):**

  Tests that need the complete stack, including HTTP.

Each category gets its own directory beneath `/tests`, so they can be handled accordingly.  

```text
tests/
  ├─ unit/          tests that do not need a particular setup
  ├─ integration/   tests that need access to a Joomla installation
  └─ system/        tests that need the complete stack
```

## Summary

```text
<project root>
  ├─ build/              created on the fly, should not be included in source repository
  ├─ docs/               your documentation
  ├─ source/             the source code of your extension
  └─ tests/
       ├─ unit/          tests that do not need a particular setup
       ├─ integration/   tests that need access to a Joomla installation
       └─ system/        tests that need the complete stack
```
