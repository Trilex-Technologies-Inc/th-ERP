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

<main class="accounting-transaction-page">
<section class="accounting-transaction-card accounting-expense-card">
<div class="accounting-transaction-intro">
    <span class="accounting-section-kicker"><?php etr("Quick actions") ?></span>
    <h1><?php etr("Expense transaction") ?></h1>
    <p><?php etr("Record an expense, including its VAT and payment amount.") ?></p>
</div>
<form action="expense_trans.php" method="POST" class="accounting-transaction-form">
<?php
hidden('transactionid', $transactionid);
hidden('dimid', $dimid);
?>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Narrative") ?>:</div>
<div class="col-12 col-md-auto">
<?php textbox('narrative', $narrative); ?>
</div>
</div><div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Time") ?>:</div>
	<div class="col-12 col-md-auto">
	<?php datebox('transtime', formatDate($transtime));	?>
	</div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Excpense account") ?></div>
	<div class="col-12 col-md-auto"><?php combobox('accountid', $accounts, null, false); ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Amount") ?></div>
	<div class="col-12 col-md-auto"><?php moneybox('amount', ''); ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("VAT") ?></div>
	<div class="col-12 col-md-auto"><?php moneybox('vat', ''); ?></div>
</div>

</div>
<br/>
<?php saveButton(); ?>
</form>
</section>
</main>
<?php
bottom();
?>
</body>
