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
	    name
	from customer
	where name like '$name%'";

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
<input type=hidden name=mode value='<?php echo $mode ?>'/>
<div class="border">
<table>
<tr><td><?php etr("Customer name") ?>:</td><td><input type="text" name="name" value="<?php echo $name ?>"/></td>
<tr><td><?php searchButton() ?></td></tr>
</tr>
</table>
</div>
</form>

<form action="customers.php" method=POST>
<table width='100%'>
<th><?php etr("Delete") ?></th>
<th><?php etr("Customer no") ?></th>
<th width='50%'><?php etr("Name") ?></th>
<th><?php etr("Balance") ?></th>
<th><?php etr("Over due") ?></th>
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
        echo "<td><a href='$href'>$row->name</a></td>";
		$href = "sales.php?customerid=$row->customerid&unpaid=1";
		echo "<td align=right><a href='$href'>" . formatMoney(getCustomerBalance($row->customerid)) . "</a></td>";
		echo "<td align=right><a href='$href&overdue=1'>" . formatMoney(getCustomerBalance($row->customerid, true)) . "</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<table>
<tr>
<td><?php button("New customer", "new", "customer.php?mode=$mode") ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
