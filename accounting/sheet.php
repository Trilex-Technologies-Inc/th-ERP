<?php
	include('include.php');

	$starttime = getMonthStepperDate();
	$endtime = addTime($starttime, TYPE_MONTHS);

	$sql = "
	select
	    t.transactionid,
	    unix_timestamp(transtime) as transtime,
		narrative,
		a.accountid,
		amount
	from transaction t
	join account a
	left outer join transaction_part tp on tp.transactionid=t.transactionid and tp.accountid=a.accountid
	where transtime between from_unixtime($starttime) and from_unixtime($endtime)
	and valid = 1
	order by t.transactionid desc, accountid
	";
    $rs = query($sql);
	
	$accounts = rs2array(query("select accountid, name from account order by accountid"));


?>

<head>
<title>thERP - <?php etr("Transaction sheet") ?></title>
<?php
styleSheet();
include_datebox();
?>
</head>

<body>

<?php include("menubar.php") ?>
<?php title(tr("Transaction sheet")) ?>

<center>
<form action="sheet.php" method="GET">
<?php monthStepper($starttime) ?>
</form>
</center>
&nbsp;

<form action="sheet.php" method=POST>
<table>
<?php
	echo "<th>" . tr("Id") . "</th>";
	echo "<th>" . tr("Date") . "</th>";
	echo "<th>" . tr("Narrative") . "</th>";
	foreach ($accounts as $account) {
		echo "<th>" . $account[0] . ' - ' . $account[1] . "</th>";
	}
    $class = "odd";
    $i = 0;
	$lastTransid = -1;
	$first = true;
    while ($row = fetch_object($rs)) {
		if ($row->transactionid != $lastTransid) {
			if (!$first) {
				echo "</tr>";
			}
	        echo "<tr class='$class'>";
			echo "<td>$row->transactionid</td>";
			echo "<td>" . formatDate($row->transtime) . "</td>";
			echo "<td>$row->narrative</td>";
			$class = ($class == "odd" ? "even" : "odd");
		}
		echo "<td>" . formatMoney($row->amount) . "</td>";
        $i++;
		$lastTransid = $row->transactionid;
		$first = false;
    }
	if (!$first)
		echo "</tr>";
    echo "<tr class='$class'>";
	echo "<td/>";
	echo "<td/>";
	echo "<td>";
	textbox('narrative_new', '');
	echo "</td>";
	foreach ($accounts as $account) {
		echo "<td>";
		$accountid = $account[0];
		moneybox("amount_new_$accountid", '');
		echo "</td>";
	}
	echo "</tr>";
?>

</table>
<br/>
<?php saveButton() ?>
</form>
</body>
