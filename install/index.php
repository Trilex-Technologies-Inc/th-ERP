<?php
declare(strict_types=1);

session_start();
$root = dirname(__DIR__);
$configFile = $root . '/conf/config.php';
$legacyConfigFile = $root . '/conf/configure.php';
$schemaFile = $root . '/sql/therp.sql';
$installed = file_exists($configFile) || file_exists($legacyConfigFile);

if (empty($_SESSION['installer_csrf'])) {
    $_SESSION['installer_csrf'] = bin2hex(random_bytes(32));
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function requirement(string $label, bool $passed, string $detail = ''): array
{
    return ['label' => $label, 'passed' => $passed, 'detail' => $detail];
}

$configDir = $root . '/conf';
$configTargetWritable = is_dir($configDir) ? is_writable($configDir) : is_writable($root);
$requirements = [
    requirement('PHP 8.3 or newer', version_compare(PHP_VERSION, '8.3.0', '>='), PHP_VERSION),
    requirement('MySQLi extension', extension_loaded('mysqli')),
    requirement('Session extension', extension_loaded('session')),
    requirement('JSON extension', extension_loaded('json')),
    requirement('Iconv extension', extension_loaded('iconv')),
    requirement('Database schema is readable', is_readable($schemaFile)),
    requirement('Configuration directory is writable', $configTargetWritable),
];
$requirementsPassed = !in_array(false, array_column($requirements, 'passed'), true);

$values = [
    'dbhost' => trim((string)($_POST['dbhost'] ?? 'localhost')),
    'dbname' => trim((string)($_POST['dbname'] ?? 'therp')),
    'dbuser' => trim((string)($_POST['dbuser'] ?? 'therp')),
    'admin_user' => trim((string)($_POST['admin_user'] ?? 'admin')),
    'admin_name' => trim((string)($_POST['admin_name'] ?? 'Administrator')),
    'timezone' => trim((string)($_POST['timezone'] ?? date_default_timezone_get())),
];
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$installed) {
    if (!hash_equals($_SESSION['installer_csrf'], (string)($_POST['csrf'] ?? ''))) {
        $errors[] = 'The form expired. Reload the installer and try again.';
    }
    if (!$requirementsPassed) {
        $errors[] = 'Resolve all failed server requirements before installing.';
    }
    if (!preg_match('/^[A-Za-z0-9_]+$/', $values['dbname'])) {
        $errors[] = 'Database name may contain only letters, numbers, and underscores.';
    }
    if ($values['dbhost'] === '' || $values['dbuser'] === '') {
        $errors[] = 'Database host and user are required.';
    }
    if (!preg_match('/^[A-Za-z0-9_.-]{1,16}$/', $values['admin_user'])) {
        $errors[] = 'Administrator username must be 1–16 letters, numbers, dots, underscores, or hyphens.';
    }
    if ($values['admin_name'] === '' || strlen($values['admin_name']) > 64) {
        $errors[] = 'Administrator name is required and must be no longer than 64 characters.';
    }
    $adminPassword = (string)($_POST['admin_password'] ?? '');
    if (strlen($adminPassword) < 12 || strlen($adminPassword) > 32) {
        $errors[] = 'Administrator password must be 12–32 characters.';
    }
    if ($adminPassword !== (string)($_POST['admin_password_confirm'] ?? '')) {
        $errors[] = 'Administrator passwords do not match.';
    }
    if (!in_array($values['timezone'], timezone_identifiers_list(), true)) {
        $errors[] = 'Select a valid PHP timezone.';
    }

    if (!$errors) {
        mysqli_report(MYSQLI_REPORT_OFF);
        $db = @new mysqli($values['dbhost'], $values['dbuser'], (string)($_POST['dbpwd'] ?? ''));
        if ($db->connect_errno) {
            $errors[] = 'Database connection failed: ' . $db->connect_error;
        } else {
            $database = $values['dbname'];
            if (!$db->query("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8 COLLATE utf8_general_ci")) {
                $errors[] = 'Could not create the database: ' . $db->error;
            } elseif (!$db->select_db($database)) {
                $errors[] = 'Could not select the database: ' . $db->error;
            } else {
                $tableCount = 0;
                $countResult = $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '" . $db->real_escape_string($database) . "'");
                if ($countResult) {
                    $tableCount = (int)$countResult->fetch_row()[0];
                }
                if ($tableCount > 0) {
                    $errors[] = 'The selected database is not empty. Use a new empty database.';
                } else {
                    $schema = file_get_contents($schemaFile);
                    if ($schema === false || !$db->multi_query($schema)) {
                        $errors[] = 'Schema import failed: ' . $db->error;
                    } else {
                        do {
                            if ($result = $db->store_result()) {
                                $result->free();
                            }
                            if (!$db->more_results()) {
                                break;
                            }
                        } while ($db->next_result());
                        if ($db->errno) {
                            $errors[] = 'Schema import failed: ' . $db->error;
                        }
                    }
                }
            }

            if (!$errors) {
                $db->begin_transaction();
                $stmt = $db->prepare('UPDATE `user` SET username=?, full_name=?, password=?, language=\'en\', admin=1 WHERE username=\'admin\'');
                if (!$stmt) {
                    $errors[] = 'Could not prepare the administrator account: ' . $db->error;
                } else {
                    $stmt->bind_param('sss', $values['admin_user'], $values['admin_name'], $adminPassword);
                    if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                        $errors[] = 'Could not create the administrator account: ' . $stmt->error;
                    }
                    $stmt->close();
                }
                if (!$errors) {
                    $groupStmt = $db->prepare('UPDATE user_group SET username=? WHERE username=\'admin\'');
                    if (!$groupStmt) {
                        $errors[] = 'Could not prepare administrator permissions: ' . $db->error;
                    } else {
                        $groupStmt->bind_param('s', $values['admin_user']);
                        if (!$groupStmt->execute()) {
                            $errors[] = 'Could not assign administrator permissions: ' . $groupStmt->error;
                        }
                        $groupStmt->close();
                    }
                }
                if ($errors) {
                    $db->rollback();
                } else {
                    $db->commit();
                }
            }

            if (!$errors) {
                if (!is_dir($configDir) && !mkdir($configDir, 0750, true)) {
                    $errors[] = 'Could not create the configuration directory.';
                } else {
                    $config = "<?php\n"
                        . "define('DBHOST', " . var_export($values['dbhost'], true) . ");\n"
                        . "define('DBUSER', " . var_export($values['dbuser'], true) . ");\n"
                        . "define('DBPWD', " . var_export((string)($_POST['dbpwd'] ?? ''), true) . ");\n"
                        . "define('DBNAME', " . var_export($values['dbname'], true) . ");\n"
                        . "define('TZ', " . var_export($values['timezone'], true) . ");\n";
                    $temporary = $configFile . '.tmp';
                    if (file_put_contents($temporary, $config, LOCK_EX) === false || !chmod($temporary, 0640) || !rename($temporary, $configFile)) {
                        @unlink($temporary);
                        $errors[] = 'Database installed, but conf/config.php could not be written. Fix permissions and retry with a new empty database.';
                    } else {
                        unset($_SESSION['installer_csrf']);
                        $success = true;
                        $installed = true;
                    }
                }
            }
            $db->close();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>th-ERP Installer</title>
  <link rel="stylesheet" href="../include/bootstrap.min.css">
  <style>body{background:#f4f6f8}.installer{max-width:820px;margin:3rem auto}.card{box-shadow:0 .25rem 1rem rgba(0,0,0,.08)}.req{display:flex;justify-content:space-between;gap:1rem}.status-ok{color:#157347}.status-fail{color:#b02a37}</style>
</head>
<body>
<main class="container installer">
  <div class="card"><div class="card-body p-4 p-md-5">
    <h1 class="h2 mb-2">th-ERP installation</h1>
    <p class="text-muted">Check the server, initialize an empty database, and create the first administrator.</p>
    <?php if ($success): ?>
      <div class="alert alert-success"><strong>Installation complete.</strong> The installer is now locked.</div><a class="btn btn-primary" href="../common/modules.php">Open th-ERP</a>
    <?php elseif ($installed): ?>
      <div class="alert alert-info">th-ERP is already configured. To protect the database, this installer cannot run again while a configuration file exists.</div><a class="btn btn-primary" href="../common/modules.php">Open th-ERP</a>
    <?php else: ?>
      <h2 class="h5 mt-4">Server requirements</h2>
      <ul class="list-group mb-4"><?php foreach ($requirements as $item): ?><li class="list-group-item req"><span><?= h($item['label']) ?><?= $item['detail'] ? ' (' . h($item['detail']) . ')' : '' ?></span><strong class="<?= $item['passed'] ? 'status-ok' : 'status-fail' ?>"><?= $item['passed'] ? 'Pass' : 'Fail' ?></strong></li><?php endforeach; ?></ul>
      <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
      <form method="post" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= h($_SESSION['installer_csrf']) ?>">
        <h2 class="h5">Database</h2><p class="small text-muted">The database user must be allowed to create databases and tables. The selected database must be empty.</p>
        <div class="row g-3 mb-4">
          <div class="col-md-6"><label class="form-label" for="dbhost">Host</label><input class="form-control" id="dbhost" name="dbhost" required value="<?= h($values['dbhost']) ?>"></div>
          <div class="col-md-6"><label class="form-label" for="dbname">Database name</label><input class="form-control" id="dbname" name="dbname" required value="<?= h($values['dbname']) ?>"></div>
          <div class="col-md-6"><label class="form-label" for="dbuser">Database user</label><input class="form-control" id="dbuser" name="dbuser" required value="<?= h($values['dbuser']) ?>"></div>
          <div class="col-md-6"><label class="form-label" for="dbpwd">Database password</label><input class="form-control" type="password" id="dbpwd" name="dbpwd"></div>
        </div>
        <h2 class="h5">Administrator</h2>
        <div class="row g-3 mb-4">
          <div class="col-md-6"><label class="form-label" for="admin_user">Username</label><input class="form-control" id="admin_user" name="admin_user" maxlength="16" required value="<?= h($values['admin_user']) ?>"></div>
          <div class="col-md-6"><label class="form-label" for="admin_name">Full name</label><input class="form-control" id="admin_name" name="admin_name" maxlength="64" required value="<?= h($values['admin_name']) ?>"></div>
          <div class="col-md-6"><label class="form-label" for="admin_password">Password</label><input class="form-control" type="password" id="admin_password" name="admin_password" minlength="12" maxlength="32" required></div>
          <div class="col-md-6"><label class="form-label" for="admin_password_confirm">Confirm password</label><input class="form-control" type="password" id="admin_password_confirm" name="admin_password_confirm" minlength="12" maxlength="32" required></div>
          <div class="col-md-6"><label class="form-label" for="timezone">Timezone</label><input class="form-control" id="timezone" name="timezone" required value="<?= h($values['timezone']) ?>"><div class="form-text">Example: Asia/Tehran</div></div>
        </div>
        <button class="btn btn-primary" type="submit" <?= !$requirementsPassed ? 'disabled' : '' ?>>Install th-ERP</button>
      </form>
    <?php endif; ?>
  </div></div>
</main>
</body>
</html>
