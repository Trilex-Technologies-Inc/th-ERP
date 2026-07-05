<?php include("include.php") ?>

<head>
<title>thERP - <?php echo tr("Configuration") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("configuration.php") ?>
<?php menupage_begin() ?>
<ul>
<li class=menupage><a href="accounts.php" class=menupage><?php echo tr("Accounts") ?></a></li>
<li class=menupage><a href="accountgroups.php" class=menupage><?php echo tr("Account groups") ?></a></li>
<li class=menupage><a href="accountconf.php" class=menupage><?php echo tr("Account configuration") ?></a></li>
<li class=menupage><a href="dimensions.php" class=menupage><?php echo tr("Dimensions") ?></a></li>
<li class=menupage><a href="setup.php" class=menupage><?php echo tr("Setup") ?></a></li>
</ul>

<?php menupage_end() ?>

</body>
