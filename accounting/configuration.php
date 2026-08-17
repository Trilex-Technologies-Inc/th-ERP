<?php include("include.php") ?>

<head>
<title>thERP - <?php echo tr("Configuration") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("configuration.php") ?>
<?php title(tr("Configuration")) ?>
<?php menupage_begin() ?>
<main class="accounting-config-hub">
    <div class="accounting-section-heading">
        <span class="accounting-section-kicker"><?php etr("General ledger") ?></span>
        <h1><?php etr("Accounting configuration") ?></h1>
        <p><?php etr("Manage accounts, dimensions, groups, and ledger defaults.") ?></p>
    </div>
    <div class="accounting-config-grid">
        <a class="accounting-config-card" href="accounts.php"><strong><?php etr("Accounts") ?></strong><small><?php etr("Create and manage ledger accounts") ?></small><span aria-hidden="true">&#8594;</span></a>
        <a class="accounting-config-card" href="accountgroups.php"><strong><?php etr("Account groups") ?></strong><small><?php etr("Organize accounts into reporting groups") ?></small><span aria-hidden="true">&#8594;</span></a>
        <a class="accounting-config-card" href="accountconf.php"><strong><?php etr("Account configuration") ?></strong><small><?php etr("Set default accounts for transactions") ?></small><span aria-hidden="true">&#8594;</span></a>
        <a class="accounting-config-card" href="dimensions.php"><strong><?php etr("Dimensions") ?></strong><small><?php etr("Manage reporting dimensions") ?></small><span aria-hidden="true">&#8594;</span></a>
        <a class="accounting-config-card" href="setup.php"><strong><?php etr("Setup") ?></strong><small><?php etr("Load or update accounting presets") ?></small><span aria-hidden="true">&#8594;</span></a>
    </div>
</main>

<?php menupage_end() ?>

</body>
