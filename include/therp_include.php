<?php

set_error_handler("therpErrorHandler");

include('standard_include.php');

define('PERMISSION_ADMINISTRATE_USERS', 1);

define('RECUR_TYPE_DAILY', 1);
define('RECUR_TYPE_WEEKLY', 2);
define('RECUR_TYPE_MONTHLY', 3);

define('GROUPID_ASSETS', 1);
define('GROUPID_LIABILITIES', 2);
define('GROUPID_REVENUES', 3);
define('GROUPID_EXPENSES', 4);
define('GROUPID_PURCHASE_DEBIT', 5);
define('GROUPID_BANK_ACCOUNTS', 6);
define('GROUPID_FAVORITES', 7);

define('PRODUCTID_UNSPECIFIED', 1);
define('PRODUCTID_ROUNDING', 2);

define('CATEGORYID_ROUNDING', 2);

define('CUSTOMERID_CASH', 1);

define('STATE_RECEIVABLES_CREATED', 1);
define('STATE_RECEIVABLES_SENT', 2);

define('PERMISSIONID_SELL', 6);
define('PERMISSIONID_PURCHASE', 7);
define('PERMISSIONID_RECEIVE_GOODS', 8);
define('PERMISSIONID_MANAGE_PRODUCTS', 9);

define('INPUT_TYPE_AMOUNT', 0);
define('INPUT_TYPE_UNITS', 3);
define('INPUT_TYPE_MINUTES', 1);
define('INPUT_TYPE_DAYS', 2);

define('ATTR_OBJECT_EMPLOYEE', 1);
define('ATTR_OBJECT_PRODUCT', 2);
define('ATTR_OBJECT_COMPANY', 3);

define('ATTRIBUTE_TYPE_NUMERIC', 1);
define('ATTRIBUTE_TYPE_BOOLEAN', 2);
define('ATTRIBUTE_TYPE_CHOICE', 3);


