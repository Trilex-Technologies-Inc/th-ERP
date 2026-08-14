<?php
include('include.php');
include('calculations.php');
include('employee.inc');

$employeeid0 = getParam("employeeid");
checkPermission(PERMISSION_ADMINISTRATE_EMPLOYEES);
$employeeid = $employeeid0;

$employee = find("select givenname, surname from employee where employeeid=$employeeid");

$del_tripid = getParam('del_tripid');
if (!isEmpty($del_tripid)) {
	sql("delete from payevent where tripid=$del_tripid");
	sql("update employee set calctime=null where employeeid=$employeeid");
}

$mess = null;

$rs = query("
select
	tripid,
	purpuse,
	destination,
	distance,
	unix_timestamp(starttime) as starttime,
	unix_timestamp(endtime) as endtime
from trip pe
where employeeid=$employeeid 
order by tripid desc
");

?>

<?php head('Trips') ?>

<body>
<?php 
top("employees.php", "Trips", $employee->givenname . ' ' . $employee->surname);
if ($mess != null)
	echo "<div class='alert alert-info'>" . htmlspecialchars($mess) . "</div>";
?>


	<div id="header">
	<?php buildTabs($employeeid, 'paystub') ?>
	</div>
	<div id="main">
		<div id="contents">

<form action="trips.php" method="POST">
<input type="hidden" name="employeeid" value="<?php echo htmlspecialchars($employeeid0) ?>"/>
<div class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
<div><h2 class="h5 fw-bold mb-1"><?php echo tr("Trips") ?></h2><p class="text-secondary small mb-0"><?php echo htmlspecialchars($employee->givenname . ' ' . $employee->surname) ?></p></div>
<?php button("Add", "add", "trip.php?employeeid=$employeeid") ?>
</div>
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead><tr>
<th class="text-center" style="width: 90px;"><?php echo tr("Delete") ?></th>
<th class="text-end" style="width: 120px;"><?php echo tr("Id") ?></th>
<th><?php echo tr("Date") ?></th>
<th><?php echo tr("Destination") ?></th>
<th><?php echo tr("Purpose") ?></th>
<th class="text-end" style="width: 130px;"><?php echo tr("Distance") ?></th>
</tr></thead>
<tbody>

<?php
$i = 0;
while ($row = fetch($rs)) {
    $href = null;
    $href = "trip.php?tripid=$row->tripid";
    echo "<tr>";
    echo "<td class='text-center'>";
    deleteIcon("trips.php?employeeid=$employeeid0&del_tripid=$row->tripid");
    echo "</td>";
    echo "<td class='text-end font-monospace'><a href='$href'>$row->tripid</a></td>";
    echo "<td><a class='fw-semibold' href='$href'>";
	echo formatDateInterval($row->starttime, $row->endtime);
	echo "</a></td>";
	echo "<td>" . htmlspecialchars($row->destination) . "</td>";
	echo "<td>" . htmlspecialchars($row->purpuse) . "</td>";
	echo "<td class='text-end'>" . htmlspecialchars($row->distance) . "</td>";
	echo "</tr>\n";
    $i++;
}
if ($i == 0)
	echo "<tr><td colspan='6' class='text-center text-secondary py-5'>" . tr("No records found") . "</td></tr>";
?>
</tbody>
</table>
</div>
<div class="card-footer bg-white d-flex justify-content-end py-3"><span class="text-secondary small"><?php echo $i ?> <?php echo tr("records") ?></span></div>
</div>
</form>

		</div>
	</div>
<?php bottom() ?>
</body>
