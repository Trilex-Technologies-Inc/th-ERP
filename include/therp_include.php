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
	$sql = "insert into logger (loggtext, loggtime, username)
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
	$sql = "insert into logger (loggtext, loggtime, username)
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
	echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
	echo "<link href='../include/bootstrap.min.css' rel='stylesheet'>";
	echo "<LINK REL=StyleSheet HREF='../include/$file$suffix.css' TYPE='text/css'>";
	echo "<link rel='stylesheet' href='../include/therp_modern.css'>";
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
	echo "<main class='container-fluid py-4'>";
	echo "<div class='card shadow-sm border-0'><div class='card-body'>";
}

function menupage_end()
{
	echo "</div></div></main>";
	bottom();
}

function top($currentHRef, $title, $path = null, $help = "help")
{
	menubar($currentHRef);
	if ($path != null)
		title($path);
	else
		echo "<div class='mb-3'></div>";
}

function top0($module = null)
{
	$title = tr("Switch module");
	$company = findValue("select companyname from companyinfo");
	if (isEmpty($company))
		$company = '$thERP';

	echo "<header class='app-header navbar navbar-expand-lg bg-white px-3 py-2'>";
	echo "<div class='container-fluid px-0'>";
	echo "<button class='sidebar-toggle' type='button' aria-label='" . tr("Toggle navigation") . "' aria-expanded='false'><span></span><span></span><span></span></button>";
	echo "<a class='navbar-brand fw-bold text-primary' href='../common/modules.php' title='$title'><span class='brand-mark'>ERP</span><span class='brand-name'>$company</span></a>";
	if ($module != null) {
		echo "<span class='badge text-bg-light border me-auto'>" . tr($module) . "</span>";
	} else {
		echo "<span class='me-auto'></span>";
	}
	$href = '../payroll/selfservice_settings.php';
	echo "<div class='d-flex align-items-center gap-2 small'>";
	echo "<span class='user-avatar' aria-hidden='true'>" . strtoupper(substr(getUser(), 0, 1)) . "</span>";
	echo "<span class='text-secondary user-label'>" . tr("User") . ": <a class='fw-semibold' href='$href'>" . getUser() . "</a></span>";
	echo "<a class='btn btn-outline-secondary btn-sm logout-link' href='../common/modules.php?logout=true'>" . tr("Logout") . "</a>";
	echo "</div></div></header>\n";
}

function bottom()
{
	echo "<footer class='app-footer container-fluid py-4 mt-4 border-top text-center text-secondary small'>";
	echo "<a href='http://www.therpsoft.com' class='text-decoration-none'>www.therpsoft.com</a>";
	echo "</footer>";
	echo "<script src='../include/bootstrap.bundle.min.js'></script>";
	echo "<script src='../include/therp_modern.js'></script>";
}

function menu($href, $text, $width, $hasNext, $currentHref)
{
	$current = $href == $currentHref;
	$class = $current ? 'menubar_current' : 'menubar';
	$icons = array(
		'Products' => '&#9638;', 'Purchase' => '&#128722;', 'Stock move' => '&#8644;',
		'Configuration' => '&#9881;', 'Help' => '?', 'Employees' => '&#9787;',
		'Reporting' => '&#9636;', 'End of period' => '&#10003;', 'Security' => '&#128274;',
		'Languages' => 'A', 'Company info' => '&#9635;', 'Sales' => '$',
		'Customers' => '&#9787;', 'Transactions' => '&#8644;', 'Accounts' => '&#9636;'
	);
	$icon = array_key_exists($text, $icons) ? $icons[$text] : '&#9679;';
	echo "<td class='app-nav-item' style='width:$width%'><a class='$class' href='$href'><span class='nav-icon' aria-hidden='true'>$icon</span><span>" . tr($text) . "</span></a></td>\n";
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
