<?php
include('include.php');
include('salesorder.inc.php');

$name = getParam('name');
$recur = getParam('recur');

$del_customerid = getParam("del_customerid");
if (!isEmpty($del_customerid)) {
	sql("delete from customer_phone where customerid=$del_customerid");
	sql("delete from customer where customerid=$del_customerid");
}

$selectSQL = "
	select
	    customerid,
	    c.name,
	    co.name as country
	from customer c
	left join country co on co.countrycode=c.countrycode
	where c.name like '$name%'";

$mode = getParam('mode');

?>

<head>
	<title>thERP - Customers</title>
	<?php styleSheet() ?>
</head>

<body>

	<?php menubar('customers.php') ?>
	<?php
	if ($mode == 'createorder')
		title(tr("Create order") . " > " . tr("Select customer"));
	else if ($mode == 'receipt')
		title(tr("Receipt") . " > " . tr("Select customer"));
	else
		title(tr("Customers"))
	?>

	<form action="customers.php" method="GET">
		<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode) ?>" />
		<div class="border p-3 mb-4">
			<div class="row g-3 align-items-end">
				<div class="col-md-6">
					<label class="form-label"><?php etr("Customer name") ?></label>
					<input type="text" name="name" value="<?php echo htmlspecialchars($name) ?>" class="form-control" />
				</div>
				<div class="col-md-auto">
					<?php searchButton() ?>
				</div>
			</div>
		</div>
	</form>

	<form action="customers.php" method="POST">
		<div class="table-responsive">
			<table class="table table-sm table-striped table-hover align-middle w-100">
				<thead>
					<tr>
						<th><?php etr("Delete") ?></th>
						<th><?php etr("Customer no") ?></th>
						<th><?php etr("Name") ?></th>
						<th><?php etr("Country") ?></th>
						<th class="text-end"><?php etr("Balance") ?></th>
						<th class="text-end"><?php etr("Over due") ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$rs = query($selectSQL);
					$class = "odd";
					while ($row = fetch_object($rs)) {
						$href = "customer.php?customerid=$row->customerid";
						if ($mode == 'createorder')
							$href = "salesorder.php?customerid=$row->customerid&action=create&recur=$recur";
						else if ($mode == 'receipt')
							$href = "receipt.php?customerid=$row->customerid";
						echo "<tr class='$class'>";
						deleteColumn("customers.php?del_customerid=$row->customerid");
						echo "<td>$row->customerid</td>";
						echo "<td><a href='$href'>" . htmlspecialchars($row->name) . "</a></td>";
						echo "<td>" . htmlspecialchars($row->country) . "</td>";
						$balanceHref = "sales.php?customerid=$row->customerid&unpaid=1";
						echo "<td class='text-end'><a href='$balanceHref'>" . formatMoney(getCustomerBalance($row->customerid)) . "</a></td>";
						echo "<td class='text-end'><a href='$balanceHref&overdue=1'>" . formatMoney(getCustomerBalance($row->customerid, true)) . "</a></td>";
						echo "</tr>";
						$class = ($class == "odd" ? "even" : "odd");
					}
					?>
				</tbody>
			</table>
		</div>
		<div class="mb-3">
			<?php button("New customer", "new", "customer.php?mode=$mode") ?>
		</div>
	</form>
	<?php bottom() ?>
</body>
