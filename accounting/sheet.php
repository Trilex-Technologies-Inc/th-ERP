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

<div class="d-flex justify-content-center">
<form action="sheet.php" method="GET">
<?php monthStepper($starttime) ?>
</form>
</div>

<form action="sheet.php" method=POST>
<div class="card border-0 shadow-sm mb-3 overflow-auto">
<div class="d-flex fw-semibold bg-body-tertiary border-bottom p-2 gap-3" style="min-width: max-content">
<?php
	echo "<div style='width:6rem'>" . tr("Id") . "</div>";
	echo "<div style='width:8rem'>" . tr("Date") . "</div>";
	echo "<div style='width:14rem'>" . tr("Narrative") . "</div>";
	foreach ($accounts as $account) {
		echo "<div style='width:12rem'>" . $account[0] . ' - ' . $account[1] . "</div>";
	}
	echo "</div><div class='list-group list-group-flush'>";
    $i = 0;
	$lastTransid = -1;
	$first = true;
    while ($row = fetch_object($rs)) {
		if ($row->transactionid != $lastTransid) {
			if (!$first) {
				echo "</div></div>";
			}
	        echo "<div class='list-group-item'><div class='d-flex align-items-center gap-3' style='min-width:max-content'><div style='width:6rem'>$row->transactionid</div>";
			echo "<div style='width:8rem'>" . formatDate($row->transtime) . "</div>";
			echo "<div style='width:14rem'>$row->narrative</div>";
		}
		echo "<div class='text-end' style='width:12rem'>" . formatMoney($row->amount) . "</div>";
        $i++;
		$lastTransid = $row->transactionid;
		$first = false;
    }
	if (!$first)
		echo "</div></div>";
    echo "<div class='list-group-item'><div class='d-flex align-items-center gap-3' style='min-width:max-content'><div style='width:6rem'></div><div style='width:8rem'></div><div style='width:14rem'>";
	textbox('narrative_new', '');
	echo "</div>";
	foreach ($accounts as $account) {
		echo "<div style='width:12rem'>";
		$accountid = $account[0];
		moneybox("amount_new_$accountid", '');
		echo "</div>";
	}
	echo "</div></div></div>";
?>
</div>
<br/>
<?php saveButton() ?>
</form>
</body>
