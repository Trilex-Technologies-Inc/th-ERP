<?php include("include.php") ?>

<head>
<title>thERP - <?php echo tr("Configuration") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('configuration.php') ?>

<?php menupage_begin() ?>
<ul>
<li class=menupage><a href="categories.php" class=menupage><?php echo tr("Categories") ?></a></li>
<li class=menupage><a href="vatcategories.php" class=menupage><?php echo tr("VAT categories") ?></a></li>
<li class=menupage><a href="locations.php" class=menupage><?php echo tr("Locations") ?></a></li>
<li class=menupage><a href="attributes.php" class=menupage><?php echo tr("Attributes") ?></a></li>
<li class=menupage><a href="unittypes.php" class=menupage><?php echo tr("Unit types") ?></a></li>
</ul>
<?php menupage_end() ?>

</body>
