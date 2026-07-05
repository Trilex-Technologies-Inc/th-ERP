<?php include("include.php") ?>

<head>
<title>thERP - <?php echo tr("Configuration") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('configuration.php') ?>

<?php menupage_begin() ?>
<ul>
<li class=menupage><a href="pricelists.php" class=menupage><?php echo tr("Price lists") ?></a></li>
<li class=menupage><a href="invoice_conf.php" class=menupage><?php echo tr("Invoice") ?></a></li>
</ul>
<?php menupage_end() ?>

</body>
