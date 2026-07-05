<?php
	include('include.php');
	include('employee.inc');

	$employeeid = getCurrentEmployee();	
	$periodid = getCurrentPeriod();

	$new = true;
	$attributes = null;
	$teams = null;
	if (isSave()) {
		$givenname = getParam('givenname');
		$surname = getParam('surname');
		$bank_account = getParam('bank_account');
		$street_address = getParam('street_address');
		$zipcode = getParam('zipcode');
		$city = getParam('city');
		if (isNew()) {
		} else {
            $updateSQL =
    			"update employee set
    			    givenname='$givenname',
    			    surname='$surname',
    			    bank_account='$bank_account',
					street_address='$street_address',
					zipcode='$zipcode',
					city='$city'
                where employeeid=$employeeid";
    		sql($updateSQL);
		}

	}

	$del_teamid = getParam('del_teamid');
	if (!isEmpty($del_teamid)) {
		sql("delete from emp_team where employeeid=$employeeid and teamid=$del_teamid");
	}

	$teamid_new = getParam('teamid_new');
	if (!isEmpty($teamid_new)) {
		sql("insert into emp_team (employeeid, teamid) values ($employeeid, $teamid_new)");
	}

	$title = "Edit ";

	$emp = new Dummy();
	if (!isEmpty($employeeid)) {
	    $selectSQL =
  		"select e.employeeid,
		       givenname,
		       surname,
		       active,
		       bank_account,
			   street_address,
			   zipcode,
			   city
		from employee e
		where e.employeeid=$employeeid
		";
		$emp = find($selectSQL, true);
		if ($emp != null) {
			$new = false;

			$teams = query("select et.teamid, description
			                from emp_team et
							join team t on t.teamid=et.teamid
							where employeeid=$employeeid");
		}
	}

	$allTeams = rs2array(query("select teamid, description from team"));
?>

<?php head("Settings") ?>

<body>
<?php top("employees.php", "Settings") ?>

<form name=form1 action="selfservice_settings.php" method="POST">
<table>

<tr><td class=label><?php echo tr("Givenname") ?>:</td><td><input type="text" name="givenname" value="<?php echo $emp->givenname ?>"/></td></tr>
<tr><td class=label><?php echo tr("Surname") ?>:</td><td><input type="text" name="surname" value="<?php echo $emp->surname ?>"/></td></tr>
<?php

if ($teams != null) {
	echo "<tr>";
	echo "<td class=label>" . tr("Teams") . ":</td>";
	echo "<td>";
	while ($row = fetch($teams)) {
		$href = "selfservice_settings.php?del_teamid=$row->teamid";
		echo $row->description . "&nbsp;";
		deleteIcon($href);
		echo ",&nbsp;";
	}
	comboBox('teamid_new', $allTeams, null, true);
	echo "</td>";
	echo "</tr>";
}
?>
<tr><td class=label><?php echo tr("Bank account") ?>:</td><td><input type="text" name="bank_account" value="<?php echo $emp->bank_account ?>"/></td></tr>


<tr><td class=label><?php etr("Street") ?>:</td><td><?php textbox('street_address', $emp->street_address, 60) ?></td></tr>
<tr><td class=label><?php etr("Zipcode") ?>:</td><td><?php textbox('zipcode', $emp->zipcode, 15) ?></td></tr>
<tr><td class=label><?php etr("City") ?>:</td><td><?php textbox('city', $emp->city, 30) ?></td></tr>
</table>

<br/>
<?php saveButton() ?>
</form>
<?php bottom() ?>

</body>
