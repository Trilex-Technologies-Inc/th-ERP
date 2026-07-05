<?php

function buildTabs($employeeid, $tab)
{
	echo "<ul id=primary>";
	if ($tab == 'general')
		echo "<li><span>" . tr("General") . "</span></li>";
	else
		buildLink("employee_detail.php?employeeid=$employeeid", tr("General"), $employeeid);
	$tabs = query("select tabid, name from emp_tab where tabid != " . TABID_GENERAL);
	while ($row = fetch($tabs)) {
		if ($tab == "tab_$row->tabid")
			echo "<li><span>" . $row->name . "</span></li>";
		else
			buildLink("employee_attributes.php?employeeid=$employeeid&tabid=$row->tabid", 
			               $row->name, $employeeid);		
	}
		
	if ($tab == 'schedule')
		echo "<li><span>" . tr("Schedule") . "</span></li>";
	else
		buildLink("employee_schedule.php?employeeid=$employeeid", tr("Schedule"), $employeeid);
	buildLink("calendar.php?employeeid=$employeeid", tr("Calendar"), $employeeid);
	if ($tab == 'payitems')
		echo "<li><span>" . tr("Pay items") . "</span></li>";
	else
		buildLink("employee_payitems.php?employeeid=$employeeid", tr("Pay items"), $employeeid);
	if ($tab == 'paystub')
		echo "<li><span>" . tr("Pay stub") . "</span></li>";
	else
		buildLink("employee_paystub.php?employeeid=$employeeid", tr("Pay stub"), $employeeid);
	if ($tab == 'trips')
		echo "<li><span>" . tr("Trips") . "</span></li>";
	else
		buildLink("trips.php?employeeid=$employeeid", tr("Trips"), $employeeid);
	echo "</ul>";
}

function buildLink($href, $text, $employeeid)
{
	echo "<li>";
	if (isEmpty($employeeid))
		echo  "<a>$text</a>";
	else
		echo "<a href='$href'>$text</a>";
	echo "</li>";
}

?>
