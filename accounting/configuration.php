<?php include("include.php") ?>

<head>
<title>thERP - <?php echo tr("Configuration") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("configuration.php") ?>
<?php title(tr("Configuration")) ?>
<?php menupage_begin() ?>
<div class="accounting-settings">
    <header class="accounting-settings-hero">
        <div class="accounting-settings-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 3 4 7v5c0 4.5 3.4 7.7 8 9 4.6-1.3 8-4.5 8-9V7l-8-4Z"/><path d="m8 12 2.5 2.5L16 9"/></svg>
        </div>
        <div>
            <span class="accounting-settings-eyebrow"><?php etr("General ledger setup") ?></span>
            <h1><?php etr("Configuration") ?></h1>
            <p><?php etr("Manage accounts, dimensions, groups, and defaults used throughout the general ledger.") ?></p>
        </div>
    </header>

    <div class="accounting-settings-heading">
        <div><span><?php etr("Reference data") ?></span><h2><?php etr("Accounting settings") ?></h2></div>
        <span class="accounting-settings-count">5 <?php etr("settings") ?></span>
    </div>

    <nav class="accounting-settings-grid" aria-label="<?php etr("Accounting configuration") ?>">
        <a class="accounting-settings-card accounting-settings-card-featured" href="accounts.php"><span class="accounting-settings-card-icon"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4zM8 9h8M8 13h5M8 17h3"/></svg></span><span class="accounting-settings-card-copy"><strong><?php etr("Accounts") ?></strong><small><?php etr("Create and manage general ledger accounts") ?></small></span><span class="accounting-settings-card-arrow" aria-hidden="true">&#8594;</span></a>
        <a class="accounting-settings-card" href="accountgroups.php"><span class="accounting-settings-card-icon accounting-settings-card-icon-green"><svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/><path d="M8 4v16M16 4v16"/></svg></span><span class="accounting-settings-card-copy"><strong><?php etr("Account groups") ?></strong><small><?php etr("Organize accounts for reporting") ?></small></span><span class="accounting-settings-card-arrow" aria-hidden="true">&#8594;</span></a>
        <a class="accounting-settings-card" href="accountconf.php"><span class="accounting-settings-card-icon accounting-settings-card-icon-orange"><svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/><circle cx="9" cy="7" r="2"/><circle cx="15" cy="12" r="2"/><circle cx="11" cy="17" r="2"/></svg></span><span class="accounting-settings-card-copy"><strong><?php etr("Account configuration") ?></strong><small><?php etr("Set default accounts for transactions") ?></small></span><span class="accounting-settings-card-arrow" aria-hidden="true">&#8594;</span></a>
        <a class="accounting-settings-card" href="dimensions.php"><span class="accounting-settings-card-icon accounting-settings-card-icon-blue"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4zM8 9h8M8 13h5M8 17h3"/></svg></span><span class="accounting-settings-card-copy"><strong><?php etr("Dimensions") ?></strong><small><?php etr("Manage reporting dimensions") ?></small></span><span class="accounting-settings-card-arrow" aria-hidden="true">&#8594;</span></a>
        <a class="accounting-settings-card" href="setup.php"><span class="accounting-settings-card-icon accounting-settings-card-icon-purple"><svg viewBox="0 0 24 24"><path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M18.4 5.6l-2.8 2.8M8.4 15.6l-2.8 2.8"/><circle cx="12" cy="12" r="4"/></svg></span><span class="accounting-settings-card-copy"><strong><?php etr("Setup") ?></strong><small><?php etr("Load accounting presets and defaults") ?></small></span><span class="accounting-settings-card-arrow" aria-hidden="true">&#8594;</span></a>
    </nav>
</div>

<?php menupage_end() ?>

</body>
