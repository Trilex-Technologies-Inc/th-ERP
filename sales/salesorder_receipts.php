<?php
include('include.php');
include('salesorder.inc.php');

$orderid = getParam('orderid');

$sql =
	"select p.receiptid,
		   unix_timestamp(transtime) as receiptdate,
		   p.transactionid,
		   pa.amount
	from receipt_allocation pa
	join receipt p on p.receiptid=pa.receiptid
	join transaction t on t.transactionid=p.transactionid
	where orderid=$orderid
	";
$receipts = query($sql);
?>

<head>
	<title>thERP - <?php etr("receipts") ?></title>
	<?php styleSheet() ?>
</head>

<body>
	<?php include("menubar.php") ?>
	<?php
	title("<a href='sales.php'>" . tr("Sales orders") . "</a> > <a href='salesorder.php?orderid=$orderid'>$orderid");
	?>

	<div class="mb-4">
		<div class="row g-2">
			<div class="col-auto"><strong><?php etr("Sales order") ?>:</strong></div>
			<div class="col-auto"><?php echo $orderid ?></div>
		</div>
	</div>
	<div class="table-responsive">
		<table class="table table-sm table-striped table-hover align-middle w-100">
			<thead>
				<tr>
					<th><?php etr("Receipt Id") ?></th>
					<th><?php etr("Date") ?></th>
					<th class="text-end"><?php etr("Amount") ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$class = 'odd';
				while ($row = fetch($receipts)) {
					echo "<tr class='$class'>";
					echo "<td><a href='receipt.php?receiptid=$row->receiptid'>$row->receiptid</a></td>";
					echo "<td>" . formatDate($row->receiptdate) . "</td>";
					echo "<td class='text-end'>" . formatMoney($row->amount) . "</td>";
					echo "</tr>";
					$class = ($class == "odd" ? "even" : "odd");
				}

				?>
			</tbody>
		</table>
	</div>
</body>
