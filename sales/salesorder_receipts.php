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
<LINK REL=StyleSheet HREF="therp.css" TYPE="text/css">
</head>

<body>
<?php include("menubar.php") ?>
<?php 
title("<a href='sales.php'>" . tr("Sales orders") . "</a> > <a href='salesorder.php?orderid=$orderid'>$orderid"); 
?>

<table>
<tr><td><b><?php etr("Sales order") ?>:</b></td><td><?php echo $orderid ?></td>
</table>
<br/>
<table>
<th><?php etr("Receipt Id") ?></th>
<th><?php etr("Date") ?></th>
<th><?php etr("Amount") ?></th>
<?php
$class = 'odd'; 
while ($row = fetch($receipts)) {
	echo "<tr class=$class>";
	echo "<td><a href='receipt.php?receiptid=$row->receiptid'>$row->receiptid</a></td>";
	echo "<td>" . formatDate($row->receiptdate) . "</td>";
	echo "<td>" . formatMoney($row->amount) . "</td>";
	echo "</tr>";
	$class = ($class == "odd" ? "even" : "odd");
}

?>
</table>
</body>
