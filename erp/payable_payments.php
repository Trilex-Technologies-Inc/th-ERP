<?php
	include('include.php');
	include('payable.inc.php');

	$payableid = getParam('payableid');

	$sql =
	"select p.paymentid,
		   unix_timestamp(transtime) as paymentdate,
		   p.transactionid,
		   pa.amount
	from payment_allocation pa
	join payment p on p.paymentid=pa.paymentid
	join transaction t on t.transactionid=p.transactionid
	where payableid=$payableid
	";
	$payments = query($sql);
?>

<head>
<title>thERP - <?php etr("Payments") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php include("menubar.php") ?>
<?php 
title("<a href='payables.php'>" . tr("Payables") . "</a> > <a href='payable.php?payableid=$payableid'>$payableid"); 
?>

<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><b><?php etr("Payable") ?>:</b></div><div class="col-12 col-md-auto"><?php echo $payableid ?></div>
</div></div>
<br/>
<div class="card border-0 shadow-sm overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<th><?php etr("Payment Id") ?></th>
<th><?php etr("Date") ?></th>
<th><?php etr("Amount") ?></th>
<?php
$class = 'odd'; 
while ($row = fetch($payments)) {
	echo "<tr class=$class>";
	echo "<td><a href='payment.php?paymentid=$row->paymentid'>$row->paymentid</a></td>";
	echo "<td>" . formatDate($row->paymentdate) . "</td>";
	echo "<td>" . formatMoney($row->amount) . "</td>";
	echo "</tr>";
	$class = ($class == "odd" ? "even" : "odd");
}

?>
</table></div></div>
</body>
