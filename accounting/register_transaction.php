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
		$errmess = getError($transactionid);
		if ($errmess == null) {
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
?>
</head>

<body>
<?php
menubar("register_transaction.php");
$title = tr("Register");
title("<a href='transactions.php'>" . tr("Transactions") . "</a> > $title");

?>

<main class="accounting-transaction-page">
<section class="card border-0 shadow-sm mb-4">
	<div class="card-body p-4 p-lg-5 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
		<div class="d-flex align-items-center gap-3">
			<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary fs-4 flex-shrink-0" aria-hidden="true">⇄</span>
			<div><span class="accounting-section-kicker"><?php etr("General ledger") ?></span><h1 class="h3 fw-bold mt-1 mb-1"><?php etr("Register transaction") ?></h1><p class="text-secondary mb-0"><?php etr("Enter the transaction details and balance the accounts before confirming.") ?></p></div>
		</div>
		<?php if (!$new) { ?><span class="badge rounded-pill <?php echo $locked ? 'text-bg-secondary' : ($balance == 0 ? 'text-bg-success' : 'text-bg-warning') ?> px-3 py-2"><?php echo $locked ? tr("Confirmed") : ($balance == 0 ? tr("Balanced") : tr("Draft")) ?> &middot; #<?php echo htmlspecialchars($transactionid) ?></span><?php } ?>
	</div>
</section>

<?php if ($errmess != null) { ?><div class="alert alert-danger" role="alert"><strong><?php etr("Transaction could not be confirmed") ?>:</strong> <?php echo htmlspecialchars($errmess) ?></div><?php } ?>

<form action="register_transaction.php" method="POST" class="accounting-transaction-form">
<?php
hidden('transactionid', $transactionid);
hidden('dimid', $dimid);
?>
<section class="card border-0 shadow-sm mb-4 overflow-hidden">
	<div class="card-header bg-white px-4 py-3"><span class="text-secondary small text-uppercase fw-bold"><?php etr("Step 1") ?></span><h2 class="h5 fw-bold mb-0 mt-1"><?php etr("Transaction details") ?></h2></div>
	<div class="card-body p-4"><div class="row g-4">
		<div class="col-12 col-md-8"><label class="form-label fw-semibold" for="narrative"><?php etr("Narrative") ?></label><?php if ($locked) echo "<div class='form-control bg-body-tertiary'>" . htmlspecialchars($narrative) . "</div>"; else textbox('narrative', $narrative, 80); ?><div class="form-text"><?php etr("Describe the purpose of this journal entry") ?></div></div>
		<div class="col-12 col-md-4"><label class="form-label fw-semibold" for="transtime"><?php etr("Transaction date") ?></label><?php if ($locked) echo "<div class='form-control bg-body-tertiary'>" . formatDate($transtime) . "</div>"; else datebox('transtime', formatDate($transtime)); ?></div>
	</div></div>
</section>
<?php
if ($parts != null) {
	if (count($dims) > 1) {
		echo "<nav class='mb-3' aria-label='" . tr("Dimensions") . "'><ul class='nav nav-tabs'>";
		for ($i=0; $i < count($dims); $i++) {
			$name = htmlspecialchars($dims[$i][1]);
			$currDimid = $dims[$i][0];
			if ($dimid == $currDimid)
				echo "<li class='nav-item'><span class='nav-link active' aria-current='page'>$name</span></li>";
			else {
				$href = "register_transaction.php?";
				$href .= "transactionid=$transactionid&dimid=$currDimid";
				echo "<li class='nav-item'><a class='nav-link' href='" . htmlspecialchars($href) . "'>$name</a></li>";
			}
		}
		echo "</ul></nav>";
	}

	$dimensionName = '';
	foreach ($dims as $dimension) {
		if ($dimension[0] == $dimid) {
			$dimensionName = $dimension[1];
			break;
		}
	}
	echo "<section class='card border-0 shadow-sm overflow-hidden'><div class='card-header bg-white d-flex justify-content-between align-items-center px-4 py-3'><div><span class='text-secondary small text-uppercase fw-bold'>" . tr("Step 2") . "</span><h2 class='h5 fw-bold mb-0 mt-1'>" . tr("Posting lines") . "</h2></div><span class='badge text-bg-light border'>" . htmlspecialchars($dimensionName) . "</span></div>";
	echo "<div class='table-responsive'><table class='table table-hover align-middle mb-0'><thead class='table-light'><tr><th class='text-center' style='width:85px'>" . tr("Delete") . "</th><th style='width:150px'>" . tr("Account ID") . "</th><th>" . tr("Account") . "</th><th class='text-end' style='width:210px'>" . tr("Amount") . "</th></tr></thead><tbody>";
	$i = 0;
	while ($part = fetch($parts)) {
		echo "<tr><td class='text-center'><input type='hidden' name='accountid_$i' value='" . htmlspecialchars($part->accountid) . "'/>";
		if (!$locked)
			checkbox("del_$i", false);
		echo "</td><td class='font-monospace text-secondary'>" . htmlspecialchars($part->accountid) . "</td><td class='fw-semibold'>" . htmlspecialchars($part->name) . "</td><td class='text-end'>";
		if ($locked)
			echo formatMoney($part->amount);
		else
			moneybox("amount_$i", $part->amount);
		echo "</td></tr>";
		$i++;
	}
	if (!$locked) {
		echo "<input type='hidden' name='count' value='$i'/>";
		for ($i=0; $i <3; $i++) {
			echo "<tr class='table-light'><td class='text-center text-primary fw-bold'>+</td><td>";
			numberbox("accountid_new_$i", '', 5);
			echo "</td><td>";
			comboBox("accountid_new$i", $accounts, null, true);
			echo "</td><td class='text-end'>";
			moneybox("amount_new$i", '');
			echo "</td></tr>";
		}
	}
	echo "</tbody><tfoot class='table-light'><tr><td colspan='3' class='fw-bold text-end'>" . tr("Balance") . "</td><td class='text-end fw-bold " . ($balance == 0 ? "text-success" : "text-danger") . "'>" . formatMoney($balance) . "</td></tr></tfoot></table></div>";
	echo "<div class='card-footer bg-white px-4 py-3'><span class='small " . ($balance == 0 ? "text-success" : "text-danger") . "'>" . ($balance == 0 ? tr("The transaction is balanced and ready to confirm") : tr("Debits and credits must total zero before confirmation")) . ".</span></div></section>";

}
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4">
	<a class="btn btn-outline-secondary" href="transactions.php">&#8592; <?php etr("Back to transactions") ?></a>
	<?php if (!$locked) { ?><div class="d-flex gap-2"><button class="btn btn-outline-primary" type="submit" name="save" value="1"><?php echo $new ? tr("Continue") . ' &#8594;' : tr("Save draft") ?></button><?php if (!$new) { ?><button class="btn btn-primary" type="submit" name="confirm" value="1" <?php echo $balance == 0 ? '' : 'disabled' ?>><?php etr("Confirm transaction") ?></button><?php } ?></div><?php } ?>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
</main>
<?php
bottom();
?>
</body>
