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
	echo "<center><p>$mess</p></center>";
?>


	<div id="header">
	<?php buildTabs($employeeid, 'paystub') ?>
	</div>
	<div id="main">
		<div id="contents">

<center>

<form action='trips.php' method=POST>
<input type=hidden name=employeeid value='<?php echo $employeeid0 ?>'/>
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Id") ?></th>
<th><?php echo tr("Date") ?></th>
<th><?php echo tr("Destination") ?></th>
<th><?php echo tr("Purpose") ?></th>
<th><?php echo tr("Distance") ?></th>

<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
    $href = null;
    $href = "trip.php?tripid=$row->tripid";
    echo "<tr class='$class'>";
    deleteColumn("trips.php?employeeid=$employeeid0&del_tripid=$row->tripid");
    echo "<td align=right><a href='$href'>$row->tripid</a></td>";
    echo "<td><a href='$href'>";
	echo formatDateInterval($row->starttime, $row->endtime);
	echo "</a></td>";
	echo "<td>$row->destination</td>";
	echo "<td>$row->purpuse</td>";
	echo "<td align=right>$row->distance</td>";
	echo "</tr>\n";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
?>

</table>
<br/>
<?php button("Add", "add", "trip.php?employeeid=$employeeid") ?>
</form>
</center>

		</div>
	</div>
<?php bottom() ?>
</body>
