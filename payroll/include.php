<?php

include_once('../include/therp_include.php');

define('PERMISSION_CONFIGURATE_PAYROLL', 2);
define('PERMISSION_ADMINISTRATE_EMPLOYEES', 3);
define('PERMISSION_REGISTER_PAYEVENTS', 4);
define('PERMISSION_SELF_SERVICE', 5);

define('GROUPID_PAYABLE', 1);
define('GROUPID_GENERAL_LEDGER', 5);

define('GL_GROUPID_EXPENSES', 3);

define('TIME_REGISTRATION_IN', 1);
define('TIME_REGISTRATION_OUT', 2);
define('TIME_REGISTRATION_START_BREAK', 3);
define('TIME_REGISTRATION_END_BREAK', 4);

define('TABID_GENERAL', 1);

function getEmployee()
{
	$employeeid = getParam("employeeid");
	$employee = find("select givenname, surname from employee where employeeid=$employeeid");
	return $employee;
}

function displayPeriod($periodid = null)
{
	if ($periodid == null)
		$periodid = getCurrentPeriod();
	$row = find("select unix_timestamp(starttime) as starttime,
	             unix_timestamp(endtime) as endtime
	             from payperiod where periodid=$periodid");
	echo date(DATE_PATTERN, $row->starttime);
	echo ' - ';
	echo date(DATE_PATTERN, $row->endtime);
}

function getEmployeeStr($employeeid)
{
	$emp = find("select givenname, surname from employee where employeeid=$employeeid");
	return $emp->givenname . ' ' . $emp->surname;
}

function displayEmployee($employeeid)
{
	echo getEmployeeStr($employeeid);
}

function getPolicy($employeeid, $periodid)
{
	$sql = "
	select
	  policyid
	from employee
	where employeeid=$employeeid
	";
	$policyid = findValue($sql);
	return $policyid;
}

function getEmployeeType($employeeid, $periodid)
{
	$sql = "
	select
	  typeid
	from employee_type
	where employeeid=$employeeid and fromperiodid<=$periodid
	order by fromperiodid desc
	limit 1
	";
	return findValue($sql);
}

function saveMultiple($update_sql, $insert_sql, $delete_sql)
{
    $i = 0;
    while (array_key_exists("row_$i", $_POST)) {
        $doInsert = false;
        $update_sql1 = $update_sql;
        $delete_sql1 = $delete_sql;
        $j = 0;
        while (array_key_exists("a$j" . "_$i", $_POST)) {
            $value = $_POST["a$j" . "_$i"];
            if ($update_sql != null)
                $update_sql1 = str_replace(":a$j", $value, $update_sql1);
            if ($delete_sql != null)
                $delete_sql1 = str_replace(":a$j", $value, $delete_sql1);
            $j++;
        }
        if (array_key_exists("del_$i", $_POST)) {
            query($delete_sql1);
        } else if ($update_sql != null) {
            query($update_sql1);
        }
        $i++;
    }
    $i = 0;
    $doInsert = false;
    while (array_key_exists("n$i", $_POST)) {
        $value = $_POST["n$i"];
        $insert_sql = str_replace(":n$i", $value, $insert_sql);
        if (strlen($value) > 0)
            $doInsert = true;
        $i++;
    }
    if ($doInsert)
        query($insert_sql);
}

function saveSingle($update_sql, $insert_sql, $delete_sql)
{
    if (!array_key_exists("save", $_POST))
        return;
    if (array_key_exists("new", $_POST))
        $sql = $insert_sql;
    else if (array_key_exists("delete", $_POST))
        $sql = $delete_sql;
    else
        $sql = $update_sql;
    $j = 0;
    while (array_key_exists("a$j", $_POST)) {
        $value = $_POST["a$j"];
        $sql = str_replace(":a$j", $value, $sql);
        $j++;
    }
    query($sql);
}

function getAccountTypeDescriptionList()
{
	$list = array();
	$list[] = array(ACCOUNT_TYPE_EXPENSES, "Expenses");
	$list[] = array(ACCOUNT_TYPE_DEDUCTION, "Deduction");
	$list[] = array(ACCOUNT_TYPE_AMENDMENT, "Amendment");
	return $list;
}

function sumMap($key, $amount, $map)
{
	if (array_key_exists($key, $map))
		$sum = $map[$key];
	else
		$sum = 0;
	$sum += $amount;
	$map[$key] = $sum;
	return $map;
}

function getInputTypeDescriptionList()
{
	$list = array();
	$list[] = array(INPUT_TYPE_AMOUNT, tr("Amount"));
	$list[] = array(INPUT_TYPE_UNITS, tr("Units"));
	$list[] = array(INPUT_TYPE_MINUTES, tr("Hours"));
	$list[] = array(INPUT_TYPE_DAYS, tr("Days"));
	return $list;
}

function formatQuantity($quantity, $inputtype)
{
	if ($inputtype == INPUT_TYPE_MINUTES)
		return minutes2hours($quantity);
	return $quantity;
}

function formatDateInterval($start, $end)
{
	if (!isEmpty($end))
		$end = addDay($end, -1);
	if ($start == $end)
		return formatDate($start);
	if (DATE_PATTERN == 'Y-m-d' || DATE_PATTERN == 'y-m-d') {
		$str = formatDate($start) . ' -> ';
		if (date('Y', $start) == date('Y', $end)) {
			if (date('m', $start) == date('m', $end))
				$str .= date('d', $end);
			else
				$str .= date('m-d', $end);
		} else
			$str .= formatDate($end);
		return $str;
	}
}

function getCurrentEmployee()
{
	return findValue("select employeeid from user where username='" . getUser() . "'");
}

function menubar($currentHref = null, $helpSection = null)
{
	top0("Payroll");
	echo "<table width='100%' cellspacing=0 cellpadding=0 >";
	echo "<tr>";
	echo "<td>";
	echo "<table width='100%' class=menubar>";
		echo "<tr>";
		$percent = 20;
		menu('employees.php', 'Employees', $percent, true, $currentHref);
		menu('reporting.php', 'Reporting', $percent, true, $currentHref);
		menu('endofperiod.php', 'End of period', $percent, true, $currentHref);
		menu('configuration.php', 'Configuration', $percent, true, $currentHref);
		if ($helpSection != null)
			$helpSection = '#' . $helpSection;
		menu('help.php' . $helpSection, 'Help', $percent, false, $currentHref);
		echo "</tr>";
	echo "</table>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
}

?>
