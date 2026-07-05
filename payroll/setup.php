<?php 
include("include.php");

if (getParam("setup") == "th") {
	tx("runScript", array("../sql/thai-payroll.sql"));
}
if (getParam("setup") == "se") {
	tx("runScript", array("../sql/clean.sql"));
	tx("runScript", array("../sql/swedish-payroll.sql"));
}
if (getParam("setup") == "demo") {
	tx("runScript", array("../sql/demodata.sql"));
}

?>

<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Setup") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php include("menubar.php") ?>
<?php title(tr("Setup")) ?>

<ul>
<li class=menupage><a href="setup.php?setup=th"><?php echo tr("Load thai setup") ?></a></li>
<li class=menupage><a href="setup.php?setup=se"><?php echo tr("Load swedish setup") ?></a></li>
<li class=menupage><a href="setup.php?setup=demo"><?php echo tr("Load demo data") ?></a></li>
</ul>
</body>
