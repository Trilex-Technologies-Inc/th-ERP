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
	echo "<tr><td colspan=3><h2>" . $label . "</h2></td></tr>";

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
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
        echo "<td>$row->accountid</td>";
        $href = "account_balance.php?accountid=$row->accountid&year=$year";
        if ($type == TYPE_MONTHS)
        	$href .= "&month=$month";
        echo "<td><a href='$href'>$row->name</a></td>";
		echo "<td align=right>";
		if ($assets) {
			echo formatMoney($row->startbalance);
		}
		echo "</td>";
        echo "<td align=right>" . formatMoney($row->balance) . "</td>";
		echo "<td align=right>";
		if ($assets) {
			echo formatMoney($row->endbalance);
		}
		echo "</td>";
        echo "</tr>";
        $sum += $row->balance;
        $startSum += $row->startbalance;
        $endSum += $row->endbalance;
        $class = ($class == "odd" ? "even" : "odd");
    }
	echo "<tr><td colspan=2><b>" . tr("Total") . "</b></td>";
	echo "<td align=right>";
	if ($assets)
		echo formatMoney($startSum);
	echo "</td>";
	echo "<td align=right>" . formatMoney($sum) . "</td>";
	echo "<td align=right>";
	if ($assets)
		echo formatMoney($endSum);
	echo "</td>";
	echo "</tr>";
	echo "<tr height=10></tr>";
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
<center>
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
</center>
</form>

<center>
<table>
<th><?php etr("Id") ?></th>
<th><?php etr("Name") ?></th>
<th><?php etr("Starting") ?></th>
<th><?php etr("Period") ?></th>
<th><?php etr("Final") ?></th>
<?php
$revenues = showGroup(GROUPID_REVENUES, $start, $end);
$expenses = showGroup(GROUPID_EXPENSES, $start, $end);
$profit = (-1) * ($expenses + $revenues);
echo "<tr>";
echo "<td colspan=3><b>" . tr("Profit") . "</b></td>";
echo "<td align=right><b>" . formatMoney($profit) . "</b></td>";
echo "</tr>";
echo "<tr height=10></tr>";
showGroup(GROUPID_ASSETS, $start, $end, true);
showGroup(GROUPID_LIABILITIES, $start, $end, true);
?>
</table>
</center>
<?php bottom() ?>
</body>
