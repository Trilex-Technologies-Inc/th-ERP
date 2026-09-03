<?php
	include('include.php');

    $name = getParam('name');
	$mode = getParam('mode');

	$del_supplierid = getParam('del_supplierid');
	if (!isEmpty($del_supplierid)) {
		sql("delete from supplier where supplierid=$del_supplierid");
	}
	$selectSQL = "
	select
	    supplierid,
	    name
	from supplier
	where name like '$name%'";
	$selectionMode = in_array($mode, array('createorder', 'createpayable', 'payable', 'payment'));

?>

<head>
<title>thERP - <?php etr("Suppliers") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('purchase.php') ?>
<?php
if ($mode == 'createpayable')
	$title = tr("Register payable") . " > " . tr("Select supplier");
else if ($mode == 'payment')
	$title = tr("Enter payment") . " > " . tr("Select supplier");
else if ($mode == 'createorder')
	$title = tr("Create purchase order") . " > " . tr("Select supplier");
else
	$title = tr("Suppliers");
title($title);
?>

<main class="suppliers-page<?php echo $selectionMode ? ' is-selection' : '' ?>">
	<header class="suppliers-intro">
		<div class="suppliers-intro-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM19 8v6M16 11h6"/></svg>
		</div>
		<div>
			<span class="suppliers-eyebrow"><?php echo $selectionMode ? tr("Supplier selection") : tr("Purchasing") ?></span>
			<h1><?php echo $selectionMode ? tr("Choose a supplier") : tr("Suppliers") ?></h1>
			<p><?php echo $mode == 'createorder' ? tr("Select the supplier for your new purchase order.") : tr("Find and manage supplier records used in purchasing.") ?></p>
		</div>
		<?php if ($mode == 'createorder') { ?>
			<div class="supplier-step" aria-label="<?php etr("Step 1 of 2") ?>"><span>1</span><div><small><?php etr("Step 1 of 2") ?></small><strong><?php etr("Select supplier") ?></strong></div></div>
		<?php } else { ?>
			<div class="suppliers-new"><?php newButton("supplier.php?mode=$mode") ?></div>
		<?php } ?>
	</header>

	<form action="suppliers.php" method="GET" class="suppliers-filter card border-0 shadow-sm">
		<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode) ?>"/>
		<div class="card-body">
			<div class="suppliers-section-heading">
				<div><span><?php etr("Search") ?></span><h2><?php etr("Find supplier") ?></h2></div>
			</div>
			<div class="row g-3 align-items-end">
				<div class="col-12 col-md-9 col-lg-10">
					<label class="form-label fw-semibold" for="supplier-search"><?php etr("Supplier name") ?></label>
					<input id="supplier-search" type="text" name="name" value="<?php echo htmlspecialchars($name) ?>" placeholder="<?php etr("Search by supplier name") ?>"/>
				</div>
				<div class="col-12 col-md-3 col-lg-2 d-grid"><?php searchButton() ?></div>
			</div>
		</div>
	</form>

	<section class="suppliers-results card border-0 shadow-sm overflow-hidden">
		<div class="card-header bg-white suppliers-results-header">
			<div><span><?php etr("Directory") ?></span><h2><?php echo $selectionMode ? tr("Select supplier") : tr("Suppliers") ?></h2></div>
			<?php if ($selectionMode) { ?><small><?php etr("Choose a row to continue") ?></small><?php } ?>
		</div>
		<div class="erp-table-responsive">
			<table class="erp-data-table suppliers-table">
				<thead><tr>
					<?php if (!$selectionMode) { ?><th class="supplier-delete"><?php etr("Delete") ?></th><?php } ?>
					<th class="supplier-id-column"><?php etr("Id") ?></th>
					<th><?php etr("Supplier name") ?></th>
					<th class="supplier-action-column"><span class="visually-hidden"><?php etr("Action") ?></span></th>
				</tr></thead>
				<tbody>
				<?php
				$rs = query($selectSQL);
				$supplierCount = 0;
				while ($row = fetch_object($rs)) {
					$href = "supplier.php?supplierid=$row->supplierid";
					if ($mode == 'createpayable' || $mode == 'payable')
						$href = "payable.php?supplierid=$row->supplierid";
					else if ($mode == 'payment')
						$href = "payment.php?supplierid=$row->supplierid";
					else if ($mode == 'createorder')
						$href = "purchaseorder.php?supplierid=$row->supplierid&action=create";

					echo "<tr>";
					if (!$selectionMode) {
						echo "<td class='supplier-delete'>";
						deleteIcon("suppliers.php?del_supplierid=$row->supplierid");
						echo "</td>";
					}
					echo "<td><span class='supplier-id'>#" . htmlspecialchars($row->supplierid) . "</span></td>";
					echo "<td><a class='supplier-name' href='$href'><span class='supplier-avatar' aria-hidden='true'>" . strtoupper(htmlspecialchars(substr($row->name, 0, 1))) . "</span><span>" . htmlspecialchars($row->name) . "</span></a></td>";
					echo "<td class='supplier-action-column'><a class='supplier-select-action' href='$href'>" . ($selectionMode ? tr("Select") : tr("Open")) . " <span aria-hidden='true'>&#8594;</span></a></td>";
					echo "</tr>";
					$supplierCount++;
				}
				if ($supplierCount == 0) {
					$colspan = $selectionMode ? 3 : 4;
					echo "<tr><td colspan='$colspan'><div class='suppliers-empty'><span aria-hidden='true'>&#9675;</span><strong>" . tr("No suppliers found") . "</strong><small>" . tr("Try another supplier name or add a new supplier.") . "</small></div></td></tr>";
				}
				?>
				</tbody>
			</table>
		</div>
		<div class="suppliers-results-footer">
			<span><?php echo $supplierCount ?> <?php etr("suppliers") ?></span>
			<?php newButton("supplier.php?mode=$mode") ?>
		</div>
	</section>
</main>
<?php bottom() ?>
</body>
