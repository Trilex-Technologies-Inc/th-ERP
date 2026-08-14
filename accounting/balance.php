<?php
include('include.php');

$type = getParam('type', TYPE_MONTHS);
if ($type == TYPE_MONTHS)
	$start = getMonthStepperDate();
else
	$start = getYearStepperDate();
$end = addTime($start, $type);

$name = getParam('name');

function getBalance($accountid, $date)
{
	$balance = findValue("
	select sum(amount)
	from transaction_part tp
	join transaction t on t.transactionid=tp.transactionid
	where transtime < from_unixtime($date)
	and accountid=$accountid and dimid=1");
	return $balance;
}

function showGroup($groupid, $date, $endtime, $assets = false)
{
	$type = getParam('type', TYPE_MONTHS);
	$year = date("y", $date);
	$month = date("m", $date);
	$label = findValue("select description from accountgroup where groupid=$groupid");
	echo "<section class='card border-0 shadow-sm mb-4'><div class='card-header bg-body-tertiary'><h2 class='h5 mb-0'>" . $label . "</h2></div><div class='list-group list-group-flush'>";

	$selectSQL = "
	select
		a.accountid,
		name,
		sum(amount) as balance,
		(select sum(amount)
		 from transaction_part tps
		 join transaction ts on ts.transactionid=tps.transactionid
		 where transtime < from_unixtime($date)
		 and tps.accountid=a.accountid and tps.dimid=1) as startbalance,
		(select sum(amount)
		 from transaction_part tpe
		 join transaction te on te.transactionid=tpe.transactionid
		 where transtime < from_unixtime($endtime)
		 and tpe.accountid=a.accountid and tpe.dimid=1) as endbalance
	from account a
	join account_group ag on ag.accountid=a.accountid and ag.dimid=a.dimid
	and ag.groupid=$groupid ";
	if ($assets)
		$selectSQL .= " left outer ";
	$selectSQL .= "
	join
	(
	select accountid, amount
	from transaction_part tp
	join transaction t on t.transactionid=tp.transactionid
	where t.transtime between from_unixtime($date) and from_unixtime($endtime)
	and valid = 1 and tp.dimid=1
	) tp2 on tp2.accountid=a.accountid
	where a.dimid=1
	group by a.accountid, name
	";
	$sum = 0;
	$startSum = 0;
	$endSum = 0;
    $rs = query($selectSQL);
    while ($row = fetch_object($rs)) {
        echo "<div class='list-group-item'><div class='row align-items-center'><div class='col-2'>$row->accountid</div>";
        $href = "account_balance.php?accountid=$row->accountid&year=$year";
        if ($type == TYPE_MONTHS)
        	$href .= "&month=$month";
        echo "<div class='col-4'><a href='$href'>$row->name</a></div>";
		echo "<div class='col-2 text-end'>";
		if ($assets) {
			echo formatMoney($row->startbalance);
		}
		echo "</div>";
        echo "<div class='col-2 text-end'>" . formatMoney($row->balance) . "</div>";
		echo "<div class='col-2 text-end'>";
		if ($assets) {
			echo formatMoney($row->endbalance);
		}
		echo "</div></div></div>";
        $sum += $row->balance;
        $startSum += $row->startbalance;
        $endSum += $row->endbalance;
    }
	echo "<div class='list-group-item bg-body-tertiary'><div class='row fw-bold'><div class='col-6'>" . tr("Total") . "</div><div class='col-2 text-end'>";
	if ($assets)
		echo formatMoney($startSum);
	echo "</div>";
	echo "<div class='col-2 text-end'>" . formatMoney($sum) . "</div>";
	echo "<div class='col-2 text-end'>";
	if ($assets)
		echo formatMoney($endSum);
	echo "</div></div></div></div></section>";
	return $sum;
}

?>

<head>
<title>thERP - <?php etr("Balance") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("balance.php") ?>
<?php title(tr("Balance")) ?>

<br/>
<form name=searchform action="balance.php" method="GET">
<div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
<?php
$yearsChecked = '';
$monthsChecked = '';
if ($type == TYPE_YEARS) {
	yearStepper($start);
	$yearsChecked = 'checked';
} else {
	monthStepper($start);
	$monthsChecked = 'checked';
}
echo "<input type=radio name=type value='" . TYPE_YEARS . "' $yearsChecked onClick='document.searchform.submit()'>" . tr("Years") . "</input>";
echo "<input type=radio name=type value='" . TYPE_MONTHS . "' $monthsChecked onClick='document.searchform.submit()'>" . tr("Months") . "</input>";
?>
</div>
</form>

<div class="container-fluid px-0">
<div class="card border-0 shadow-sm mb-3"><div class="card-body py-2"><div class="row fw-semibold"><div class="col-2"><?php etr("Id") ?></div><div class="col-4"><?php etr("Name") ?></div><div class="col-2 text-end"><?php etr("Starting") ?></div><div class="col-2 text-end"><?php etr("Period") ?></div><div class="col-2 text-end"><?php etr("Final") ?></div></div></div></div>
<?php
$revenues = showGroup(GROUPID_REVENUES, $start, $end);
$expenses = showGroup(GROUPID_EXPENSES, $start, $end);
$profit = (-1) * ($expenses + $revenues);
echo "<div class='alert alert-primary d-flex justify-content-between fw-bold'><span>" . tr("Profit") . "</span><span>" . formatMoney($profit) . "</span></div>";
showGroup(GROUPID_ASSETS, $start, $end, true);
showGroup(GROUPID_LIABILITIES, $start, $end, true);
?>
</div>
<?php bottom() ?>
</body>
