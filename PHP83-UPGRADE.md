# PHP 8.3 Upgrade

This project has been updated for PHP 8.3 compatibility while preserving its existing application behavior and Bootstrap UI changes.

## Changes made

- Replaced the removed `mysql_*` database extension with `mysqli` in the shared database layer.
- Replaced direct `mysql_insert_id()` calls with the shared `insert_id()` helper.
- Replaced removed `mysql_field_name()` / `mysql_num_fields()` usage with `mysqli` equivalents.
- Replaced removed `ereg()` usage with `preg_match()`.
- Updated bundled legacy FPDF 1.53 code:
  - Added a PHP 8 compatible `__construct()` constructor.
  - Replaced deprecated/removed curly-brace string offsets with square-bracket offsets.
  - Replaced removed `each()` iteration with `foreach`.
  - Removed calls to `get_magic_quotes_runtime()` and `set_magic_quotes_runtime()`.
- Replaced deprecated `utf8_decode()` calls with an `iconv()` based compatibility helper.
- Made `isEmpty()` safe when passed `null`, avoiding PHP 8.x deprecation warnings.
- Validated every PHP source file with the PHP CLI syntax checker. All 206 PHP files pass under PHP 8.4, which also covers PHP 8.3 syntax compatibility.

## Server requirements

Enable these PHP extensions on the PHP 8.3 server:

- `mysqli`
- `iconv`
- `session`
- `json`
- `filter`
- `zlib` (recommended for PDF/image handling)

Depending on enabled ERP features, also enable common extensions such as `gd` and `mbstring`.

## Important

The application is a legacy codebase and still contains older application patterns such as SQL assembled directly from request values. The compatibility upgrade does not redesign application logic or security architecture. Database credentials and configuration should be tested on a staging environment before production deployment.
