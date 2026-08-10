<?php
	include('include.php');

    $supplierid = getParam('supplierid');
	$mode = getParam('mode');

	$del_orderid = getParam('del_orderid');
	if (!isEmpty($del_orderid)) {
		sql("delete from purchaseorder_item where orderid=$del_orderid");
		sql("delete from purchaseorder where orderid=$del_orderid");
	}
	
	$sql = "
	select so.orderid,
	    unix_timestamp(orderdate) as orderdate,
	    name as suppliername
	from purchaseorder so
	join supplier c on c.supplierid=so.supplierid
	where so.supplierid like '$supplierid%'
	group by orderid	
	order by orderid desc
	";

    $rs = query($sql);
	$suppliers = rs2array(query("select supplierid, name from supplier"));
?>

<head>
<title>thERP - <?php etr("Purchase orders") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('purchase.php') ?>
<?php 
$title = "Purchase orders";
if ($mode == 'select')
	$title = "Select purchase order";
title(tr($title)) 
?>

<form action="purchaseorders.php" method="GET">
<div class="border">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Supplier") ?>:</div><div class="col-12 col-md-auto"><?php comboBox('supplierid', $suppliers, $supplierid, true) ?></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><input type="submit" name="search" value="<?php etr("Search") ?>" /></div></div>

</div>
</div>
</form>
&nbsp;

<form action="purchase.php" method=POST>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Id") ?></th>
<th><?php etr("Supplier") ?></th>
<th><?php etr("Order date") ?></th>
<?php
    $class = "odd";
    $i = 0;
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		deleteColumn("purchaseorders.php?del_orderid=$row->orderid");
        echo "<td><a href='purchaseorder.php?orderid=$row->orderid'>$row->orderid</a></td>";
        echo "<td>$row->suppliername</td>";
        echo "<td>" . date(DATE_PATTERN, $row->orderdate) . "</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
    }
?>
</table>
<br/>
<?php newButton("suppliers.php?mode=createorder") ?>
&nbsp;
<?php saveButton() ?>
</form>
<?php bottom() ?>	
</body>