function therpExceptionHandler($e)
{
	echo "<h1>Technical error</h1>";
	echo "<pre>";
	echo $e;
	echo "</pre>";
	//try {
		$ex = str_replace('\"', '', $e);
		$sql ="insert into logger (loggtext, loggtime, username)
		     values (\"$ex\", now(), '" . getUser() . "')";
		echo $sql;
		sql($sql);
		die;
	//} catch (Exceptione $e2) {
		//echo $e2;
	//}
}

//set_exception_handler("therpExceptionHandler");

function therpErrorHandler($errno, $errstr)
{
	if ($errno == E_NOTICE || $errno == E_STRICT)
		return;
	$isError = ($errno == E_USER_ERROR || $errno == E_CORE_ERROR
	    || $errno == E_COMPILE_ERROR || $errno == E_USER_ERROR);
	if ($isError) {
		echo "<h1>Technical error</h1>";
		echo "<pre>";
		echo $errstr;
		echo "</pre>";
	}
	$ex = $errno . ": " . str_replace('\"', '', $errstr);
	$sql ="insert into logger (loggtext, loggtime, username)
	     values (\"$ex\", now(), '" . getUser() . "')";
	sql($sql);
	if ($isError)
		die;
}

function serverErrorHandler($errno, $errstr)
{
	if ($errno == E_NOTICE || $errno == E_STRICT)
		return;
	$isError = ($errno == E_USER_ERROR || $errno == E_CORE_ERROR
	    || $errno == E_COMPILE_ERROR || $errno == E_USER_ERROR);
	if ($isError) {
		echo "ERROR:$errstr";
	}
	if ($isError)
		die;
}

function moduleCombo($module)
{
	echo "\n<script>\n";
	echo "function onModuleChange() {\n";
	echo "  var module = document.getElementById('module').value;\n";
	echo "  if (module == 'payroll') document.location.href='../payroll/index.php';\n";
	echo "  else if (module == 'erp') document.location.href='../erp/index.php';\n";
	echo "  else if (module == 'common') document.location.href='../common/users.php';\n";
	echo "  else if (module == 'selfservice') document.location.href='../payroll/inout.php';\n";
	echo "  else if (module == 'accounting') document.location.href='../accounting/index.php';\n";
	echo "  else if (module == 'project') document.location.href='../project/projects.php';\n";
	echo "}\n";
	echo "</script>\n";
	echo "<select id='module' onChange='onModuleChange()' style='background: #CCCCE5; font-size: 8pt'>\n";
	$selected = $module == 'erp' ? 'selected' : '';
	echo "<option value='erp' $selected>" . tr("Order/Stock") . "</option>\n";
	$selected = $module == 'payroll' ? 'selected' : '';
	echo "<option value='payroll' $selected>" . tr("Payroll") . "</option>\n";
	$selected = $module == 'selfservice' ? 'selected' : '';
	echo "<option value='selfservice' $selected>" . tr("Self-service") . "</option>\n";
	$selected = $module == 'project' ? 'selected' : '';
	echo "<option value='project' $selected>" . tr("Project") . "</option>\n";
	$selected = $module == 'accounting' ? 'selected' : '';
	echo "<option value='accounting' $selected>" . tr("General ledger") . "</option>\n";
	$selected = $module == 'common' ? 'selected' : '';
	echo "<option value='common' $selected>" . tr("Common") . "</option>\n";
	echo "</select>\n";
}

function styleSheet($file = 'therp')
{
	$suffix = '';
	if (getLanguage() == 'th' && $file == 'therp')
		$suffix = '_th';
	echo "<LINK REL=StyleSheet HREF='../include/$file$suffix.css' TYPE='text/css'>";
}

function hasPermission($permissionid)
{
	$count = findValue("select count(*)
	                    from user u
						join user_group ur on ur.username=u.username
						join usergroup_permission rp on rp.groupid=ur.groupid and permissionid=$permissionid
						where u.username='" . getUser() . "'");
	return $count > 0;
}

function checkPermission($permissionid)
{
	if (!hasPermission($permissionid)) {
		$description = findValue("
		select description from permission
		where permissionid=$permissionid");
		echo tr("Unauthorized, you need this permission") . ': ' . $description;
		die;
	}
}

function menupage_begin()
{
	echo "<br>";
	echo "<div class=border>";
	echo "<center>";
	echo "<table><tr><td>";
}

function menupage_end()
{
	echo "</td></tr></table>";
	echo "</center>";
	echo "</div>";
	bottom();
}

function top($currentHRef, $title, $path = null, $help = "help")
{
	menubar($currentHRef);
	if ($path != null)
		title($path);
	else
		echo "<br>";
}

function top0($module = null)
{
	echo "<table cellpadding=0 cellspacing=0 width='100%' border=0>";
	echo "<tr>";
	echo "<td width=20><img src='../images/tl.png'></td>";
	echo "<td rowspan=2 bgColor='#CCCCE5'>";
	$title = tr("Switch module");
	$thERP = '$thERP';
	$thERP = findValue("select companyname from companyinfo");
	echo "<span style='font-size: 14pt;'>";
	echo "<a class=logo href='../common/modules.php' title='$title'>$thERP</a></span>";
	if ($module != null) {
		echo "<span style='font-size: 12pt;'>&nbsp;-&nbsp;";
		$onMouseOver = "document.getElementById(\"down\").src=\"../images/down_hover.gif\"";
		$onMouseOut = "document.getElementById(\"down\").src=\"../images/down.gif\"";
		echo "<a class=logo href='../common/modules.php' title='$title' ";
		echo "onMouseOver='$onMouseOver' onMouseOut='$onMouseOut'>";
		echo tr($module);
		echo "&nbsp;<img id=down src='../images/down.gif' border=0 style='position: relative; top: -2'>";
		echo "</a>";
		echo "</span>";
	}
	echo "</td>";
	echo "<td align=right valign=bottom rowspan=2 bgColor='#CCCCE5' style='padding: 3'>";
	echo "<span class=username>";
	$href = '../payroll/selfservice_settings.php';
	echo tr("User") . ": <a href='$href'>" . getUser() . "</a> | <a href='../common/modules.php?logout=true' class=logo>";
	echo tr("Logout") . "</a>";
	echo "</span>\n";
	echo "</td>";
	echo "<td width=20><img src='../images/tr.png'></td>";
	echo "</tr>";
	echo "<tr bgColor='#CCCCE5'>";
	echo "<td>&nbsp;</td>";
	echo "<td>&nbsp;</td>";
	echo "</tr>";
	echo "</table>\n";
}

function bottom()
{
	echo "<br>";
	echo "<table cellpadding=0 cellspacing=0 width='100%' border=0>";
	echo "<tr>";
	echo "<td width=20><img src='../images/bl.png'></td>";
	echo "<td bgColor='#CCCCE5' align=center><a href='http://www.therpsoft.com' class=logo>www.therpsoft.com</a></td>";
	echo "<td width=20><img src='../images/br.png'></td>";
	echo "</tr>";
	echo "</table>";
}

function menu($href, $text, $width, $hasNext, $currentHref)
{
	$current = $href == $currentHref;
	$class = $current ? 'menubar_current' : 'menubar';
	echo "<td width='$width%' align='center'><a class=$class href='$href'>" . tr($text) . "</a></td>\n";
	if ($hasNext)
		echo "<td>|</td>";

}

function showUpgrade()
{
	if (isset($_REQUEST['upgrademess'])) {
		$mess = $_REQUEST['upgrademess'];
		echo "<center><p>$mess</p></center>";
	}
}

function cancel_transaction($transid, $narrative = null)
{
	if ($narrative == null)
		$narrative = tr("Cancel transaction ") . $transid;
	sql("insert into transaction (transtime, narrative, createdtime)
		 values (now(), '$narrative', now())");
	$transid2 = insert_id();
	$parts = query("select accountid, amount from transaction_part where transactionid=$transid");
	while ($row = fetch($parts)) {
		$amount = (-1) * $row->amount;
		sql("insert into transaction_part (transactionid, accountid, amount)
			 values ($transid2, $row->accountid, $amount)");
	}
	sql("update transaction set cancel_transid=$transid2 where transactionid=$transid");
	return $transid2;
}

function move_stock($productid, $diff, $narrative, $accountid, $transid = null)
{
	$standardCost = findValue("select purchase_price from product where productid=$productid");
	$amount = $diff * $standardCost;
	if ($transid == null) {
		sql("insert into transaction (transtime, narrative, createdtime) values (now(), '$narrative', now())");
		$transid = insert_id();
	}
	$finished_goods_accountid = findValue("select finished_goods from accountconf");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $finished_goods_accountid, $amount)");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $accountid, (-1) * $amount)");
	sql("insert into stockmove (productid, diff, narrative, transactionid)
	     values ($productid, $diff, '$narrative', $transid)");
}

function getCreditLength($supplierid)
{
	$creditLength = findValue("select credit_length from supplier where supplierid=$supplierid");
	if (isEmpty($creditLength))
		$creditLength = findValue("select credit_length from settings");
	return $creditLength;
}

function head_begin($title)
{
	echo "<head>";
	metatag();
	headTitle($title);
	styleSheet();
	include_common();
	include_datebox();
	echo "<script src='../include/AjaxRequest.js'></script>";
}

function head_end()
{
	echo "</head>";
}

function headTitle($text)
{
	$text = tr($text);
	echo "<title>thERP - $text</title>";
}

function head($title)
{
	head_begin($title);
	head_end();
}

function oscommerce()
{
	$rs = query("show tables like 'products'");
	return (num_rows($rs) > 0);	
}

function getCurrentPeriod()
{
	$sql = "
	select periodid
	from payperiod where isnull(locked) or locked=0
	order by payperiod.starttime limit 1";
	return findValue($sql);
}

?>