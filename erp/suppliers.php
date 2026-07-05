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

<form action="suppliers.php" method="GET">
<input type=hidden name=mode value='<?php echo $mode ?>'/>
<div class="border">
<table>
<tr><td><?php etr("Supplier name") ?>:</td><td><input type="text" name="name" value="<?php echo $name ?>"/></td>
<tr><td><?php searchButton() ?></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="suppliers.php" method=POST>
<input type=hidden name=mode value='<?php echo $mode ?>'/>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Id") ?></th>
<th><?php etr("Supplier name") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		deleteColumn("suppliers.php?del_supplierid=$row->supplierid");
        echo "<td>$row->supplierid</td>";
		$href = "supplier.php?supplierid=$row->supplierid";
		if ($mode == 'createpayable')
			$href = "payable.php?supplierid=$row->supplierid";
		else if ($mode == 'payment')
			$href = "payment.php?supplierid=$row->supplierid";
		else if ($mode == 'payable')
			$href = "payable.php?supplierid=$row->supplierid";
		else if ($mode == 'createorder')
			$href = "purchaseorder.php?supplierid=$row->supplierid&action=create";
        echo "<td><a href='$href'>$row->name</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<table>
<tr>
<td><?php newButton("supplier.php?mode=$mode") ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
