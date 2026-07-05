<?php 
include("include.php");

if (getParam("setup") == "bas") {
	tx("runScript", array("../sql/bas.sql"));
}

?>

<head>
<?php metatag() ?>
<title>thERP - <?php echo tr("Setup") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php include("menubar.php") ?>
<?php title(tr("Setup")) ?>

<ul>
<li class=menupage><a href="setup.php?setup=bas"><?php echo tr("Load BAS accounts (Sweden)") ?></a></li>
</ul>
</body>
