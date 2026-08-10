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
echo "<div class='container-fluid px-0 erp-form-layout'>";
th("Payaccount group");
th("Period sum");
for ($i=0; $i < $count; $i++) {
	$groupid = getParam("groupid$i");
	if (isEmpty($groupid))
		continue;
	echo "<div class='row g-3 align-items-center mb-2'>";
	echo "<div class='col-12 col-md-auto'>";
	$periodSum = periodSum($groupid);
	combobox("groupid$i", $groups, $groupid, true);
	echo "</div>";
	echo "<div class='col-12 col-md-auto'>$periodSum</div>";
	echo "</div>";
}
echo "<div class='row g-3 align-items-center mb-2'>";
echo "<div class='col-12 col-md-auto'>";
combobox("groupid$i", $groups, null, true);
echo "</div>";
echo "</div>";
if (!isEmpty(getParam("groupid$count")))
	$count++;
hidden('count', $count);
echo "</div>";
?>
<br>
<?php button("Refresh", "save") ?>
<br>
</form>


<?php bottom() ?>
</body>
