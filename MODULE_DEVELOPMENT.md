# Developing optional thERP modules

Optional modules are additive packages under `modules/`. Existing thERP areas
such as Sales, Inventory, and Payroll continue to use their original routes.

## Package structure

```text
modules/example/
├── module.json
├── install.php
├── uninstall.php
├── include.php
├── main.php
├── new.php
├── edit.php
└── delete.php
```

The manifest must use the directory name as its `id`:

```json
{
  "id": "example",
  "title": "Example",
  "description": "An optional thERP module.",
  "version": "1.0.0",
  "author": "Your name",
  "default_action": "main"
}
```

Routes use the CiteCRM-style `module:action` format:

```text
common/module.php?page=example:main
common/module.php?page=example:edit&id=12
```

Only lowercase letters, numbers, underscores, and hyphens are accepted in
module and action names. The dispatcher only loads an action from a discovered,
installed, and enabled module.

`install.php` and `uninstall.php` run inside the existing application context,
so they can use `sql()`, `query()`, and `sql_string()`. A module must only create
or remove tables it owns. Prefix owned tables with the module name.

All state-changing actions should accept POST only, include `moduleFormToken()`
in the form, validate it with `checkModuleFormToken()`, validate inputs, scope
records to the current user where appropriate, and escape rendered values.

Administrators manage packages from Common > Modules. See
`modules/taskmanager/` for a complete working example.

