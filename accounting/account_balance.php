<?php
	include('include.php');

	$accountid = getParam('accountid');
	$year = getParam('year');
	$month = getParam('month');
	if (isEmpty($month)) {
		$start = mktime(0,0,0, 1, 1, $year);
		$end = strtotime("next year", $start);
	} else {
		$start = mktime(0,0,0, $month, 1, $year);
		$end = strtotime("next month", $start);
	}


	$selectSQL =
	"select accountid,
		   name
	from account
	where accountid=$accountid
	";
	$account = find($selectSQL);

	$sql = "
	select t.transactionid, narrative, amount, unix_timestamp(transtime) as transtime
	from transaction_part tp
	join transaction t on t.transactionid=tp.transactionid
	where accountid=$accountid
	and valid = 1
	and transtime between from_unixtime($start) and from_unixtime($end)
	";
	$parts = query($sql);
?>

<head>
<title>thERP - <?php etr("Account balance") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php include("menubar.php") ?>
<?php title("Balance > $account->name") ?>

<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Accout") ?>:</div>
	<div class="col-12 col-md-auto"><?php echo "$accountid - $account->name" ?></div>
</div>
</div>
<br/>
<div class="card border-0 shadow-sm">
<div class="card-header bg-body-tertiary"><div class="row fw-semibold"><div class="col-6"><?php etr("Transaction") ?></div><div class="col-3 text-end"><?php etr("Amount") ?></div><div class="col-3"><?php etr("Date") ?></div></div></div>
<div class="list-group list-group-flush">
<?php
$sum = 0;
while ($part = fetch($parts)) {
	echo "<div class='list-group-item'><div class='row align-items-center'><div class='col-6'><a href='transaction.php?transactionid=$part->transactionid'>$part->transactionid</a> - $part->narrative</div>";
	echo "<div class='col-3 text-end'>";
	printf('%9.2f', $part->amount);
	echo "</div><div class='col-3'>" . formatDate($part->transtime) . "</div></div></div>";
	$sum += $part->amount;
}
?>
<div class="list-group-item bg-body-tertiary"><div class="row fw-bold"><div class="col-6"><?php etr("Total") ?></div><div class="col-3 text-end"><?php echo formatMoney($sum) ?></div></div></div>
</div></div>

</body>
