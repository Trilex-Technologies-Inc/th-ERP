<?php
	include('include.php');

    $supplierid = getParam('supplierid');
	
	$sql = "
	select
	    paymentid,
	    unix_timestamp(transtime) as paymentdate,
	    name as suppliername,
	    p.transactionid,
		amount
	from payment p
	join supplier c on c.supplierid=p.supplierid
	join transaction t on t.transactionid=p.transactionid
	where c.supplierid like '$supplierid%'";
	$sql .= " order by paymentid desc";

    $rs = query($sql);
	$suppliers = rs2array(query("select supplierid, name from supplier"));
?>

<head>
<title>thERP - <?php etr("Payments") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("purchase.php") ?>
<?php title(tr("Payments")) ?>

<form action="payments.php" method="GET">
<div class="border">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Supplier") ?>:</div><div class="col-12 col-md-auto"><?php comboBox('supplierid', $suppliers, $supplierid, true) ?></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><input type="submit" name="search" value="<?php etr("Search") ?>" /></div></div>

</div>
</div>
</form>
&nbsp;

<form action="payments.php" method=POST>
<div class="card border-0 shadow-sm overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<th><?php etr("Id") ?></th>
<th><?php etr("Supplier") ?></th>
<th><?php etr("Date") ?></th>
<th><?php etr("Amount") ?></th>
<?php
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
        echo "<td><a href='payment.php?paymentid=$row->paymentid'>$row->paymentid</a></td>";
        echo "<td>$row->suppliername</td>";
        echo "<td>" . date(DATE_PATTERN, $row->paymentdate) . "</td>";
		echo "<td>" . formatMoney($row->amount) . "</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table></div></div>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php newButton("suppliers.php?mode=payment") ?></div>
</div>
</div>
</form>
</body>
