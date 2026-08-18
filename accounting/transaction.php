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

<main class="container-fluid px-0">
<form action="transaction.php" method="POST">
<input type="hidden" name="transactionid" value="<?php echo htmlspecialchars($transactionid) ?>"/>

<section class="card border-0 shadow-sm mb-4 overflow-hidden">
	<div class="card-body p-4 p-lg-5">
		<div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
			<div class="d-flex align-items-start gap-3">
				<span class="dashboard-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary fs-4 flex-shrink-0" aria-hidden="true">⇄</span>
				<div><span class="text-secondary small text-uppercase fw-bold"><?php etr("General ledger") ?></span><h1 class="h3 fw-bold mt-1 mb-2"><?php echo htmlspecialchars($trans->narrative) ?></h1><p class="text-secondary mb-0"><?php etr("Transaction") ?> <span class="font-monospace">#<?php echo htmlspecialchars($trans->transactionid) ?></span></p></div>
			</div>
			<div><span class="badge rounded-pill <?php echo $cancel_transid != null ? 'text-bg-secondary' : ($trans->valid ? 'text-bg-success' : 'text-bg-danger') ?> px-3 py-2"><?php echo $cancel_transid != null ? tr("Cancelled") : ($trans->valid ? tr("Balanced") : tr("Invalid")) ?></span></div>
		</div>
	</div>
	<div class="card-footer bg-white px-4 px-lg-5 py-3">
		<div class="row g-3">
			<div class="col-12 col-sm-6 col-lg-3"><span class="d-block text-secondary small"><?php etr("Transaction ID") ?></span><strong class="font-monospace">#<?php echo htmlspecialchars($trans->transactionid) ?></strong></div>
			<div class="col-12 col-sm-6 col-lg-3"><span class="d-block text-secondary small"><?php etr("Date and time") ?></span><strong><?php echo formatDate($trans->transtime) . ' ' . date('H:i', $trans->transtime) ?></strong></div>
			<div class="col-12 col-sm-6 col-lg-3"><span class="d-block text-secondary small"><?php etr("Created by") ?></span><strong><?php echo htmlspecialchars($trans->createdby) ?></strong></div>
			<div class="col-12 col-sm-6 col-lg-3"><span class="d-block text-secondary small"><?php etr("Dimension") ?></span><strong><?php foreach ($dims as $dimension) { if ($dimension[0] == $dimid) { echo htmlspecialchars($dimension[1]); break; } } ?></strong></div>
		</div>
	</div>
</section>

<?php if (!$trans->valid) { ?><div class="alert alert-danger d-flex align-items-center gap-2" role="alert"><strong aria-hidden="true">!</strong><span><?php etr("This transaction is invalid because it does not balance.") ?></span></div><?php } ?>
<?php if ($cancel_transid != null) { ?><div class="alert alert-secondary d-flex justify-content-between align-items-center flex-wrap gap-2" role="status"><span><?php etr("This transaction has been cancelled.") ?></span><a class="alert-link" href="transaction.php?transactionid=<?php echo urlencode($cancel_transid) ?>"><?php etr("Show cancellation transaction") ?> &#8594;</a></div><?php } ?>
<?php
if (count($dims) > 1) {
	echo "<nav class='mb-3' aria-label='" . tr("Dimensions") . "'><ul class='nav nav-tabs'>";
	for ($i=0; $i < count($dims); $i++) {
		$name = htmlspecialchars($dims[$i][1]);
		$currDimid = $dims[$i][0];
		if ($dimid == $currDimid)
			echo "<li class='nav-item'><span class='nav-link active' aria-current='page'>$name</span></li>";
		else {
			$href = "transaction.php?";
			$href .= "transactionid=$transactionid&dimid=$currDimid";
			echo "<li class='nav-item'><a class='nav-link' href='" . htmlspecialchars($href) . "'>$name</a></li>";
		}		
	}
	echo "</ul></nav>";
}	

?>
<section class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white d-flex justify-content-between align-items-center px-4 py-3"><div><span class="text-secondary small text-uppercase fw-bold"><?php etr("Posting details") ?></span><h2 class="h5 fw-bold mb-0 mt-1"><?php etr("Transaction lines") ?></h2></div></div>
<div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead class="table-light"><tr><th style="width:130px"><?php etr("Account ID") ?></th><th><?php etr("Account") ?></th><th class="text-end" style="width:220px"><?php etr("Amount") ?></th></tr></thead><tbody>
<?php
$balance = 0;
$partCount = 0;
while ($part = fetch($parts)) {
	$partCount++;
	echo "<tr><td class='font-monospace text-secondary'>" . htmlspecialchars($part->accountid) . "</td><td class='fw-semibold'>" . htmlspecialchars($part->name) . "</td><td class='text-end text-nowrap fw-semibold'>" . formatMoney($part->amount) . "</td></tr>";
	$balance += $part->amount;
}
if ($partCount == 0)
	echo "<tr><td colspan='3' class='text-center text-secondary py-4'>" . tr("No transaction lines") . "</td></tr>";
?>
</tbody><tfoot class="table-light"><tr><td colspan="2" class="fw-bold"><?php etr("Balance") ?></td><td class="text-end fw-bold <?php echo $balance == 0 ? 'text-success' : 'text-danger' ?>"><?php echo formatMoney($balance) ?></td></tr></tfoot>
</table></div>
<div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-3 px-4 py-3"><span class="text-secondary small"><?php echo $partCount ?> <?php etr("transaction lines") ?></span><?php if ($cancel_transid == null) { ?><button class="btn btn-outline-danger" type="submit" name="cancel" value="1" onclick="return confirm('<?php echo htmlspecialchars(tr("Cancel this transaction?"), ENT_QUOTES) ?>')"><?php etr("Cancel transaction") ?></button><?php } ?></div>
</section>
</form>
</main>
<?php bottom() ?>
</body>
