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

<main class="container-fluid px-0">
	<div class="card border-0 shadow-sm overflow-hidden">
		<div class="card-header bg-white py-3">
			<h2 class="h5 fw-bold mb-1"><?php echo tr("Setup") ?></h2>
			<p class="text-secondary small mb-0"><?php echo tr("Load payroll setup data") ?></p>
		</div>
		<div class="list-group list-group-flush">
			<a class="list-group-item list-group-item-action d-flex flex-wrap justify-content-between align-items-center gap-2 py-3" href="setup.php?setup=th">
				<span class="fw-semibold"><?php echo tr("Load thai setup") ?></span>
				<span class="badge text-bg-light border"><?php echo tr("Setup") ?></span>
			</a>
			<a class="list-group-item list-group-item-action d-flex flex-wrap justify-content-between align-items-center gap-2 py-3" href="setup.php?setup=se">
				<span class="fw-semibold"><?php echo tr("Load swedish setup") ?></span>
				<span class="badge text-bg-light border"><?php echo tr("Setup") ?></span>
			</a>
			<a class="list-group-item list-group-item-action d-flex flex-wrap justify-content-between align-items-center gap-2 py-3" href="setup.php?setup=demo">
				<span class="fw-semibold"><?php echo tr("Load demo data") ?></span>
				<span class="badge text-bg-light border"><?php echo tr("Demo") ?></span>
			</a>
		</div>
	</div>
</main>
<?php bottom() ?>
</body>
