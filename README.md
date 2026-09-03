# th-ERP
th-ERP: ERP POS stock accounting payroll

## Web installer

For a new installation, open the application root in a browser. If `conf/config.php`
does not exist, th-ERP redirects to `/install/`. The installer checks server
requirements, creates and imports an empty database, creates the administrator,
and writes the configuration file. It locks automatically after configuration.

The web-server user must be able to create `conf/config.php`, and the supplied
database account must be allowed to create a database and tables. Use PHP 8.3 or
newer and remove write access from the configuration directory after installation.
