<?php
include('include.php');
include('calculations.php');


$sql = "
select
  employeeid,
  givenname,
  surname,
  bank_account
from employee
";
$q = query($sql);

$periodid = getCurrentPeriod();

$groups = rs2array(query("select g.groupid, description
                          from payaccountgroup g
                          join payaccountgroup_description d
                          on d.groupid=g.groupid and language='" . getLanguage() . "'
                          where report=1"));
$groupSum = array();
?>

<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Payment report") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php include("menubar.php") ?>
<?php title(tr("Payment report")) ?>

<table>
<th><?php echo tr("Id") ?></th>
<th><?php echo tr("Name") ?></th>
<?php
foreach ($groups as $group) {
	echo "<th>" . formatCase($group[1]) . "</th>";
}

$class = "odd";
while ($row = fetch($q)) {
	echo "<tr class='$class'>";
	echo "<td>$row->employeeid</td>";
	echo "<td><a href='employee_paystub.php?employeeid=$row->employeeid'>$row->givenname $row->surname</a></td>";
	calculateIfNeeded($row->employeeid, $periodid);
	foreach ($groups as $group) {
		$groupid = $group[0];
		$amount = findValue("select sum(amount)
		                     from payevent pe
							 join payaccount_group g on g.accountid=pe.accountid
							 where employeeid=$row->employeeid and groupid=$groupid and periodid=$periodid
							 ", 0);
		echo "<td align=right>";
	    printf('%9.2f', $amount);
		echo "</td>";
		$groupSum = sumMap($groupid, $amount, $groupSum);
	}
	echo "</tr>";
	$class = ($class == "odd" ? "even" : "odd");
}
?>
<tr>
<td/>
<td><b>Total</b></td>
<?php
	foreach ($groups as $group) {
		echo "<td align=right><b>";
		$groupid = $group[0];
		if (array_key_exists($groupid, $groupSum)) {
			$sum = findValue("select sum(amount)
			                     from payevent pe
								 join payaccount_group g on g.accountid=pe.accountid
								 where groupid=$groupid
								 ", 0);
			echo formatMoney($sum);
		}
		echo "</b></td>";
	}
?>
</tr>
</table>
</body>