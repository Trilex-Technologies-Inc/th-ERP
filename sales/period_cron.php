<?php
$_REQUEST['username'] = $argv[1];
$_REQUEST['pwd'] = $argv[2];

include('include.php');
include('salesorder.inc.php');
include('period.inc.php');

function rollPeriod($cycleid, $periodid)
{
	createReceivables($cycleid, $periodid);
	sendReceivables($cycleid, $periodid);
}

$cycleid = $argv[3];

$period = getReceivablesPeriod($cycleid);
if ($period == null)
	die("There are no receivables periods waiting to be processed.\n");
$periodStart = formatDatetime($period->starttime);
$user = getUser();

if ($period->starttime > time()) {
	$currentTime = fromatDatetime(time());
	$mess = "Current period start time ($periodStart) is after current time ($currentTime)";
	sql("
	insert into logger (loggtext, loggtime, username, level)
	values ('$mess', now(), '$user', 100)"); 
}

tx("rollPeriod", array($cycleid, $period->periodid));

$periodEnd = formatDatetime($period->endtime);
$mess = "Period $periodStart - $periodEnd sucessfully processed.";
sql("
insert into logger (loggtext, loggtime, username, level)
values ('$mess', now(), '$user', 100)"); 

?>

