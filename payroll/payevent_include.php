<?php

function backButton($employeeid)
{
	$back = getParam('back');
	/*if ($back == 'paystub')
		echo "<a href='employee_paystub.php?employeeid=$employeeid'>" . tr("Back to pay stub") . "</a>";
	else
		echo "<a href='calendar.php?employeeid=$employeeid'>" . tr("Back to calendar") . "</a>";*/
	if ($back == 'paystub')
		button("Back to pay stub", "back", "employee_paystub.php?employeeid=$employeeid");
	else
		button("Back to calendar", "back", "calendar.php?employeeid=$employeeid");

}

function employeeRow($employeeid)
{
	$emp = find("select givenname, surname from employee where employeeid=$employeeid");
	echo "<tr>";
	echo "<td>" . tr("Employee") . ":</td>";
	echo "<td>$emp->givenname $emp->surname</td>";
	echo "</tr>";
}

function dateRow($starttime)
{
	echo "<tr>";
	echo "<td>" . tr("Date") . ":</td>";
  	echo "<td>";
  	datebox("starttime", isEmpty($starttime) ? '' : date(DATE_PATTERN, $starttime));
  	echo "</td>";
	echo "</tr>";
}

?>