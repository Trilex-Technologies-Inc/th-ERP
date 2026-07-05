<?php
include('database.inc.php');

session_start();
if (file_exists('../conf/config.php'))
	include('../conf/config.php');
if (file_exists('../conf/configure.php'))
	include('../conf/configure.php');
else {
	defineIfNotDefined('DBHOST', 'localhost');
	defineIfNotDefined('DBNAME', getRealm());
	defineIfNotDefined('DBUSER', 'therp');
	defineIfNotDefined('DBPWD', 'abc123');
	defineIfNotDefined('VENDOR', 'mysql');
	connect(DBHOST, DBUSER, DBPWD);
	select_db(getDBName()) or die('Could not select database');
}
defineIfNotDefined('CHARSET', 'UTF-8');
header("Content-type: text/html; charset=" . CHARSET);
defineIfNotDefined('DATE_PATTERN_MYSQL', '%Y-%m-%d');
defineIfNotDefined('DATE_PATTERN', 'Y-m-d');
defineIfNotDefined('TIME_PATTERN', 'H:i');

authenticate();

if (array_key_exists('language', $_SESSION)) {
	$lang = $_SESSION['language'];
	include('../languages/' . $_SESSION['language'] . '.inc');
}

if (defined('TZ'))
	date_default_timezone_set(TZ);

function myExceptionHandler($e)
{
	echo "<h1>Technical error</h1>";
	echo "<pre>";
	echo $e;
	echo "</pre>";
}

define('TYPE_DAYS', 1);
define('TYPE_HOURS', 0);
define('TYPE_MONTHS', 2);
define('TYPE_WEEKS', 3);
define('TYPE_YEARS', 4);

function hours2minutes($hours)
{
	$hours = strtok($hours, ":.");
	$minutes = strtok(":.");
	return $hours*60 + $minutes;
}

function minutes2hours($minutes)
{
	$hours = floor($minutes/60);
	$minutes = $minutes - $hours*60;
	return sprintf("%02d:%02d", $hours, $minutes);
}


function isEmpty($str)
{
	return (strlen(trim($str)) == 0) || ($str == "null");
}

function getParam($name, $default = null)
{
	if (array_key_exists($name, $_REQUEST)) {
		$param = $_REQUEST[$name];
		if ($default != null && isEmpty($param)) {
			return $default;
		}
		return $param;
	} else
		return $default;
}


function formatCase($str)
{
    $first = substr($str, 0, 1);
    $first = strtoupper($first);
    $tail = substr($str, 1);
    return $first . $tail;
}

function field_name($rs, $i)
{
    return mysql_field_name($rs, $i);
}

function num_fields($rs)
{
    return mysql_num_fields($rs);
}


function buttonRow($buttons)
{
    echo "<table class='buttonrow'>";
    echo "<tr>";
    for ($i = 0; $i < count($buttons); $i++) {
        echo "<td>";
        echo $buttons[$i];
        echo "</td>";
    }
    echo "</tr>\n";
    echo "</table>";
}

function button($caption, $name, $url = null, $accesskey = null)
{
	$caption = tr($caption);
    $type = "submit";
    if ($url != null)
        $type = "button";
    echo "<input type=$type value='$caption' name='$name' ";
    if ($url != null)
        echo "onClick=\"window.location.href='$url'\"";
	if ($accesskey != null)
		echo "accesskey='$accesskey'";
    echo "/>";
}

function newButton($url = null)
{
    return button("New", "new", $url);
}

function saveButton()
{
    return button("Save", "save", null);
}

function searchButton()
{
    return button("Search", "search", null);
}

function deleteButton()
{
    return button("Delete", "delete", null);
}


function paramInput($name)
{
    echo "<input type='text' ";
    echo "name=$name ";
    echo "value='" . getParam("$name") . "' ";
    echo "/>";
}

