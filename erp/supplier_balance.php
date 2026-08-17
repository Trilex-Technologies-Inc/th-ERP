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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto">Name:</div><div class="col-12 col-md-auto"><input type="text" name="name" value="<?php echo $name ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><input type="submit" name="search" value="Search" /></div></div>

</div>
</div>
</form>
&nbsp;

<div class="card border-0 shadow-sm overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
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
</table></div></div>
<?php bottom() ?>
</body>
