<?php include("include.php") ?>

<head>
<title>thERP - <?php echo tr("Configuration") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('configuration.php') ?>
<?php title(tr("Configuration")) ?>

<?php menupage_begin() ?>
<div class="sales-settings">
	<header class="sales-settings-hero">
		<div class="sales-settings-mark" aria-hidden="true">
			<svg viewBox="0 0 24 24"><path d="M12 2v20M17 6H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/><path d="M4 5h16M4 19h16"/></svg>
		</div>
		<div>
			<span class="sales-settings-eyebrow"><?php etr("Sales setup") ?></span>
			<h1><?php etr("Configuration") ?></h1>
			<p><?php etr("Manage sales pricing and invoice defaults used throughout customer orders.") ?></p>
		</div>
	</header>

	<div class="sales-settings-heading">
		<div>
			<span><?php etr("Reference data") ?></span>
			<h2><?php etr("Sales settings") ?></h2>
		</div>
		<span class="sales-settings-count">2 <?php etr("settings") ?></span>
	</div>

	<nav class="sales-settings-grid" aria-label="<?php etr("Sales configuration") ?>">
		<a href="pricelists.php" class="sales-settings-card sales-settings-card-featured">
			<span class="sales-settings-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h10"/><path d="M18 15v6M15 18h6"/></svg></span>
			<span class="sales-settings-card-copy">
				<strong><?php etr("Price lists") ?></strong>
				<small><?php etr("Maintain customer-facing price levels and VAT behavior") ?></small>
			</span>
			<span class="sales-settings-card-arrow" aria-hidden="true">&#8594;</span>
		</a>

		<a href="invoice_conf.php" class="sales-settings-card">
			<span class="sales-settings-card-icon sales-settings-card-icon-green" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6M9 16h3"/></svg></span>
			<span class="sales-settings-card-copy">
				<strong><?php etr("Invoice") ?></strong>
				<small><?php etr("Configure invoice numbering, labels, and document defaults") ?></small>
			</span>
			<span class="sales-settings-card-arrow" aria-hidden="true">&#8594;</span>
		</a>
	</nav>
</div>
<?php menupage_end() ?>

</body>
