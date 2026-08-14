<?php
include('include.php');

$employeeid0 = getParam("employeeid");
$selfservice = false;
if ($employeeid0 == 'current') {
	checkPermission(PERMISSION_SELF_SERVICE);
	$employeeid = getCurrentEmployee();
	$selfservice = true;
	$_REQUEST['selfservice'] = 1;
} else {
	checkPermission(PERMISSION_ADMINISTRATE_EMPLOYEES);
	$employeeid = $employeeid0;
}

$tripid = getParam('tripid');
$starttime = parseDate(getParam("starttime", time()));
$endtime = isEmpty($starttime) ? null : strtotime("+1 day", $starttime);
$back = getParam("back");

$periodid = getParam('periodid');
if (isEmpty($periodid))
	$periodid = getCurrentPeriod();

if (isSave()) {
	$origin = getParam("origin");
	$destination = getParam("destination");
	$distance = prepParam("distance");
	$purpuse = getParam('purpuse');
	$starttime = parseDate(getParam("starttime"));
	$endtime = parseDate(getParam("endtime"));
	$endtime = strtotime("+1 day", $endtime);
	$date = $starttime;
	$night_allowance = getParam("night_allowance", 0);
	if (!isEmpty($tripid)) {
		$sql = "
		update trip
		set
			origin='$origin',
			destination='$destination',
			purpuse='$purpuse',
			distance=$distance,
			starttime=from_unixtime($starttime),
			endtime=from_unixtime($endtime),
			night_allowance=$night_allowance
		where tripid=$tripid";
		sql($sql);
	} else {
		$sql = "insert into trip (employeeid, origin, destination, purpuse, starttime, endtime, distance, night_allowance) ";
		$sql .= "values ($employeeid, '$origin', '$destination', '$purpuse', from_unixtime($starttime), from_unixtime($endtime), $distance, $night_allowance) ";
		sql($sql);
		$tripid = insert_id();
	}
}
if (isDelete()) {
	sql("delete from trip where tripid=$tripid");
	$tripid = null;
}

