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
	echo "<section class='card border-0 shadow-sm mb-4 overflow-hidden'>";
	echo "<div class='card-header bg-white d-flex justify-content-between align-items-center px-4 py-3'><div><span class='text-secondary small text-uppercase fw-bold'>" . tr("Account group") . "</span><h2 class='h5 fw-bold mb-0 mt-1'>" . htmlspecialchars($label) . "</h2></div><span class='badge text-bg-light border'>" . ($assets ? tr("Balance sheet") : tr("Period activity")) . "</span></div>";
	echo "<div class='table-responsive'><table class='table table-hover align-middle mb-0'>";
	echo "<thead class='table-light'><tr><th style='width:110px'>" . tr("Id") . "</th><th>" . tr("Name") . "</th><th class='text-end' style='width:160px'>" . tr("Starting") . "</th><th class='text-end' style='width:160px'>" . tr("Period") . "</th><th class='text-end' style='width:160px'>" . tr("Final") . "</th></tr></thead><tbody>";

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
	$count = 0;
    $rs = query($selectSQL);
    while ($row = fetch_object($rs)) {
		$count++;
        echo "<tr><td class='font-monospace text-secondary'>" . htmlspecialchars($row->accountid) . "</td>";
        $href = "account_balance.php?accountid=$row->accountid&year=$year";
        if ($type == TYPE_MONTHS)
        	$href .= "&month=$month";
		echo "<td><a class='fw-semibold text-decoration-none' href='" . htmlspecialchars($href) . "'>" . htmlspecialchars($row->name) . "</a></td>";
		echo "<td class='text-end text-nowrap text-secondary'>";
		if ($assets) {
			echo formatMoney($row->startbalance);
		}
		echo "</td>";
        echo "<td class='text-end text-nowrap fw-semibold'>" . formatMoney($row->balance) . "</td>";
		echo "<td class='text-end text-nowrap text-secondary'>";
		if ($assets) {
			echo formatMoney($row->endbalance);
		}
		echo "</td></tr>";
        $sum += $row->balance;
        $startSum += $row->startbalance;
        $endSum += $row->endbalance;
    }
	if ($count == 0)
		echo "<tr><td colspan='5' class='text-center text-secondary py-4'>" . tr("No account activity for this period") . "</td></tr>";
	echo "</tbody><tfoot class='table-light'><tr class='fw-bold'><td colspan='2'>" . tr("Total") . "</td><td class='text-end text-nowrap'>";
	if ($assets)
		echo formatMoney($startSum);
	echo "</td>";
	echo "<td class='text-end text-nowrap'>" . formatMoney($sum) . "</td>";
	echo "<td class='text-end text-nowrap'>";
	if ($assets)
		echo formatMoney($endSum);
	echo "</td></tr></tfoot></table></div></section>";
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

<main class="container-fluid px-0">
<section class="card border-0 shadow-sm mb-4">
<div class="card-body p-3 p-lg-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
	<div><span class="text-secondary small text-uppercase fw-bold"><?php etr("Accounting report") ?></span><h1 class="h4 fw-bold mt-1 mb-0"><?php etr("Balance") ?></h1></div>
<form name="searchform" action="balance.php" method="GET" class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
<?php
$yearsChecked = '';
$monthsChecked = '';
if ($type == TYPE_YEARS) {
	$yearsChecked = 'checked';
} else {
	$monthsChecked = 'checked';
}
$periodLabel = $type == TYPE_YEARS ? date("Y", $start) : date("M Y", $start);
?>
<div class="d-flex align-items-center border rounded-3 bg-body-tertiary p-1">
	<button class="btn btn-sm btn-light border-0 px-3" type="submit" name="prev" value="1" aria-label="<?php etr("Previous period") ?>">&#8249;</button>
	<strong class="text-center px-3 text-nowrap" style="min-width:110px"><?php echo htmlspecialchars($periodLabel) ?></strong>
	<button class="btn btn-sm btn-light border-0 px-3" type="submit" name="next" value="1" aria-label="<?php etr("Next period") ?>">&#8250;</button>
</div>
<div class="btn-group btn-group-sm" role="group" aria-label="<?php etr("Report period") ?>">
	<input class="btn-check" id="period-months" type="radio" name="type" value="<?php echo TYPE_MONTHS ?>" <?php echo $monthsChecked ?> onchange="document.searchform.submit()"><label class="btn btn-outline-primary" for="period-months"><?php etr("Monthly") ?></label>
	<input class="btn-check" id="period-years" type="radio" name="type" value="<?php echo TYPE_YEARS ?>" <?php echo $yearsChecked ?> onchange="document.searchform.submit()"><label class="btn btn-outline-primary" for="period-years"><?php etr("Yearly") ?></label>
</div>
<input type="hidden" name="year" value="<?php echo date('Y', $start) ?>"/>
<?php if ($type == TYPE_MONTHS) { ?><input type="hidden" name="month" value="<?php echo date('m', $start) ?>"/><?php } ?>
</form>
</div>
</section>

<?php
$revenues = showGroup(GROUPID_REVENUES, $start, $end);
$expenses = showGroup(GROUPID_EXPENSES, $start, $end);
$profit = (-1) * ($expenses + $revenues);
echo "<section class='card border-0 shadow-sm mb-4 text-bg-primary'><div class='card-body px-4 py-3 d-flex justify-content-between align-items-center'><div><span class='small text-uppercase fw-bold opacity-75'>" . tr("Period result") . "</span><h2 class='h5 mb-0 mt-1'>" . tr("Profit") . "</h2></div><strong class='fs-4 text-nowrap'>" . formatMoney($profit) . "</strong></div></section>";
showGroup(GROUPID_ASSETS, $start, $end, true);
showGroup(GROUPID_LIABILITIES, $start, $end, true);
?>
</main>
<?php bottom() ?>
</body>