function textbox($name, $value = null, $size = null, $mandatory = false)
{
    echo "<input type='text' ";
    echo "name=$name ";
    echo "value='$value' ";
    if ($size != null)
        echo "size='$size' ";
    if ($size == null)
    	$size = 20;
    if (array_key_exists('readonly', $_REQUEST))
    	echo "onKeyPress='return false;' ";
    else
    	echo "onKeyPress='return checkLength(event, this.value, $size)' ";
    echo ">";
    hidden("old_$name", $value);
	if ($mandatory) 
		addValidator("validateMandatory('" . tr($name) . "', document.postform.$name)");    
}

function comboBox($name, $data, $selectedValue, $allowNull, $onChange = null)
{
	$onChange = $onChange == null ? '' : "onChange='$onChange'";
    echo "<select name='$name' $onChange ";
    if (array_key_exists('readonly', $_REQUEST))
    	echo "disabled=true ";
    echo ">\n";
    if ($allowNull)
        echo "<option></option>\n";
    for ($j=0; $j < count($data); $j++) {
        $option = $data[$j];
		if (count($option) > 2)
			$label = $option[1].' - '.$option[2];
		else if (count($option) > 1)
			$label = $option[1];
		else
			$label = $option[0];        echo "<option value='$option[0]' ";
        if ($option[0] == $selectedValue)
            echo "selected";
        echo ">$label</option>\n";    }
    echo "</select>\n";
    hidden("old_$name", $selectedValue);
}

function parseDate($datestr)
{
	if (isEmpty($datestr))
		return null;
	if (strlen($datestr) == 10) {
		if (strstr($datestr, '-') === FALSE) {
			return $datestr;
		}
		if (DATE_PATTERN == 'Y-m-d') {
			$year = strtok($datestr, '-');
			$month = strtok('-');
			$day = strtok('-');
			$date = mktime(0, 0, 0, $month, $day, $year);
			return $date;
		}
	}
	return strtotime($datestr);
}

function formatDate($date)
{
    if ($date == null)
        return "";
	$date = 0 + $date;
	return date(DATE_PATTERN, $date);
}

function formatInterval($start, $end)
{
	$str = formatDate($start) . ' - ';
	$end = addTime($end, TYPE_DAYS, -1);
	if (getYear($start) != getYear($end)) {
		$str .= formatDate($end);	
	} else {
		$str .= date('m-d', $end);
	}	
	return $str;
}


function parseTime($hhmm)
{
	$hh = strtok($hhmm, ":.,");
	$mm = strtok(":.,");
	if (isEmpty($mm) && strlen($hhmm) == 4) {
		$hh = substr($hhmm, 0, 2);
		$mm = substr($hhmm, 2, 2);
	} else {
		if ($mm == '5') {
			$mm = 30;
		}
	}
	return $hh*60 + $mm;
}

function formatTime($minutes)
{
	$hh = floor($minutes / 60);
	$mm = $minutes - $hh*60;
	if (strlen($hh) == 1)
		$hh = "0" . $hh;
	if (strlen($mm) == 1)
		$mm = "0" . $mm;
	return $hh . ":" . $mm;
}

function formatDatetime($date)
{
	return formatDate($date) . ' ' . date('H:i', $date);
}

function mkdatetime($date, $minutes, $seconds = 0)
{
    $year = date("Y", $date);
    $month = date("m", $date);
    $day = date("d", $date);
    $hour = floor($minutes / 60);
    $minute = $minutes - $hour*60;
    return mktime($hour, $minute, $seconds, $month, $day, $year);
}

function addDay($date, $diff = 1)
{
    $year = date("Y", $date);
    $month = date("m", $date);
    $day = date("d", $date);
    $hour = date("H", $date);
    $minute = date("i", $date);
    return mktime($hour, $minute, 0, $month, $day+$diff, $year);
}

