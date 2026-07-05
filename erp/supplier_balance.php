<?php
	include('include.php');
	include('purchaseorder.inc.php');

    $name = getParam('name');

	$selectSQL = "
	select
	    c.supplierid,
	    name
	from supplier c
	where name like '$name%'
	";
?>

<head>
<title>thERP - <?php etr("Supplier  balance") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php
menubar('purchase.php');
title(tr("Supplier balance"));
?>

<form action="supplier_balance.php" method="GET">
<div class="border">
<table>
<tr><td>Name:</td><td><input type="text" name="name" value="<?php echo $name ?>"/></td>
<tr><td><input type="submit" name="search" value="Search" /></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<table>
<th><?php etr("Id") ?></th>
<th><?php etr("Name") ?></th>
<th><?php etr("Balance") ?></th>
<th><?php etr("Over due") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
        echo "<td>$row->supplierid</td>";
        echo "<td><a href='supplier.php?supplierid=$row->supplierid'>$row->name</a></td>";
		$href = "payables.php?supplierid=$row->supplierid&unpaid=1";
		echo "<td align=right><a href='$href'>" . formatMoney(getSupplierBalance($row->supplierid)) . "</a></td>";
		echo "<td align=right><a href='$href&overdue=1'>" . formatMoney(getSupplierBalance($row->supplierid, true)) . "</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<?php bottom() ?>
</body>
