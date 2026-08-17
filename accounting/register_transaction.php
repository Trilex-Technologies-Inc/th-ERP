<?php
	include('include.php');

	function confirm($transactionid)
	{
		$balance = findValue("
		select sum(amount) from tmp_transaction_part where transactionid=$transactionid");
		$valid = ($balance == 0) ? 1 : 0;
		if (!$valid) {
			return "ERROR:" . tr("Transaction doesn't balance") . "!";
		}
		$tmpTransid = $transactionid;
		$transactionid = findValue("
		select max(transactionid) from transaction");
		$transactionid++;
		sql("
		insert into transaction (transactionid, narrative, transtime, createdby, createdtime)
		select $transactionid, narrative, transtime, createdby, now()
		from tmp_transaction
		where transactionid=$tmpTransid");
		sql("
		insert into transaction_part (transactionid, dimid, accountid, amount)
		select $transactionid, dimid, accountid, amount
		from tmp_transaction_part
		where transactionid=$tmpTransid");
		sql("update tmp_transaction set locked=1 where transactionid=$tmpTransid");
		return $transactionid;
	}

	$transactionid = getParam('transactionid');
	$dimid = getParam('dimid', 1);
	$narrative = '';
	$transtime = time();
	$parts = null;
	$balance = 0;
	$new = true;
	$errmess = null;
	$locked = 0;

	if (isSave()) {
		begin();
		if (isNew()) {
			$narrative = getParam("narrative");
			$transtime = parseDate(getParam('transtime'));
			sql("insert into tmp_transaction (narrative, transtime, createdby)
			     values ('$narrative', from_unixtime($transtime), '" . getUser() . "')");
			$transactionid = insert_id();
		} else {
			$count = getParam('count');
			$i = 0;
			while ($i < $count) {
				$accountid = getParam("accountid_$i");
				$amount = getParam("amount_$i");
				sql("
				update tmp_transaction_part set amount=$amount
				where transactionid=$transactionid and accountid=$accountid
				and dimid=$dimid");
				if (getParam("del_$i"))
					sql("
					delete from tmp_transaction_part
					where transactionid=$transactionid and accountid=$accountid
					and dimid=$dimid");
				$i++;
			}
			$sum = 0;
			for ($i=0; $i <=3; $i++) {
				$accountid = getParam("accountid_new$i", getParam("accountid_new_$i"));
				if (!isEmpty($accountid)) {
					$amount = prepMoneyParam("amount_new$i");
					if (isEmpty($amount))
						$amount = (-1) * $sum;
					sql("insert into tmp_transaction_part (transactionid, dimid, accountid, amount)
					     values ($transactionid, $dimid, $accountid, $amount)");
					$sum += $amount;
				}
			}
		}
		commit();
	}

	if (array_key_exists("confirm", $_POST)) {
		$ret = tx("confirm", array($transactionid));
		$errormess = getError($transactionid);
		if ($errormess == null) {
			header("Location: transaction.php?transactionid=$ret");
		}
	}

	if (!isEmpty($transactionid)) {
	    $sql =
  		"select t.transactionid,
  		       unix_timestamp(transtime) as transtime,
		       narrative,
			   locked,
			   sum(tp.amount) as balance
		from tmp_transaction t
		left outer join tmp_transaction_part tp on tp.transactionid=t.transactionid
		where t.transactionid='$transactionid'
		group by t.transactionid, transtime, narrative, locked
		";
		$trans = find($sql);
		if ($trans != null) {
			$narrative = $trans->narrative;
			$transtime = $trans->transtime;
			$balance = $trans->balance;
			$locked = $trans->locked;
			$new = false;
		}
		$sql = "
		select a.accountid, a.name, amount
		from tmp_transaction_part tp
		join account a on a.accountid=tp.accountid and a.dimid=tp.dimid
		where transactionid=$transactionid and tp.dimid=$dimid
		";
		$parts = query($sql);
	}

	$accounts = rs2array(query("
	select a.accountid, a.accountid, name
	from account a
	join account_group ag on ag.accountid=a.accountid and groupid=".GROUPID_FAVORITES."
	where a.dimid=$dimid
	union
	select accountid, accountid, name
	from account
	where dimid=$dimid
	"));
	$dims = rs2array(query("select dimid, name from dimension"));
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
menubar("register_transaction.php");
$title = tr("Register");
title("<a href='transactions.php'>" . tr("Transactions") . "</a> > $title");

if ($errmess != null)
	echo "<center><font class=error>$errmess</font></center>";

?>

<main class="accounting-transaction-page">
<section class="accounting-transaction-card">
<div class="accounting-transaction-intro">
    <span class="accounting-section-kicker"><?php etr("General ledger") ?></span>
    <h1><?php etr("Register transaction") ?></h1>
    <p><?php etr("Enter the transaction details and balance the accounts before confirming.") ?></p>
</div>
<form action="register_transaction.php" method="POST" class="accounting-transaction-form">
<?php
hidden('transactionid', $transactionid);
hidden('dimid', $dimid);
?>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Narrative") ?>:</div>
<div class="col-12 col-md-auto">
<?php
if ($locked)
	echo $narrative;
else
	textbox('narrative', $narrative, 80);
?>
</div>
</div><div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Time") ?>:</div>
	<div class="col-12 col-md-auto">
	<?php
	if ($locked)
		formatDate($transtime);
	else
		datebox('transtime', formatDate($transtime));
	?>
	</div>
</div>
</div>
<?php
if ($parts != null) {
	if (count($dims) > 1) {
		echo "<div id=header>";
		echo "<ul id=primary>";
		for ($i=0; $i < count($dims); $i++) {
			$name = $dims[$i][1];
			$currDimid = $dims[$i][0];
			if ($dimid == $currDimid)
				echo "<li><span>$name</span></li>";
			else {
				$href = "register_transaction.php?";
				$href .= "transactionid=$transactionid&dimid=$currDimid";
				echo "<li><a href='$href'>$name</a></li>";
			}
		}
		echo "</div>";
		echo "<div id=main>";
		echo "<div id='contents'>";
	}

	echo "<div class='card border-0 shadow-sm'><div class='card-header bg-body-tertiary'><div class='row fw-semibold align-items-center'><div class='col-2'>" . tr("Delete") . "</div><div class='col-6'>" . tr("Account") . "</div><div class='col-4 text-end'>" . tr("Amount") . "</div></div></div><div class='list-group list-group-flush'>";
	$i = 0;
	while ($part = fetch($parts)) {
		echo "<input type=hidden name='accountid_$i' value='$part->accountid'/>";
		echo "<div class='list-group-item'><div class='row g-2 align-items-center'><div class='col-2'>";
		if (!$locked)
			checkbox("del_$i", false);
		echo "</div><div class='col-6'>$part->accountid - $part->name</div><div class='col-4 text-end'>";
		if ($locked)
			echo formatMoney($part->amount);
		else
			moneybox("amount_$i", $part->amount);
		echo "</div></div></div>";
		$i++;
	}
	if (!$locked) {
		echo "<input type=hidden name=count value='$i'/>";
		for ($i=0; $i <3; $i++) {
			echo "<div class='list-group-item'><div class='row g-2 align-items-center'><div class='col-2'></div><div class='col-2'>";
			numberbox("accountid_new_$i", '', 5);
			echo "</div><div class='col-4'>";
			comboBox("accountid_new$i", $accounts, null, true);
			echo "</div><div class='col-4 text-end'>";
			moneybox("amount_new$i", '');
			echo "</div></div></div>";
		}
	}
	echo "<div class='list-group-item bg-body-tertiary'><div class='row fw-bold'><div class='col-8 text-end'>" . tr("Balance") . ":</div><div class='col-4 text-end'>" . formatMoney($balance) . "</div></div></div>";
	echo "</div></div>";

if (count($dims) > 1) {
	echo "</div></div>";
}

}
?>
<br/>
<?php
if (!$locked) {
	$label = $new ? tr("Next")." > " : "Save";
	button($label, "save");
	if (!$new) {
		echo "&nbsp;&nbsp;";
		button("Confirm", "confirm");
	}
}
?>
<input type=hidden name=new value='<?php echo $new ?>'/>
</form>
</section>
</main>
<?php
bottom();
?>
</body>