function addTime($date, $type, $diff=1)
{
	if (isEmpty($date))
		return null;
    $year = date("Y", $date);
    $month = date("m", $date);
    $day = date("d", $date);
    $hour = date("H", $date);
    $minute = date("i", $date);
    if ($type == TYPE_HOURS)
        $hour += $diff;
    else if ($type == TYPE_DAYS)
        $day += $diff;
    else if ($type == TYPE_WEEKS)
        $day += $diff*7;
    else if ($type == TYPE_MONTHS)
        $month += $diff;
    else if ($type == TYPE_YEARS)
        $year += $diff;
    return mktime($hour, $minute, 0, $month, $day, $year);
}

function roundTime($date, $type)
{
    $year = date("Y", $date);
    $month = date("m", $date);
    $day = date("d", $date);
    $hour = date("H", $date);
    $minute = date("i", $date);
    if ($type == TYPE_HOURS)
        $minute = 0;
    else if ($type == TYPE_DAYS) {
        $minute = 0;
        $hour = 0;
    } else if ($type == TYPE_WEEKS) {
        return strtotime("last Sunday", $date);
    } else if ($type == TYPE_MONTHS) {
        $minute = 0;
        $hour = 0;
        $day = 1;
    }
    return mktime($hour, $minute, 0, $month, $day, $year);
}

function getYear($date)
{
	return date("Y", $date);	
}

function getAge($birthday)
{
	list($year,$month,$day) = explode("-",$birthday);	
	$year_diff = date("Y") - $year;
	$month_diff = date("m") - $month;
	$day_diff = date("d") - $day;
	if ($month_diff < 0) $year_diff--;
	elseif (($month_diff==0) && ($day_diff < 0)) $year_diff--;
	return $year_diff;
}


function dayDiff($date1, $date2)
{
    return round(($date1-$date2) / 24 / 3600);
}

function isSearch()
{
	return array_key_exists("search", $_GET);
}

function isSave()
{
	return array_key_exists("save", $_POST);
}

function isDelete()
{
	return array_key_exists("delete", $_POST);
}

function isClear()
{
	return array_key_exists("clear", $_POST);
}

function isNew()
{
	if (!array_key_exists("new", $_POST))
		return false;
	return $_POST['new'] == "1";
}

function newbox()
{
    if (getParam("action") == "new") {
        echo "<input type=hidden name=new value='1'/>";
    }
}

function datebox($id, $value=null)
{
	if (strstr($value, '-') === false)
		$value = formatDate($value);
 	echo "<input type='text' id='$id' name='$id' value='$value' size='12' ";
 	if (array_key_exists('readonly', $_REQUEST))
 		echo "onKeyPress='return false;' ";
 	else 
 		echo "onKeyPress='return onDateKeyPress(event, this);' ";
 	echo ">";
	echo "<img id='$id" . "_button' src='../include/jscalendar/img.gif'/>";
 	if (!array_key_exists('readonly', $_REQUEST)) {
		echo "<script>\n";
	    echo "Calendar.setup(\n";
	    echo "{\n";
	    echo "  inputField: '$id',\n";
	    echo "  ifFormat: '". DATE_PATTERN_MYSQL . "',\n";
	    echo "  button: '$id" . "_button'\n";
	    echo "}\n";
	    echo ");\n";
	    echo "</script>\n";
	    $label = $id;
		addValidator("validateDate('" . tr($label)  ."', document.postform.$id)");
    	hidden("old_$id", $value);		
 	} 	
}

function include_datebox()
{
    echo "<style type='text/css'>@import url(../include/jscalendar/calendar-win2k-1.css);</style>\n";
    echo "<script src='../include/jscalendar/calendar.js'></script>\n";
    echo "<script src='../include/jscalendar/lang/calendar-en.js'></script>\n";
    echo "<script src='../include/jscalendar/calendar-setup.js'></script>\n";
}

function include_common()
{
    echo "<script src='../include/common.js'></script>\n";
}

