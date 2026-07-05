<?php
include('include.php');
include('payevent_include.php');

define('GROUPID_TRIP', 9898);

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

$groupid = getParam('groupid');
if ($groupid == GROUPID_TRIP) {
	header("Location: trip.php?employeeid=$employeeid0");
}
$accountid = getParam('accountid');
$payeventid = getParam('payeventid');
$starttime = parseDate(getParam("starttime", time()));
$endtime = isEmpty($starttime) ? null : strtotime("+1 day", $starttime);
$back = getParam("back");

$periodid = getParam('periodid');
if (isEmpty($periodid))
	$periodid = getCurrentPeriod();

if (isSave()) {
	$value = getParam("value");
	$accountid = getParam('accountid');
	$inputtype = findValue("select inputtype from payaccount where accountid=$accountid");
	if ($inputtype == INPUT_TYPE_MINUTES)
		$value = parseTime($value);
	$narrative = getParam('narrative');
	$starttime = parseDate(getParam("starttime"));	
	if ($inputtype == INPUT_TYPE_DAYS)
		$endtime = strtotime("+" . $value . " day", $starttime);
	else
		$endtime = strtotime("+1 day", $starttime);
	$date = $starttime;
	$correction = getParam("correction", 0);
	if (!isEmpty($payeventid)) {
		$sql =
		  "update payevent
		   set value=$value,
			   accountid=$accountid,
			   narrative='$narrative',
			   starttime=from_unixtime($starttime),
			   endtime=from_unixtime($endtime),
			   correction=$correction,
			   regtime=now()
		   where payeventid=$payeventid
		   ";
		sql($sql);
	} else {
		$sql = "insert into payevent (employeeid, periodid, value, accountid, narrative, starttime, endtime, correction, regtime) ";
		$sql .= "values ($employeeid, $periodid, $value, $accountid, '$narrative', from_unixtime($starttime), from_unixtime($endtime), $correction, now()) ";
		sql($sql);
		$payeventid = insert_id();
	}
}
if (isDelete()) {
	sql("delete from payevent where payeventid=$payeventid");
	sql("update employee set calctime=now() where employeeid=$employeeid");
	$payeventid = null;
}

