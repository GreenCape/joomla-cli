# Directory Layout

## The `build` directory
## The `docs` directory
## The `source` directory
## The `tests` directory

Although literature knows a myriad of different test categories, we actually only need to differentiate three types of tests:

- **Unit tests:**

  Tests that do not need a particular setup.
- **Integration tests (edge-to-edge):**

  Tests that need access to a Joomla! installation.
- **System (Acceptance) tests (end-to-end):**

  Tests that need the complete stack, including HTTP.

Each category gets its own directory beneath `/tests`, so they can be handled accordingly.  

## Summary

```text
<project root>
├─ build/           created on the fly, should not be included in source repository
├─ docs/            your documentation
├─ source/          the source code of your extension
└─ tests/
   ├─ unit          tests that do not need a particular setup
   ├─ integration   tests that need access to a Joomla installation
   └─ system        tests that need the complete stack
```
