<?php
	include('include.php');
	include('employee.inc');
	
	checkPermission(PERMISSION_ADMINISTRATE_EMPLOYEES);

	$employeeid = getParam('employeeid');
	$periodid = getCurrentPeriod();
	$policyid = getParam('policyid');

	$new = true;
	$attributes = null;
	$teams = null;
	if (isSave()) {
		$periodstart = findValue("select unix_timestamp(starttime) 
		                          from payperiod where periodid=$periodid");
		$givenname = getParam('givenname');
		$surname = getParam('surname');
		$policyid = getParam('policyid');
		$bank_account = getParam('bank_account');
		$active = getParam('active');
		$street_address = getParam('street_address');
		$zipcode = getParam('zipcode');
		$city = getParam('city');
		$birthdate = prepStringParam('birthdate');
		$username = getParam('username');
		if (isNew()) {
			$sql = "insert into employee (givenname, surname, bank_account, 
			                              active, street_address, zipcode, city, policyid,
			                              birthdate)
			        values ('$givenname','$surname', '$bank_account',
					        1, '$street_address', '$zipcode', '$city', $policyid,
					        $birthdate)";
			sql($sql);
			$employeeid = insert_id();
		} else {
    		$employeeid = getParam('employeeid');
            $updateSQL =
    			"update employee set
    			    givenname='$givenname',
    			    surname='$surname',
    			    bank_account='$bank_account',
    			    active=$active,
					street_address='$street_address',
					zipcode='$zipcode',
					city='$city',
					birthdate=$birthdate
                where employeeid=$employeeid";
    		sql($updateSQL);
		}
		if (!isEmpty($username)) {
			sql("update user set employeeid=$employeeid where username='$username'");
		}
		if ($policyid != getParam('old_policyid')) {
			sql("update employee set policyid=$policyid where employeeid=$employeeid");
		}

		$count = getParam('count');
		$i = 0;
		while ($i < $count) {
			$attributeid = getParam("attributeid_$i");
			$value = prepNull(getParam("value_$i"));
			$old_value = prepNull(getParam("old_value_$i"));
			if ($value != $old_value) {
				sql("insert into emp_attribute 
				     (employeeid, attributeid, fromtime, regtime, value)
					 values ($employeeid, $attributeid, from_unixtime($periodstart), 
					 now(), $value)");
			}
			$i++;
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

	$title = "Add";
	if (!$new)
		$title = "Edit ";

	$emp = new Dummy();
	$old_policyid = null;
	if (!isEmpty($employeeid)) {
	    $selectSQL =
  		"select e.employeeid,
		       givenname,
		       surname,
		       active,
		       bank_account,
			   street_address,
			   zipcode,
			   city,
			   username,
			   birthdate
		from employee e
		left outer join user u on u.employeeid=e.employeeid
		where e.employeeid=$employeeid
		";
		$emp = find($selectSQL, true);
		if ($emp != null) {
			$new = false;
			$old_policyid = getPolicy($employeeid, $periodid);
			if (isEmpty($policyid))
				$policyid = $old_policyid;
		    $sql =
	  		"select pa.policyid,
	  		   pa.attributeid,
	  		   ea.value,
	  		   description
			from policy_attribute pa
			join attribute a on a.attributeid=pa.attributeid
			left outer join policy_attribute_value pav
			on pav.policyid=pa.policyid and pav.attributeid=pa.attributeid
			and pav.regtime = (select max(regtime) from policy_attribute_value pav2
			                        where pav2.policyid=pav.policyid and pav2.attributeid=pav.attributeid
			                        and pav2.fromtime<=now())	
			left outer join emp_attribute ea
			on pa.attributeid=ea.attributeid and ea.employeeid=$employeeid
			and ea.regtime = (select max(regtime) from emp_attribute ea2
			                    where ea2.employeeid=ea.employeeid
			                    and ea2.attributeid=ea.attributeid
			                    and ea2.fromtime<=now())
			where pa.policyid=$policyid
			and pav.value is null
			and pa.tabid=" . TABID_GENERAL . "
			";
			$attributes = query($sql);

			$teams = query("select et.teamid, description
			                from emp_team et
							join team t on t.teamid=et.teamid
							where employeeid=$employeeid");
		}
	}

	$policies = rs2array(query("select p.policyid, description
	                            from policy p
	                            join policy_description d on d.policyid=p.policyid and language='" . getLanguage() . "'"));
	$allTeams = rs2array(query("select teamid, description from team"));
?>

<?php head_begin('Employee') ?>
<script>
function onPolicyChange()
{
	<?php
	echo "document.location.href=\"employee_detail.php?employeeid=$employeeid";
	echo "&policyid=\" + form1.policyid.value;\n";
	?>
}
</script>
<?php head_end() ?>

<body>
<?php menubar("employees.php", "hiring") ?>
<?php title("$emp->givenname $emp->surname") ?>

	<div id="header">
	<?php buildTabs($employeeid, 'general') ?>
	</div>
	<div id="main">
		<div id="contents">



<form name=form1 action="employee_detail.php" method="POST">
<?php hidden('employeeid', $employeeid) ?>
<table>
<tr><td class=label>Id:</td>
<td>
<?php
	if (!$new) {
		echo $employeeid;
		echo "<input type='hidden' name='employeeid' value='$employeeid'/>";
	}
?>
</td>

<tr><td class=label><?php echo tr("Givenname") ?>:</td><td><input type="text" name="givenname" value="<?php echo $emp->givenname ?>"/></td></tr>
<tr><td class=label><?php echo tr("Surname") ?>:</td><td><input type="text" name="surname" value="<?php echo $emp->surname ?>"/></td></tr>
<tr><td class=label><?php echo tr("Birth date") ?>:</td><td><?php datebox('birthdate', $emp->birthdate) ?></td></tr>
<tr><td class=label><?php echo tr("Policy") ?>:</td><td><?php comboBox("policyid", $policies, $policyid, false, 'onPolicyChange()') ?></td></tr>
<?php hidden('old_policyid', $old_policyid) ?>
<?php
if ($attributes != null) {
	$i = 0;
	while ($row = fetch($attributes)) {
		echo "<tr>";
		echo "<input type=hidden name='attributeid_$i' value='$row->attributeid'/>";
		echo "<td class=label>". formatCase($row->description) . ":</td>";
		echo "<td>";
		numberBox("value_$i", $row->value);
		echo "&nbsp;<a href='employee_history.php?employeeid=$employeeid&attributeid=$row->attributeid'>";
		image('history.gif');
		echo "</td>";
		echo "<input type=hidden name='old_value_$i' value='$row->value'/>";
		echo "</tr>";
		$i++;
	}
	echo "<input type=hidden name=count value='$i'/>";
}

if ($teams != null) {
	echo "<tr>";
	echo "<td class=label>" . tr("Teams") . ":</td>";
	echo "<td>";
	while ($row = fetch($teams)) {
		$href = "employee_detail.php?employeeid=$employeeid&del_teamid=$row->teamid";
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


<?php if (!$new) { ?>
<tr>
<td class=label><?php echo tr("Active") ?>:</td>
<td><?php checkbox('active', $emp->active) ?></td>
</tr>
<?php } ?>
<tr><td class=label><?php etr("Street") ?>:</td><td><?php textbox('street_address', $emp->street_address, 60) ?></td></tr>
<tr><td class=label><?php etr("Zipcode") ?>:</td><td><?php textbox('zipcode', $emp->zipcode, 15) ?></td></tr>
<tr><td class=label><?php etr("City") ?>:</td><td><?php textbox('city', $emp->city, 30) ?></td></tr>
<tr><td class=label><?php etr("Username") ?>:</td><td><?php textbox('username', $emp->username, 30) ?></td></tr>
</table>

<br/>
<?php saveButton() ?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
		</div>
	</div>

<?php bottom() ?>
</body>
