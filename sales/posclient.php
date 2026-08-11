<?php
include('include.php');

checkPermission(PERMISSIONID_SELL);

if (getParam('action') == 'create') {
	header('Location: salesorder.php?customerid=' . CUSTOMERID_CASH . '&action=create');
	die;
}
?>

<head>
<title>thERP - <?php etr("Point of sale") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar('index.php') ?>
<?php title(tr("Point of sale")) ?>

<main class="browser-pos-page">
	<section class="browser-pos-hero card border-0 shadow-sm overflow-hidden">
		<div class="card-body p-4 p-lg-5">
			<div class="browser-pos-content">
				<div class="browser-pos-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24"><path d="M5 4h14v16H5V4Zm3 3h8v4H8V7Zm0 8h2m2 0h2m2 0h0M8 18h2m2 0h2m2 0h0"/></svg>
				</div>
				<div class="browser-pos-copy">
					<span><?php etr("Browser POS") ?></span>
					<h1><?php etr("Point of sale") ?></h1>
					<p><?php etr("Create a cash sale, add products, take payment, and print the receipt directly from your browser.") ?></p>
				</div>
				<a class="btn btn-primary btn-lg browser-pos-start" href="posclient.php?action=create"><?php etr("Start new sale") ?> <span aria-hidden="true">&#8594;</span></a>
			</div>
		</div>
		<div class="browser-pos-features">
			<div><strong><?php etr("No installation") ?></strong><span><?php etr("Works in modern browsers") ?></span></div>
			<div><strong><?php etr("Secure session") ?></strong><span><?php etr("Uses your current ERP login") ?></span></div>
			<div><strong><?php etr("Receipt printing") ?></strong><span><?php etr("Browser-native PDF printing") ?></span></div>
		</div>
	</section>
</main>

<?php bottom() ?>
</body>
