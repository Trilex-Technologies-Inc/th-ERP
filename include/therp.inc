<?php

include('frebe.inc');

define('PERMISSION_ADMINISTRATE_USERS', 1);

function moduleCombo($module)
{
	echo "\n<script>\n";
	echo "function onModuleChange() {\n";
	echo "  var module = document.getElementById('module').value;\n";
	echo "  if (module == 'payroll') document.location.href='../payroll/index.php';\n";
	echo "  else if (module == 'erp') document.location.href='../erp/index.php';\n";
	echo "  else if (module == 'common') document.location.href='../common/users.php';\n";
	echo "  else if (module == 'selfservice') document.location.href='../payroll/inout.php';\n";
	echo "  else if (module == 'accounting') document.location.href='../accounting/register_transaction.php';\n";
	echo "  else if (module == 'project') document.location.href='../project/projects.php';\n";
	echo "}\n";
	echo "</script>\n";
	echo "<select id='module' onChange='onModuleChange()' style='background: #CCCCE5; font-size: 8pt'>\n";
	$selected = $module == 'erp' ? 'selected' : '';
	echo "<option value='erp' $selected>" . tr("ERP") . "</option>\n";	
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

function styleSheet($file = 'therp.css')
{
	echo "<LINK REL=StyleSheet HREF='../include/$file' TYPE='text/css'>";
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
		echo tr("Unauthorized") . "!";
		die;
	}
}
?>