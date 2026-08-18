<?php
include('include.php');

$customerid = getParam('customerid');

$sql = "
	select
	    receiptid,
	    unix_timestamp(transtime) as receiptdate,
	    name as customername,
	    p.transactionid,
		amount
	from receipt p
	join customer c on c.customerid=p.customerid
	join transaction t on t.transactionid=p.transactionid
	where c.customerid like '$customerid%'";
$sql .= " order by receiptid desc";

$rs = query($sql);
$customers = rs2array(query("select customerid, name from customer"));
?>

<head>
	<title>thERP - <?php etr("Receipts") ?></title>
	<?php styleSheet() ?>
</head>

<body>

	<?php menubar("index.php") ?>
	<?php title(tr("Receipts")) ?>

	<form action="receipts.php" method="GET">
		<div class="border p-3 mb-4">
			<div class="row g-3 align-items-end">
				<div class="col-md-6">
					<label class="form-label"><?php etr("Customer") ?></label>
					<?php comboBox('customerid', $customers, $customerid, true) ?>
				</div>
				<div class="col-auto">
					<input type="submit" name="search" value="<?php etr("Search") ?>" class="btn btn-primary" />
				</div>
			</div>
		</div>
	</form>
	&nbsp;

	<form action="receipts.php" method=POST>
		<div class="table-responsive">
			<table class="table table-sm table-striped table-hover align-middle w-100">
				<thead>
					<tr>
						<th><?php etr("Id") ?></th>
						<th><?php etr("Customer") ?></th>
						<th><?php etr("Date") ?></th>
						<th class="text-end"><?php etr("Amount") ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$class = "odd";
					while ($row = fetch_object($rs)) {
						echo "<tr class='$class'>";
						echo "<td><a href='receipt.php?receiptid=$row->receiptid'>$row->receiptid</a></td>";
						echo "<td>$row->customername</td>";
						echo "<td>" . date(DATE_PATTERN, $row->receiptdate) . "</td>";
						echo "<td class='text-end'>" . formatMoney($row->amount) . "</td>";
						echo "</tr>";
						$class = ($class == "odd" ? "even" : "odd");
					}
					?>
				</tbody>
			</table>
		</div>
		<div class="d-flex gap-2">
			<td><?php newButton("customers.php?mode=receipt") ?></td>
		</div>
	</form>
	<?php bottom() ?>
</body>
