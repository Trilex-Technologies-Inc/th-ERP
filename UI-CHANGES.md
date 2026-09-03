# UI modernization changes

This version keeps the ERP business/database logic intact and modernizes the shared UI layer.

## Main changes
- Added Bootstrap 5.3.3 globally through the shared stylesheet helper.
- Added responsive viewport metadata.
- Replaced shared header/footer/title/menu-page layout tables with Bootstrap containers, flex utilities, cards, and navbar structure.
- Added a modern responsive theme in `include/therp_modern.css`.
- Added `include/therp_modern.js` to classify legacy tables at runtime:
  - form-layout tables receive responsive form styling;
  - actual data tables receive Bootstrap table styling and responsive horizontal wrappers.
- Rebuilt `common/login.php` using Bootstrap card/form layout instead of nested layout tables.
- Modernized common buttons, inputs, selects, borders/cards, navigation, spacing, and mobile behavior.

## Compatibility note
A full lint using the container's current PHP version encounters a pre-existing parse error in legacy `include/fpdf/fpdf.php` at line 432. The UI modernization does not modify that file.
