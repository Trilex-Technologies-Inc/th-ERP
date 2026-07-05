<?php
include('include.php');

$employeeid = getCurrentEmployee();
if (isEmpty($employeeid)) {
	echo tr("No employee associated with user!");
	die;
}	
header("Location: calendar.php?employeeid=current");
die;
?>
