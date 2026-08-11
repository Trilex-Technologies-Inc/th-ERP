<?php
include('include.php');

$totalOrders = findValue("select count(*) from productionorder", 0);
$openOrders = findValue("select count(*) from productionorder where transactionid is null and cancelled != 1", 0);
$finishedOrders = findValue("select count(*) from productionorder where transactionid is not null and cancelled != 1", 0);
$monthOrders = findValue("select count(*) from productionorder where createdtime >= date_format(now(), '%Y-%m-01')", 0);
$recentOrders = query("
	select orderid, unix_timestamp(createdtime) as createdtime, transactionid, cancelled, createdby
	from productionorder
	order by orderid desc
	limit 5");
?>

<head>
<title>thERP - <?php etr("Manufacturing") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar('index.php') ?>
<?php title(tr("Manufacturing")) ?>

<main class="manufacturing-dashboard">
	<section class="manufacturing-hero">
		<div class="manufacturing-hero-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 20V9l5 3V9l5 3V5h4l2 15H4Zm3 0v-3m4 3v-3m4 3v-3"/></svg></div>
		<div class="manufacturing-hero-copy"><span><?php etr("Production workspace") ?></span><h1><?php etr("Manufacturing") ?></h1><p><?php etr("Create production orders, track work in progress, and review completed output.") ?></p></div>
		<a class="btn btn-primary btn-lg manufacturing-create" href="productionorder.php?action=create"><?php etr("New production order") ?> <span aria-hidden="true">&#8594;</span></a>
	</section>

	<section class="manufacturing-stats" aria-label="<?php etr("Production summary") ?>">
		<a href="productionorders.php" class="manufacturing-stat"><span><?php etr("Open orders") ?></span><strong><?php echo (int) $openOrders ?></strong><small><?php etr("Ready for production") ?></small></a>
		<a href="productionorders.php" class="manufacturing-stat"><span><?php etr("Finished") ?></span><strong><?php echo (int) $finishedOrders ?></strong><small><?php etr("Completed orders") ?></small></a>
		<a href="productionorders.php" class="manufacturing-stat"><span><?php etr("This month") ?></span><strong><?php echo (int) $monthOrders ?></strong><small><?php etr("Orders created") ?></small></a>
		<a href="productionorders.php" class="manufacturing-stat"><span><?php etr("All orders") ?></span><strong><?php echo (int) $totalOrders ?></strong><small><?php etr("Production history") ?></small></a>
	</section>

	<section class="manufacturing-recent card border-0 shadow-sm overflow-hidden">
		<div class="card-header bg-white manufacturing-card-header"><div><span><?php etr("Latest activity") ?></span><h2><?php etr("Recent production orders") ?></h2></div><a href="productionorders.php"><?php etr("View all") ?> &#8594;</a></div>
		<div class="erp-table-responsive"><table class="erp-data-table manufacturing-orders-table">
			<thead><tr><th><?php etr("Order") ?></th><th><?php etr("Created date") ?></th><th><?php etr("Created by") ?></th><th class="text-center"><?php etr("Status") ?></th><th class="text-end"><?php etr("Action") ?></th></tr></thead><tbody>
			<?php
			$count = 0;
			while ($row = fetch($recentOrders)) {
				$status = tr("Open"); $statusClass = "is-draft";
				if ($row->cancelled) { $status = tr("Cancelled"); $statusClass = "is-cancelled"; }
				else if (!isEmpty($row->transactionid)) { $status = tr("Finished"); $statusClass = "is-received"; }
				$id = htmlspecialchars($row->orderid);
				$href = "productionorder.php?orderid=" . urlencode($row->orderid);
				echo "<tr><td><span class='manufacturing-order-id'>#$id</span></td><td>" . date(DATE_PATTERN, $row->createdtime) . "</td><td>" . htmlspecialchars($row->createdby) . "</td><td class='text-center'><span class='stock-move-status $statusClass'>$status</span></td><td class='text-end'><a class='manufacturing-open' href='$href'>" . tr("Open") . " &#8594;</a></td></tr>";
				$count++;
			}
			if ($count == 0) echo "<tr><td colspan='5'><div class='manufacturing-empty'><span aria-hidden='true'>&#9881;</span><strong>" . tr("No production orders yet") . "</strong><small>" . tr("Create the first order to begin tracking production.") . "</small></div></td></tr>";
			?>
			</tbody></table></div>
		<div class="manufacturing-card-footer"><span><?php echo $count ?> <?php etr("recent orders") ?></span><a class="btn btn-primary btn-sm" href="productionorder.php?action=create"><?php etr("New production order") ?></a></div>
	</section>
</main>

<?php bottom() ?>
</body>
