<?php
include('include.php');
include('policy.inc');

checkPermission(PERMISSIONID_SELL);

$del_listid = getParam('del_listid');
if (!isEmpty($del_listid)) {
	$sql = "
	delete from pricelist
	where listid=$del_listid";
	sql($sql);
}

if (isSave()) {
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$listid = getParam("listid_$i");
		$description = getParam("description_$i");
		if ($description != getParam("old_description_$i")) {
			sql("update pricelist set description='$description' where listid=$listid");
		}
		$i++;
	}
	$listid_new = getParam('listid_new');
	$description_new = getParam('description_new');
	if (!isEmpty($listid_new)) {
		$sql = "
		insert into pricelist (listid, description)
		values ($listid_new, '$description_new')";
		sql($sql);
	}
}

$sql = "
select
  a.listid,
  description
from pricelist a
";

$rs = query($sql);
?>

<html>

<head>
	<?php metatag() ?>
	<title>thERP - <?php echo tr("Price lists") ?></title>
	<?php styleSheet() ?>
</head>

<body>

	<?php
	menubar("configuration.php");
	title("<a href='configuration.php'>" . tr("Configuration") . "</a> > " . tr("Price lists"));
	?>

	<main class="price-lists-page">
		<header class="price-lists-intro">
			<div class="price-lists-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h10"/><path d="M18 15v6M15 18h6"/></svg>
			</div>
			<div>
				<span class="price-lists-eyebrow"><?php etr("Sales setup") ?></span>
				<h1><?php etr("Price lists") ?></h1>
				<p><?php etr("Maintain the named price levels available to customers and sales orders.") ?></p>
			</div>
		</header>

	<form action="pricelists.php" method="POST">
		<section class="price-lists-card card border-0 shadow-sm overflow-hidden">
			<div class="card-header bg-white price-lists-header">
				<div><span><?php etr("Reference data") ?></span><h2><?php etr("Configured price lists") ?></h2></div>
			</div>
			<div class="price-lists-table">
				<div class="price-lists-row price-lists-head">
					<div><?php etr("Delete") ?></div>
					<div><?php etr("Id") ?></div>
					<div><?php etr("Description") ?></div>
				</div>
				<?php
				$i = 0;
				while ($row = fetch($rs)) {
					echo "<div class='price-lists-row'>";
					echo "<div class='price-lists-delete'>";
					deleteIcon("pricelists.php?del_listid=$row->listid");
					echo "</div>";
					echo "<div><span class='price-list-id'>#" . htmlspecialchars($row->listid) . "</span></div>";
					echo "<div class='price-lists-field'>";
					hidden("listid_$i", $row->listid);
					textBox("description_$i", $row->description);
					hidden("old_description_$i", $row->description);
					echo "</div></div>";
					$i++;
				}
				hidden('count', $i);
				?>
				<div class="price-lists-row price-lists-new">
					<div></div>
					<div class="price-lists-field"><?php textBox('listid_new', '', 6) ?></div>
					<div class="price-lists-field"><?php textBox('description_new', '') ?></div>
				</div>
			</div>
			<div class="price-lists-footer">
				<span><?php echo htmlspecialchars($i) ?> <?php etr("Price lists") ?></span>
				<?php saveButton() ?>
			</div>
		</section>
	</form>
	</main>
	<?php bottom() ?>
</body>

</html>