if (array_key_exists('confirm', $_POST)) {
	begin();
	$productid = findValue("
	select carcompensation_productid from travelconf");
	$price = findValue("
	select price from sales_price
	where listid=1 and productid='$productid'");
	$debitaccountid = findValue("
	select expense_accountid
	from category c
	join product p on p.categoryid=c.categoryid
	where p.productid='$productid'");

	$cashaccountid = findValue("
	select default_cash from accountconf");
	$trip = find("
	select
		unix_timestamp(endtime) as endtime,
		unix_timestamp(starttime) as starttime,
		origin,
		destination,
		purpuse,
		distance,
		night_allowance
	from trip
	where tripid=$tripid");
	$narrative = tr("Trip");
	$user = getUser();
	$transactionid = findValue("
	select max(transactionid) from transaction", 0);
	$transactionid++;
	sql("
	insert into transaction (transactionid, narrative, transtime, createdby, valid, createdtime)
	values ($transactionid, '$narrative', from_unixtime($trip->endtime), '$user', 1, now())");
	$amount = $trip->distance * $price;
	$sum = $amount;
	sql("
	insert into transaction_part (transactionid, dimid, accountid, amount)
	values ($transactionid, 1, $debitaccountid, $amount)");
	$days = dayDiff($trip->endtime, $trip->starttime);
	if ($days > 1) {
		$perdiem_productid = findValue("
		select perdiem_productid from travelconf");
		$perdiem_price = findValue("
		select price from sales_price
		where listid=1 and productid='$perdiem_productid'");
		$perdiem_accountid = findValue("
		select expense_accountid
		from category c
		join product p on p.categoryid=c.categoryid
		where p.productid='$perdiem_productid'");
		$amount = $days * $perdiem_price;
		sql("
		insert into transaction_part (transactionid, dimid, accountid, amount)
		values ($transactionid, 1, $perdiem_accountid, $amount)");
		$sum += $amount;
	}
	if ($trip->night_allowance && $days > 1) {
		$night_productid = findValue("
		select night_productid from travelconf");
		$night_price = findValue("
		select price from sales_price
		where listid=1 and productid='$night_productid'");
		$night_accountid = findValue("
		select expense_accountid
		from category c
		join product p on p.categoryid=c.categoryid
		where p.productid='$night_productid'");
		$days--;
		$amount = $days * $night_price;
		if ($night_accountid == $perdiem_accountid) {
			sql("
			update transaction_part set amount=amount+$amount
			where transactionid=$transactionid and dimid=1 and accountid=$night_accountid");
		} else {
			sql("
			insert into transaction_part (transactionid, dimid, accountid, amount)
			values ($transactionid, 1, $night_accountid, $amount)");
		}
		$sum += $amount;
	}

	sql("
	insert into transaction_part (transactionid, dimid, accountid, amount)
	values ($transactionid, 1, $cashaccountid, (-1) * $sum)");
	sql("
	update trip set transactionid=$transactionid
	where tripid=$tripid");
	commit();
}

$row = new Dummy();
$value = null;
if (!isEmpty($tripid)) {
	$row = find("
	select
		employeeid,
		origin,
		destination,
		purpuse,
 	    unix_timestamp(starttime) as starttime,
 	    unix_timestamp(endtime) as endtime,
	    distance,
	    transactionid,
	    night_allowance
    from trip
    where tripid=$tripid", true);
	$employeeid = $row->employeeid;
	$starttime = $row->starttime;
	$employeeid0 = $row->employeeid;
}
$ro = $row->transactionid != null;

?>

<?php head('Trip') ?>

<body>

<?php
top("employees.php", "Trip");
?>
<?php
$title = tr("Trip");
if (!isEmpty($tripid))
	$title .= " > $tripid";
title($title);
?>

<form action="trip.php" method="POST" name="form1">
<input type="hidden" name="employeeid" value="<?php echo htmlspecialchars($employeeid0) ?>"/>
<input type="hidden" name="periodid" value="<?php echo htmlspecialchars($periodid) ?>"/>
<input type="hidden" name="tripid" value="<?php echo htmlspecialchars($tripid) ?>"/>
<input type="hidden" name="back" value="<?php echo htmlspecialchars($back) ?>"/>
<div class="card border-0 shadow-sm">
<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
<div><h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($title) ?></h2><p class="text-secondary small mb-0"><?php etr("Trip") ?></p></div>
<span class="badge text-bg-light border"><?php echo isEmpty($tripid) ? tr("New") : '#' . htmlspecialchars($tripid) ?></span>
</div>
<div class="card-body p-4">
<div class="row g-4">
<div class="col-12 col-lg-6">
  <label class="form-label fw-semibold"><?php etr("Name") ?></label>
  <div class="form-control-plaintext"><?php displayEmployee($employeeid) ?></div>
</div>
<div class="col-12 col-lg-6">
  <label class="form-label fw-semibold"><?php etr("Period") ?></label>
  <div class="form-control-plaintext"><?php displayPeriod($periodid) ?></div>
</div>
<div class="col-12 col-lg-6">
	<label class="form-label fw-semibold"><?php etr("Starttime") ?></label>
	<div><?php
	if ($ro)
		echo formatDate($row->starttime);
	else
		datebox('starttime', $row->starttime)
	?></div>
</div>
<div class="col-12 col-lg-6">
	<label class="form-label fw-semibold"><?php etr("Endtime") ?></label>
	<div><?php
	if ($ro)
		echo formatDate(addTime($row->endtime, TYPE_DAYS, -1));
	else
		datebox('endtime', addTime($row->endtime, TYPE_DAYS, -1));
	?></div>
</div>
<div class="col-12 col-lg-6">
	<label class="form-label fw-semibold"><?php etr("Origin") ?></label>
	<div><?php
	if ($ro)
		echo htmlspecialchars($row->origin);
	else
		textbox('origin', $row->origin)
	?></div>
</div>
<div class="col-12 col-lg-6">
	<label class="form-label fw-semibold"><?php etr("Destination") ?></label>
	<div><?php
	if ($ro)
		echo htmlspecialchars($row->destination);
	else
		textbox('destination', $row->destination)
	?></div>
</div>
<div class="col-12">
	<label class="form-label fw-semibold"><?php etr("Purpose") ?></label>
	<div><?php
	if ($ro)
		echo htmlspecialchars($row->purpuse);
	else
		textbox('purpuse', $row->purpuse, 80);
	?></div>
</div>
<div class="col-12 col-lg-6">
	<label class="form-label fw-semibold"><?php etr("Distance") ?></label>
	<div><?php
	if ($ro)
		echo htmlspecialchars($row->distance);
	else
		numberbox('distance', $row->distance)
	?></div>
</div>
<div class="col-12 col-lg-6">
	<label class="form-label fw-semibold"><?php etr("Night allowance") ?></label>
	<div class="form-check"><?php checkbox('night_allowance', $row->night_allowance) ?></div>
</div>
<?php
if ($row->transactionid != null) {
	echo "<div class='col-12'>";
	echo "<label class='form-label fw-semibold'>" . tr("Transaction") . "</label><div>";
	echo "<a href='../accounting/transaction.php?transactionid=$row->transactionid'>$row->transactionid</a>";
	echo "</div>";
	echo "</div>";
}
?>
</div>
</div>
<div class="card-footer bg-white d-flex flex-wrap gap-2 py-3">
<?php
$label = isEmpty($tripid) ? 'Submit' : 'Save';
button($label, "save");
$href = "trip_report.php?tripid=$tripid";
button("Print", "print", $href);
?>
<?php
if (!isEmpty($tripid) && !$ro) {
	button("Confirm", "confirm");
	deleteButton();
}
?>
</div>
</div>
</form>
<?php bottom() ?>
</body>