function timebox($name, $value = null)
{
	echo "<input type=text name='$name' value='$value' size=6 onKeyPress='return isTime(event);' />";
}

function moneyBox($name, $value = null, $size=10, $signed=false)
{
	$signed = $signed ? "true" : "false";
	$length = $size + 3;
	echo "<input type=text name='$name' value='$value' size=$length class=moneybox ";
    if (array_key_exists('readonly', $_REQUEST))
    	echo "onKeyPress='return false;' ";
    else	
		echo "onKeyPress='return onMoneyKeyPress(event, this, $signed, $size);' ";
    echo "> ";
    hidden("old_$name", $value);    
	$label = $name;
	addValidator("validateMoney('" . tr($label)  ."', document.postform.$name, $signed, $size)");
}

function datetimebox($name)
{
    datebox($name . "date");
    echo "&nbsp;";
    timebox($name . "time");
}

function getDateTimeParam($name, $defaultDate = null)
{
    $date = getParam($name . "date");
    if (isEmpty($date))
        $date = $defaultDate;
    return $date . " " . getParam($name . "time");
}

function prepNull($str)
{
    if ($str == null)
        return "null";
    return $str;
}

function formatMoney($amount)
{
	$amount = round($amount, 2);
	return sprintf('%9.2f', $amount);
}

function deleteIcon($href)
{
	echo "<a href='$href'>";
	image("delete.png");
	echo "</a>";
}

function deleteColumn($href)
{
	echo "<td align=center>";
	deleteIcon($href);
	echo "</td>";
}

function hidden($name, $value)
{
	echo "<input type=hidden name='$name' value='$value'/>";
}

class Dummy
{
	function __get($name)
	{
		return null;
	}
}

function checkBox($name, $value, $text = '', $onChange = null, $tooltip = null)
{
	if (!isEmpty($text))
		$text = tr($text);
	$checked = $value == 1 || $value ? 'checked' : '';
	echo "<input type=checkbox name='$name' value='1' $checked ";
    if (array_key_exists('readonly', $_REQUEST))
    	echo "disabled=true ";
    else if ($onChange != null) {
    	echo " onClick='$onChange' ";
    }
    if ($tooltip != null)
    	echo " title='$tooltip' ";
	echo ">$text</input>";
	$value0 = $value ? 1 : '';
	hidden("old_$name", $value0);
}

function numberBox($name, $value, $signed = false, $precision = 10, $scale = 0, $mandatory = false)
{
	$length = $scale + $precision;
	if ($precision > 0)
		$length++;
	$signed = $signed ? 'true' : 'false';
	echo "<input type=text name='$name' value='$value' size=$length class=numberbox ";
	echo "onKeyPress='return onNumberKeyPress(event, this, $signed, $precision, $scale);' ";
	echo ">";
	hidden("old_$name", $value);	
	if ($scale > 0)
		addValidator("validateNumber('" . tr($name)  ."', document.postform.$name, $signed, $precision, $scale)");	
	if ($mandatory) 
		addValidator("validateMandatory('" . tr($name) . "', document.postform.$name)");
}

function tx($functionname, $params)
{
	//try {
		begin();
		$ret = call_user_func_array($functionname, $params);
		commit();
		return $ret;
	//} catch (Exception $e) {
	//	rollback();
	//	throw $e;
	//}
}

function getDescription($value, $list, $default = 'Unknown')
{
	foreach ($list as $row) {
		if ($row[0] == $value)
			return tr($row[1]);
	}
	return $default;
}

function image($name)
{
	echo "<img src='../images/$name' border=0/>";
}

function getUser()
{
	return $_SESSION['username'];

}

function metatag()
{
	echo "<meta http-equiv='Content-Type' content='text/html;charset=" . CHARSET . "'>";
}

function getLanguage()
{
	return $_SESSION['language'];
}

