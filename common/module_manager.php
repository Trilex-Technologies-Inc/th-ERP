<?php
include('include.php');
require_once('../include/module_system.php');
checkPermission(PERMISSION_ADMINISTRATE_USERS);

$manager = new ThERPModuleManager();
$message = '';
$messageType = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = getParam('module_name', '');
    $action = getParam('action', '');
    if (!checkModuleFormToken(getParam('form_token'))) {
        $message = 'The form expired. Please try again.';
        $messageType = 'danger';
    } elseif (!preg_match('/^[a-z][a-z0-9_-]*$/', $name)) {
        $message = 'Invalid module name.';
        $messageType = 'danger';
    } else {
        try {
            if ($action === 'install')
                $ok = $manager->install($name);
            elseif ($action === 'enable')
                $ok = $manager->setEnabled($name, true);
            elseif ($action === 'disable')
                $ok = $manager->setEnabled($name, false);
            elseif ($action === 'uninstall' && getParam('confirm') === 'yes')
                $ok = $manager->uninstall($name);
            else
                $ok = false;
            $message = $ok ? ucfirst($action) . ' completed.' : 'The module action could not be completed.';
            $messageType = $ok ? 'success' : 'warning';
        } catch (Throwable $error) {
            $message = 'Module action failed: ' . $error->getMessage();
            $messageType = 'danger';
        }
    }
}
$modules = $manager->all();
?>
<?php head('Module manager') ?>
<body>
<?php menubar('module_manager.php') ?>
<?php title('Module manager') ?>
<main class="module-admin-page">
    <header class="module-admin-hero">
        <div><span class="module-eyebrow">Extensions</span><h1>Module manager</h1><p>Install and control optional thERP business modules.</p></div>
        <div class="module-admin-hero-actions"><a class="btn btn-outline-primary" href="module_guide.php"><span aria-hidden="true">&#9776;</span> Module Development Guide</a><span class="module-admin-count"><?php echo count($modules) ?> available</span></div>
    </header>
    <?php if ($message !== '') { ?><div class="alert alert-<?php echo $messageType ?>"><?php echo htmlspecialchars($message) ?></div><?php } ?>
    <section class="module-admin-grid">
        <?php foreach ($modules as $name => $module) { ?>
        <article class="module-admin-card">
            <div class="module-admin-icon" aria-hidden="true"><?php echo $module['icon'] ?></div>
            <div class="module-admin-copy">
                <div class="module-admin-title"><h2><?php echo htmlspecialchars($module['title']) ?></h2><span class="badge <?php echo $module['enabled'] ? 'text-bg-success' : ($module['installed'] ? 'text-bg-secondary' : 'text-bg-light border') ?>"><?php echo $module['enabled'] ? 'Enabled' : ($module['installed'] ? 'Disabled' : 'Not installed') ?></span></div>
                <p><?php echo htmlspecialchars($module['description']) ?></p>
                <small>Version <?php echo htmlspecialchars($module['version']) ?><?php if ($module['author']) echo ' · ' . htmlspecialchars($module['author']) ?></small>
            </div>
            <div class="module-admin-actions">
                <?php if ($module['enabled']) { ?><a class="btn btn-primary btn-sm" href="<?php echo htmlspecialchars(moduleUrl($name, $module['default_action'])) ?>">Open</a><?php } ?>
                <?php if (!$module['installed']) { moduleActionForm($name, 'install', 'Install'); } ?>
                <?php if ($module['installed'] && !$module['enabled']) { moduleActionForm($name, 'enable', 'Enable'); } ?>
                <?php if ($module['enabled']) { moduleActionForm($name, 'disable', 'Disable', 'btn-outline-secondary'); } ?>
                <?php if ($module['installed']) { moduleActionForm($name, 'uninstall', 'Uninstall', 'btn-outline-danger', true); } ?>
            </div>
        </article>
        <?php } ?>
        <?php if (!$modules) { ?><div class="alert alert-info">No valid module packages were found in the modules directory.</div><?php } ?>
    </section>
</main>
<?php bottom() ?>
</body>
<?php
function moduleActionForm($name, $action, $label, $class = 'btn-primary', $confirm = false)
{
    echo "<form method='post' class='d-inline'" . ($confirm ? " onsubmit=\"return confirm('Uninstall this module and permanently delete its data?')\"" : '') . ">";
    echo "<input type='hidden' name='form_token' value='" . htmlspecialchars(moduleFormToken()) . "'>";
    echo "<input type='hidden' name='module_name' value='" . htmlspecialchars($name) . "'>";
    echo "<input type='hidden' name='action' value='" . htmlspecialchars($action) . "'>";
    if ($confirm) echo "<input type='hidden' name='confirm' value='yes'>";
    echo "<button class='btn btn-sm $class' type='submit'>$label</button></form>";
}
