<?php
include('include.php');

function periodSum($groupid)
{
	$sum = findValue("
	select sum(amount)
	from payevent pe
	join payaccount_group ag on ag.accountid=pe.accountid
	where ag.groupid=$groupid");
	return $sum;
}

$count = getParam("count", 0);

$groups = rs2array(query("
select groupid, description from payaccountgroup"));
?>
<?php head('Statistics') ?>

<body>
<?php 
top("endofperiod.php", "Statistics");
?>

<form action='statistics.php'>
<?php
echo "<table>";
th("Payaccount group");
th("Period sum");
for ($i=0; $i < $count; $i++) {
	$groupid = getParam("groupid$i");
	if (isEmpty($groupid))
		continue;
	echo "<tr>";
	echo "<td>";
	$periodSum = periodSum($groupid);
	combobox("groupid$i", $groups, $groupid, true);
	echo "</td>";
	echo "<td align=right>$periodSum</td>";
	echo "</tr>";
}
echo "<tr>";
echo "<td>";
combobox("groupid$i", $groups, null, true);
echo "</td>";
echo "</tr>";
if (!isEmpty(getParam("groupid$count")))
	$count++;
hidden('count', $count);
echo "</table>";
?>
<br>
<?php button("Refresh", "save") ?>
<br>
</form>


<?php bottom() ?>
</body>
