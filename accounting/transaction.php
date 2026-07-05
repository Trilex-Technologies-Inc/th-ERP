<?php
	include('include.php');

	$transactionid = getParam('transactionid');
	$dimid = getParam("dimid", 1);
	$salesorderid = getParam('salesorderid');

	$cancel_transid = null;
	if (array_key_exists('cancel', $_POST)) {
		$cancel_transid = tx('cancel_transaction' , array($transactionid));
	}

	if (!isEmpty($transactionid)) {
	    $sql =
  		"select transactionid,
  		       unix_timestamp(transtime) as transtime,
		       narrative,
			   cancel_transid,
			   createdby,
			   valid
		from transaction
		where transactionid='$transactionid'
		";
		$trans = find($sql);
		if ($trans != null) {
			$cancel_transid = $trans->cancel_transid;
		}
		$sql = "
		select a.accountid, a.name, amount
		from transaction_part tp
		join account a on a.accountid=tp.accountid and a.dimid=tp.dimid
		where transactionid=$transactionid and tp.dimid=$dimid
		";
		$parts = query($sql);
	}
	$dims = rs2array(query("select dimid, name from dimension"));

?>

<head>
<title>thERP - <?php etr("Transaction") ?></title>
<?php 
styleSheet();
styleSheet("tabs");
?>
</head>

<body>
<?php menubar("transactions.php") ?>
<?php
if (!isEmpty($salesorderid)) {
	$no = findValue("select no from salesorder where orderid=$salesorderid");
	title(tr("Salesorders") . " > <a href='../sales/salesorder.php?orderid=$salesorderid'>$no</a> > " . tr("Transaction"));
} else
	title("<a href='transactions.php'>" .tr("Transactions") . "</a> > $transactionid");
?>

<form action="transaction.php" method="POST">
<input type=hidden name=transactionid value='<?php echo $transactionid ?>'/>
<table>
<tr><td class=label><?php etr("Id") ?>:</td><td><?php echo $trans->transactionid ?></td>
<tr><td class=label><?php etr("Narrative") ?>:</td><td><?php echo $trans->narrative ?></td>
<tr><td class=label><?php etr("Time") ?>:</td><td><?php echo formatDate($trans->transtime) . ' ' . date('H:i', $trans->transtime) ?></td></tr>
<tr><td class=label><?php etr("Created by") ?>:</td><td><?php echo $trans->createdby ?></td></tr>
<tr>
<td colspan=2>
<?php
if (!$trans->valid)
	echo tr("This transaction is invalid, because it doesn't balance.");
?>
</td>
</tr>
<?php
	if ($cancel_transid != null) {
		echo "<tr>";
		echo "<td colspan=2>";
		echo tr("This transaction is cancelled") . "  <a href='transaction.php?transactionid=$cancel_transid'>" . tr("Show transaction") . "</a>";
		echo "</td>";
		echo "</tr>";
	}
?>
</table>
<?php
if (count($dims) > 1) {
	echo "<div id=header>";
	echo "<ul id=primary>";
	for ($i=0; $i < count($dims); $i++) {
		$name = $dims[$i][1];
		$currDimid = $dims[$i][0];
		if ($dimid == $currDimid)
			echo "<li><span>$name</span></li>";
		else {
			$href = "transaction.php?";
			$href .= "transactionid=$transactionid&dimid=$currDimid";
			echo "<li><a href='$href'>$name</a></li>";
		}		
	}
	echo "</div>";
	echo "<div id=main>";
	echo "<div id='contents'>";
}	

?>
<table>
<th><?php etr("Account") ?></th>
<th><?php etr("Amount") ?></th>
<?php
$balance = 0;
$class = 'odd';
while ($part = fetch($parts)) {
	echo "<tr class='$class'>";
	echo "<td>$part->accountid - $part->name</td>";
	echo "<td align=right>";
	printf('%9.2f', $part->amount);
	echo "</td>";
	echo "</tr>\n";
	$balance += $part->amount;
    $class = ($class == "odd" ? "even" : "odd");
}
?>
</table>
<?php
if ($balance != 0) {
	echo "<p class=error>" . tr("ERROR - Transaction doesn't balance") . "</p>";
}

if (count($dims) > 1) {
	echo "</div></div>";
}	

?>
<br/>
<?php
if ($cancel_transid == null)
	button("Cancel transaction", "cancel");
?>
</form>
<?php bottom() ?>
</body>