function title($title)
{
	echo "<center>";
	echo "<table cellspacing='0' cellspacing='5'>";
	echo "<tr height='3'/>";
	echo "<tr class='title'>";
	echo "<td class=title>&nbsp;$title</td>";
	echo "</tr>";
	echo "<tr height='5'/>";
	echo "</table>";
	echo "</center>\n";
}

function tr($text)
{
	if (!defined($text))
		return $text;
	return constant($text);
}

function etr($text)
{
	echo tr($text);
}

function login()
{
	$username = null;
	if (array_key_exists('PHP_AUTH_USER', $_SERVER))
		$username = $_SERVER['PHP_AUTH_USER'];
	if (isEmpty($username))
		$username = getParam('user', getParam('username'));
	$pwd = null;
	if (array_key_exists('PHP_AUTH_PW', $_SERVER))
		$pwd = $_SERVER['PHP_AUTH_PW'];
	if (isEmpty($pwd))
		$pwd = getParam('pwd', getParam('password'));
	include_once('../sql/upgrade.php');
	upgrade();
	$user = find("select language
                  from user
	              where username='" . $username . "'
					and password='" . $pwd . "'");
	if ($user == null) {
		showLoginDialog(tr("Invalid username/password"));
		return false;
	}
	$_SESSION['language'] = $user->language;
	$_SESSION['username'] = $username;
	$_SESSION['dbname'] = getDBName();
	$_SESSION['pwd'] = $pwd;
	$_SESSION['authenticated'] = getRealm();
	$remoteHost = $_SERVER['REMOTE_ADDR'];
	sql("insert into session (username, logintime, remote_host)
	     values ('$username', now(), '$remoteHost')");
	return true;
}

function logout()
{
	unset($_SESSION['authenticated']);
	unset($_SESSION['username']);
	unset($_SESSION['language']);
	unset($_SESSION['dbname']);
}

function runScript($filename)
{
	$fh = fopen($filename, 'r');
	$script = fread($fh, filesize($filename));
	fclose($fh);
	$sql = strtok($script, ";");
	while ($sql !== false) {
		if (strlen(trim($sql)) > 0)
			sql($sql);
		$sql = strtok(";");
	}
}

function getMonthStepperDate()
{
	$year = getParam("year");
	if (isEmpty($year))
		$year = date("Y");
	$month = getParam("month");
	if (isEmpty($month))
		$month = date("m");
	if (!isEmpty(getParam("prev")))
		$month--;
	if (!isEmpty(getParam("next")))
		$month++;
	$date = mktime(0,0,0, $month, 1, $year);
	return $date;
}

function monthStepper($date)
{
	echo "<center>";
	echo "<table>";
	echo "<tr>";
	echo "<td><input type='submit' name='prev' value=' < '/></td>";
	echo "<td>" . date("Y M", $date) . "</td>";
	echo "<td><input type='submit' name='next' value=' > '/></td>";
	echo "</tr>";
	echo "</table>";
	echo "</center>";
	$year = date("Y", $date);
	$month = date("m", $date);
	hidden('year', $year);
	hidden('month', $month);
}

function getYearStepperDate()
{
	$year = getParam("year");
	if (isEmpty($year))
		$year = date("Y");
	if (!isEmpty(getParam("prev")))
		$year--;
	if (!isEmpty(getParam("next")))
		$year++;
	$date = mktime(0,0,0, 1, 1, $year);
	return $date;
}

function yearStepper($date)
{
	echo "<center>";
	echo "<table>";
	echo "<tr>";
	echo "<td><input type='submit' name='prev' value=' < '/></td>";
	echo "<td>" . date("Y", $date) . "</td>";
	echo "<td><input type='submit' name='next' value=' > '/></td>";
	echo "</tr>";
	echo "</table>";
	echo "</center>";
	$year = date("Y", $date);
	hidden('year', $year);
}

function getSessionAttribute($name)
{
	if (array_key_exists($name, $_SESSION)) {
		return $_SESSION[$name];
	}
	return null;
}

function getRealm()
{
	if (defined('DBNAME')) {
		$dbname = DBNAME;
	} else {
		$dbname = 'therp';
		if (array_key_exists('dbname', $_SESSION)) {
			$dbname = $_SESSION['dbname'];
		}
		$dbname = getParam('dbname', $dbname);
	}
	return $dbname;
}

function defineIfNotDefined($name, $value)
{
	if (defined($name))
		return;
	define($name, $value);
}

function showLoginDialog($mess = null)
{
	//header("WWW-Authenticate: Basic realm=" . getRealm());
	//header('HTTP/1.0 401 Unauthorized');
	$_SESSION['ORG_SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'];
	if ($mess != null)
		$_REQUEST['login_mess'] = $mess;
	include("../common/login.php");
	//header("Location: ../common/login.php");
	die;

}

function authenticate()
{
	if (defined('public'))
		return;
	if (strstr($_SERVER['SCRIPT_NAME'], 'login.php'))
		return;
	if (array_key_exists('logout', $_GET)) {
		logout();
		showLoginDialog();
		return;
	}
	
	$dbname = getDBName();
	if (!isEmpty($dbname) && getSessionAttribute('dbname') == $dbname)
		return;
	$userSupplied = isset($_SERVER['PHP_AUTH_USER']) ||
	                !isEmpty(getParam('user')) ||
	                !isEmpty(getParam('username'));
	if (!$userSupplied) {
		showLoginDialog();
		return;
	}

	if (!login())
		die;
}

function getCodebase()
{
	if (defined('CODEBASE'))
		return CODEBASE;
	return "http://localhost/therp";
}

function getError($str)
{
	if (substr($str, 0, 6) == "ERROR:")
		return substr($str, 6);
	return null;
}

function toggleClass($class)
{
	if ($class == 'odd')
		return 'even';
	return 'odd';
}

function prepDate($str)
{
	if (isEmpty($str))
		return "null";
	$str = parseDate($str);
	return "from_unixtime($str)";
}

function prepDateParam($param)
{
	return prepDate(getParam($param));
}

function prepNumber($str)
{
	if (isEmpty($str))
		return "null";
	$str = str_replace(',', '.', $str);
	return $str;
}

function prepNumberParam($param)
{
	return prepNumber(getParam($param));
}

function prepMoney($str)
{
	return prepNumber($str);
}

function prepMoneyParam($param)
{
	return prepMoney(getParam($param));
}

function prepStringParam($param)
{
	$value = getParam($param);
	if (isEmpty($value))
		return "null";
	return "'$value'";	
}

function th($header, $href = null, $width = null)
{
	if ($width != null)
		$width = "width='$width%' ";
	echo "<th $width>";
	if ($href != null)
		echo "<a href='$href' class=th>";
	echo tr($header);
	if ($href != null)
		echo "</a>";
	echo "</th>";
}

function prepParam($name)
{
	return prepNull(getParam($name));
}

function getDBName()
{
	if (defined('DBNAME')) {
		if (DBNAME == 'alias') {
			$path = $_SERVER['PHP_SELF'];
			$alias = strtok($path, '/');
			return $alias;
		} else if (DBNAME == 'subdomain') {
			$path = $_SERVER['SERVER_NAME'];
			$subdomain = strtok($path, '.');
			return $subdomain;
		} else if (DBNAME == 'param') {
			if (array_key_exists('dbname', $_SESSION))
				$dbname = $_SESSION['dbname'];
			else
				$dbname = getParam('dbname');
			return $dbname;
		}
		return DBNAME;
	}
	return "real";
}

function addValidator($validator)
{
	$validators = array();
	if (array_key_exists('validators', $_REQUEST)) {
		$validators = $_REQUEST['validators'];
	}
	$validators[] = $validator;
	$_REQUEST['validators'] = $validators;
}



?>