$row = new Dummy();
$value = null;
if (!isEmpty($payeventid)) {
	$row = find("select
	               value,
	               pe.accountid,
				   employeeid,
				   unix_timestamp(starttime) as starttime,
				   inputtype,
				   correction,
				   derived,
				   quantity,
				   amount,
				   description,
				   employeeid
	             from payevent pe
				 join payaccount pa on pa.accountid=pe.accountid
	             where payeventid=$payeventid", true);
	$employeeid = $row->employeeid;
	$starttime = $row->starttime;
	if ($row->inputtype == INPUT_TYPE_MINUTES)
		$value = minutes2hours($row->quantity);
	else
		$value = $row->amount;
	$accountid = $row->accountid;
	if ($row->derived)
		$_REQUEST['readonly'] = true;
	$employeeid0 = $row->employeeid;
	
	$debit = query("
	select glaccountid, share, name
	from payevent_debit pd
	join account a on pd.glaccountid=a.accountid
	where payeventid=$payeventid");
}
$policyid = getPolicy($employeeid, $periodid);

$inputtype = 0;

$groups = rs2array(query("
select g.groupid, description
from payaccountgroup g
join policy_accountgroup pg on pg.groupid=g.groupid and policyid=$policyid
"));
$groups[] = array(GROUPID_TRIP, "Trip");

$accounts = array();
if (isEmpty($groupid)) {
	$groupid = $groups[0][0];
}
$accounts = rs2array(query("select a.accountid, description
							from payaccount a
							join payaccount_group ag on ag.accountid=a.accountid
							where groupid=$groupid"));
if (isEmpty($accountid)) {
	$accountid = $accounts[0][0];
}
$inputtype = INPUT_TYPE_AMOUNT;
if (!isEmpty($accountid))
	$inputtype = findValue("select inputtype from payaccount where accountid=$accountid");

if (isEmpty($value)) {
	if ($inputtype == INPUT_TYPE_UNITS)
		$value = 1;
}

?>

<?php head('Pay event') ?>
<script>
function onGroupChange()
{
	<?php
	echo "document.location.href=\"payevent.php?employeeid=$employeeid0";
	if (!isEmpty($payeventid))
		echo "&payeventid=$payeventid";
	echo "&starttime=\" + form1.starttime.value + \"";
	echo "&back=$back";
	echo "&groupid=\" + form1.groupid.value;\n";
	?>
}

function onAccountChange()
{
	<?php
	echo "document.location.href=\"payevent.php?employeeid=$employeeid0";
	if (!isEmpty($payeventid))
		echo "&payeventid=$payeventid";
	echo "&starttime=\" + form1.starttime.value + \"";
	echo "&groupid=$groupid";
	echo "&back=$back";
	echo "&accountid=\" + form1.accountid.value;\n";
	?>
}
</script>
</head>

<body>

<?php 
top("employees.php", "Pay event");
/*if ($selfservice)
	include("selfservice_menubar.php");
else
	include("menubar.php");*/
?>
<?php
$title = tr("Pay event");
if (!isEmpty($payeventid))
	$title .= " > $payeventid"; 
title($title); 
?>

<form action="payevent.php" method=POST name='form1' class="border">
<input type=hidden name=employeeid value="<?php echo $employeeid0 ?>"/>
<input type=hidden name=periodid value="<?php echo $periodid ?>"/>
<input type=hidden name=payeventid value="<?php echo $payeventid ?>"/>
<input type=hidden name=back value="<?php echo $back ?>"/>
<table>
<tr>
  <td class=label><?php etr("Name") ?>:</td>
  <td><?php displayEmployee($employeeid) ?></td>
</tr>
<tr>
  <td class=label><?php etr("Period") ?>:</td>
  <td><?php displayPeriod($periodid) ?></td>
</tr>
<?php
echo "<tr>";
echo "<td class=label>" . tr("Date") . ":</td>";
echo "<td>";
if (array_key_exists('readonly', $_REQUEST)) {
	echo formatDate($starttime);
} else {
	datebox("starttime", isEmpty($starttime) ? '' : date(DATE_PATTERN, $starttime));
}
echo "</td>";
echo "</tr>";
?>
<tr>
  <td class=label><?php etr("Type") ?>:</td>
  <td>
  <?php
	if (array_key_exists('readonly', $_REQUEST)) {
 		echo "$row->accountid - $row->description";
  	} else {
	  	comboBox('groupid', $groups, $groupid, false, 'onGroupChange()');
	  	echo "&nbsp;";
	  	comboBox('accountid', $accounts, $accountid, false, 'onAccountChange()');
  	}
  ?>
  </td>
</tr>
<tr>
  <td class=label><?php echo getDescription($inputtype, getInputTypeDescriptionList()) ?>:</td>
  <td><?php
	if ($inputtype == INPUT_TYPE_MINUTES) {
		if (array_key_exists('readonly', $_REQUEST)) 
			echo formatTime($value);
		else		
			timebox('value', $value);
  	} else {
		if (array_key_exists('readonly', $_REQUEST)) 
  			echo $value;
  		else	
			numberBox('value', $value);
  	}
  ?>
  </td>
</tr>
<tr>
  <td class=label><?php etr("Correction") ?>:</td>
  <td><?php checkBox('correction', $row->correction) ?></td>
</tr>
<?php
$first = true;
while ($row = fetch($debit)) {
	if ($first) {
		echo "<tr><td class=label>" . tr("General ledger") . ":</td>";
	} else 
		echo ", ";
	echo "<td>". $row->glaccountid .' - '. $row->name;
	$first = false;
}
if (!$first) {
	echo "</tr>";
}
?>
</table>
<table>
<tr>
<td>
<?php
$label = isEmpty($payeventid) ? 'Submit' : 'Save';
button($label, "save")
?>
</td>
<td>
<?php
if (!isEmpty($payeventid))
	deleteButton()
?>
</td>
<td>
<?php backButton($employeeid0) ?>
</td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
