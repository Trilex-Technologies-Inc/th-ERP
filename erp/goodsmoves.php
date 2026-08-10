<?php
	include('include.php');

    $locationid = getParam('locationid');
    $toid = getParam('toid');
	$mode = getParam('mode');

	$del_orderid = getParam('del_orderid');
	if (!isEmpty($del_orderid)) {
		sql("delete from movesorder_item where orderid=$del_orderid");
		sql("delete from movesorder where orderid=$del_orderid");
	}
	
	$sql = "
	select so.orderid,
	    unix_timestamp(so.orderdate) as orderdate,
		so.cancelled,
		so.sent,
		so.received,
	    c.name as locationname,
	    t.name as descname
	from movesorder so
	join location c on c.locationid=so.locationid
	join location t on t.locationid=so.toid
	where so.locationid like '$locationid%' and so.toid like '$toid%'
	group by orderid	
	order by orderid desc
	";

    $rs = query($sql);
	$locations = rs2array(query("select locationid, name from location"));
?>

<head>
<title>thERP - <?php etr("Stock move order") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('goodsmoves.php') ?>
<?php 
$title = "Stock move order";
if ($mode == 'select')
	$title = "Select Stock move order";
title(tr($title)) 
?>

<main class="stock-moves-page">
	<header class="stock-moves-intro">
		<div class="stock-moves-intro-icon" aria-hidden="true">
			<svg viewBox="0 0 24 24"><path d="M7 7h11l-3-3M17 17H6l3 3M18 7l-3 3M6 17l3-3"/></svg>
		</div>
		<div>
			<span class="stock-moves-eyebrow"><?php etr("Inventory movement") ?></span>
			<h1><?php etr("Stock move orders") ?></h1>
			<p><?php etr("Transfer products between locations and follow each order through completion.") ?></p>
		</div>
		<div class="stock-moves-create"><?php newButton("goodsmove.php?toid=&action=create") ?></div>
	</header>

	<form action="goodsmoves.php" method="GET" class="stock-moves-filter card border-0 shadow-sm">
		<div class="card-body">
			<div class="stock-moves-section-heading">
				<div><span><?php etr("Filters") ?></span><h2><?php etr("Find stock moves") ?></h2></div>
			</div>
			<div class="row g-3 align-items-end">
				<div class="col-12 col-md-5">
					<label class="form-label fw-semibold" for="locationid"><?php etr("From Location") ?></label>
					<div class="stock-moves-field"><?php comboBox('locationid', $locations, $locationid, true) ?></div>
				</div>
				<div class="col-12 col-md-5">
					<label class="form-label fw-semibold" for="toid"><?php etr("To Location") ?></label>
					<div class="stock-moves-field"><?php comboBox('toid', $locations, $toid, true) ?></div>
				</div>
				<div class="col-12 col-md-2 d-grid">
					<input type="submit" name="search" value="<?php etr("Search") ?>" />
				</div>
			</div>
			<?php if (!isEmpty($mode)) { ?><input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode) ?>" /><?php } ?>
		</div>
	</form>

	<section class="stock-moves-list card border-0 shadow-sm overflow-hidden">
		<div class="card-header bg-white stock-moves-list-header">
			<div><span><?php etr("Transfers") ?></span><h2><?php etr("Stock move orders") ?></h2></div>
		</div>
		<div class="erp-table-responsive">
			<table class="erp-data-table stock-moves-table">
				<thead><tr>
					<th class="stock-move-delete"><?php etr("Delete") ?></th>
					<th><?php etr("Id") ?></th>
					<th><?php etr("From Location") ?></th>
					<th><?php etr("To Location") ?></th>
					<th><?php etr("Order date") ?></th>
					<th class="text-center"><?php etr("Status") ?></th>
				</tr></thead>
				<tbody>
				<?php
				$i = 0;
				while ($row = fetch_object($rs)) {
					$status = tr("Draft");
					$statusClass = "is-draft";
					if ($row->cancelled == '1') {
						$status = tr("Cancelled");
						$statusClass = "is-cancelled";
					} else if ($row->received == '1') {
						$status = tr("Received");
						$statusClass = "is-received";
					} else if ($row->sent == '1') {
						$status = tr("Sent");
						$statusClass = "is-sent";
					}
					echo "<tr>";
					echo "<td class='stock-move-delete'>";
					deleteIcon("goodsmoves.php?del_orderid=$row->orderid");
					echo "</td>";
					echo "<td><a class='stock-move-id' href='goodsmove.php?orderid=$row->orderid'>#" . htmlspecialchars($row->orderid) . "</a></td>";
					echo "<td><span class='stock-location'><span class='stock-location-dot is-origin'></span>" . htmlspecialchars($row->locationname) . "</span></td>";
					echo "<td><span class='stock-location'><span class='stock-location-dot is-destination'></span>" . htmlspecialchars($row->descname) . "</span></td>";
					echo "<td class='text-nowrap'>" . date(DATE_PATTERN, $row->orderdate) . "</td>";
					echo "<td class='text-center'><span class='stock-move-status $statusClass'>$status</span></td>";
					echo "</tr>";
					$i++;
				}
				if ($i == 0) {
					echo "<tr><td colspan='6'><div class='stock-moves-empty'><span aria-hidden='true'>&#8644;</span><strong>" . tr("No stock move orders found") . "</strong><small>" . tr("Try changing the location filters or create a new order.") . "</small></div></td></tr>";
				}
				?>
				</tbody>
			</table>
		</div>
		<div class="stock-moves-list-footer">
			<span><?php echo $i ?> <?php etr("orders") ?></span>
			<?php newButton("goodsmove.php?toid=&action=create") ?>
		</div>
	</section>
</main>
<?php bottom() ?>	
</body>
