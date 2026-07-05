<?php
	include('include.php');

	if (isSave()) {
		begin();
		$accountid = getParam('accountid');
		$amount = prepMoneyParam('amount');
		$vat = prepMoneyParam('vat');
		$user = getUser();
		$narrative = getParam("narrative");
		$transtime = parseDate(getParam('transtime'));
		$cash_accountid = findValue("
		select default_cash from accountconf");
		$vat_accountid = findValue("
		select vat_recoverable from accountconf");
		sql("
		insert into transaction (narrative, transtime, createdby, valid, createdtime)
		values ('$narrative', from_unixtime($transtime), '$user', 1, now())");
		$transid = insert_id();
		$cash_amount = (-1) * $amount;
		sql("
		insert into transaction_part (transactionid, dimid, accountid, amount)
		values ($transid, 1, $cash_accountid, $cash_amount)");
		sql("
		insert into transaction_part (transactionid, dimid, accountid, amount)
		values ($transid, 1, $vat_accountid, $vat)");
		$expense_amount = $amount - $vat;
		sql("
		insert into transaction_part (transactionid, dimid, accountid, amount)
		values ($transid, 1, $accountid, $expense_amount)");
		commit();
		header("Location: transactions.php");
	}

	$transtime = time();
	$accounts = rs2array(query("
	select a.accountid, a.accountid, name
	from account a
	join account_group ag on ag.accountid=a.accountid and groupid=".GROUPID_EXPENSES."
	where a.dimid=1
	"));

?>
<head>
<title>thERP - <?php etr("Register transaction") ?></title>
<?php
styleSheet();
styleSheet('tabs');
include_datebox();
?>
</head>

<body>
<?php
menubar("index.php");
$title = tr("Register");
title("<a href='transactions.php'>" . tr("Transactions") . "</a> > $title");

if ($errmess != null)
	echo "<center><font class=error>$errmess</font></center>";

?>

<form action="expense_trans.php" method="POST">
<?php
hidden('transactionid', $transactionid);
hidden('dimid', $dimid);
?>
<table>
<tr><td class=label><?php etr("Narrative") ?>:</td>
<td>
<?php textbox('narrative', $narrative); ?>
</td>
<tr>
	<td class=label><?php etr("Time") ?>:</td>
	<td>
	<?php datebox('transtime', formatDate($transtime));	?>
	</td>
</tr>
<tr>
	<td class=label><?php etr("Excpense account") ?></td>
	<td><?php combobox('accountid', $accounts, null, false); ?></td>
</tr>
<tr>
	<td class=label><?php etr("Amount") ?></td>
	<td><?php moneybox('amount', ''); ?></td>
</tr>
<tr>
	<td class=label><?php etr("VAT") ?></td>
	<td><?php moneybox('vat', ''); ?></td>
</tr>

</table>
<br/>
<?php saveButton(); ?>
</form>
<?php
bottom();
?>
</body>
