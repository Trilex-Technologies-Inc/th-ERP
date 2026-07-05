<?php
	include('include.php');
	include('purchaseorder.inc.php');

	$payableid = getParam('payableid');
	$supplierid = getParam('supplierid');
	$new = true;
	$description = '';
	$amount = '';
	$vat = '';
	$accountid = null;
	$transid = null;
	$cancel_transid = null;
	$payed = 0;
	if (isSave()) {
		$description = getParam('description');
		$amount = getParam('amount');
		$vat = getParam('vat');
		$accountid = getParam('accountid');
		$duedate = parseDate(getParam('duedate'));
		if (isNew()) {
			$payableid = tx("create_payable", array($supplierid, $description, $amount, $vat, $accountid, $duedate));
		} else {
			$updateSQL =
				"update payable set
					description='$description',
					duedate=from_unixtime($duedate)
				where payableid=$payableid";
			sql($updateSQL);
		}
	}
	if (array_key_exists('regAndPay', $_POST)) {
		$description = getParam('description');
		$amount = getParam('amount');
		$vat = getParam('vat');
		$accountid = getParam('accountid');
		$duedate = parseDate(getParam('duedate'));
		$payableid = tx("create_payable", array($supplierid, $description, $amount, $vat, $accountid, $duedate, true));
	}
	if (array_key_exists('pay', $_POST)) {
		tx("pay_payable", array($payableid));
	}
	if (array_key_exists('cancel', $_POST)) {
		tx("cancel_payable", array($payableid));
	}

	$paymentCount = 0;
	$payment_transid = null;
	$duedate = null;
	if (!isEmpty($supplierid))
		$duedate = addDay(time(), getCreditLength($supplierid));
	if (!isEmpty($payableid)) {
	    $selectSQL =
  		"select payableid,
		       description,
			   p.amount,
			   supplierid,
			   vat,
			   p.transactionid,
			   cancel_transid,
			   unix_timestamp(duedate) as duedate,
			   a2.accountid
		from payable p
		join transaction t on t.transactionid=p.transactionid
		left outer join
		(select a.accountid, tp.transactionid
		from account a
		join account_group ag on ag.accountid=a.accountid and groupid=" . GROUPID_PURCHASE_DEBIT . "
		join transaction_part tp on tp.accountid=a.accountid) as a2
		on a2.transactionid=t.transactionid
		where payableid=$payableid
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$description = $rec->description;
			$supplierid = $rec->supplierid;
			$amount = $rec->amount;
			$vat = $rec->vat;
			$duedate = $rec->duedate;
			$accountid = $rec->accountid;
			$transid = $rec->transactionid;
			$cancel_transid = $rec->cancel_transid;
			$new = false;
			$payed = findValue("select sum(amount) from payment_allocation where payableid=$payableid");
			$paymentCount = findValue("select count(*) from payment_allocation where payableid=$payableid");
			if ($paymentCount == 1) {
				$payment_transid = findValue("select transactionid
				                              from payment_allocation ra
											  join payment r on r.paymentid=ra.paymentid
											  where payableid=$payableid");
			}
		}
	}

	$suppliername = '';
	if (!isEmpty($supplierid))
		$suppliername = findValue("select name from supplier where supplierid=$supplierid");


	$accounts = rs2array(query("select a.accountid, concat(cast(a.accountid as char), ' - ', name)
	                            from account a
								join account_group g on g.accountid=a.accountid
								where groupid in (" . GROUPID_PURCHASE_DEBIT . ")"));

?>
<head>
<title>thERP - <?php etr("Payable") ?></title>
<?php
metatag();
styleSheet();
include_datebox();
?>
</head>
<body>
<?php menubar('purchase.php') ?>
<?php
title(tr("Payable"));
?>

<form action="payable.php" method="POST">
<input type=hidden name=supplierid value='<?php echo $supplierid ?>'/>
<table>
<tr><td class=label>Id:</td>
<td>
<?php
	if (!$new) {
		echo $payableid;
		echo "<input type='hidden' name='payableid' value='$payableid'/>";
	}
?>
</td>
<tr><td class=label><?php echo tr("Supplier") ?>:</td><td><?php echo $suppliername ?></td></tr>
<tr>
	<td class=label><?php echo tr("Description") ?>:</td>
	<td><?php textbox('description', $description, 60) ?></td>
</tr>
<tr>
	<td class=label><?php echo tr("Amount") ?>:</td>
	<td>
	<?php
		if ($new)
			moneybox('amount', $amount);
		else
			echo $amount;
	?>
	</td>
</tr>
<tr>
	<td class=label><?php echo tr("VAT") ?>:</td>
	<td>
	<?php
	if ($new)
		moneybox('vat', $vat);
	else
		echo $vat;
	?>
	</td>
</tr>
<tr>
	<td class=label><?php echo tr("Due date") ?>:</td>
	<td>
	<?php datebox('duedate', formatDate($duedate)) ?>
	</td>
</tr>
<tr>
	<td class=label><?php etr("Debit account") ?>:</td>
	<td>
	<?php
	if ($new)
		comboBox("accountid", $accounts, $accountid, false);
	else
		echo getDescription($accountid, $accounts);
	?>
	</td>
</tr>
<?php
	if (!$new) {
		echo "<tr>";
		echo "<td class=label>" . tr("Transaction") . ":</td>";
		echo "<td><a href='../accounting/transaction.php?transactionid=$transid'>Show transaction</a></td>";
		echo "</tr>";
	}
?>
<tr>
<td class=label><?php etr("Payment") ?>:</td>
<td>
<?php
	if ($payed == $amount && $payed > 0)
		etr("Fully paid");
	else
		echo formatMoney($payed) . " / " . formatMoney($amount + $vat);
	echo "&nbsp;&nbsp;";
	if ($payed > 0) {
		if ($paymentCount > 1)
			echo "<a href='payable_payments.php?payableid=$payableid'>" . tr("Show payments") . "</a>";
		else
			echo "<a href='../accounting/transaction.php?transactionid=$payment_transid'>" . tr("Show transaction") . "</a>";
	}
?>
</td>
</tr>
<?php
if ($cancel_transid != null) {
	echo "<tr>";
	echo "<td colspan=2>";
	echo tr("This payable is cancelled") . " <a href='transaction.php?transactionid=$cancel_transid'>" . tr("Show transaction") . "</a>";
	echo "</td>";
	echo "</tr>";
}
?>
</table>
<br/>
<?php
button('Register', 'save');
echo "&nbsp;";

if ($new) {
	button('Register and pay', 'regAndPay');
	echo "&nbsp;";
}

if (!$new && $amount + $vat > $payed && $cancel_transid == null) {
	button("Pay", "pay");
}
?>
&nbsp;
<?php
if ($cancel_transid == null && $amount > $payed)
	button("Cancel", "cancel")
?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
