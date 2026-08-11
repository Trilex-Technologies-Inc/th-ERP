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
		$active = getParam('active', 0);
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
<?php title($new ? tr("Create employee") : htmlspecialchars(trim($emp->givenname . " " . $emp->surname))) ?>

<main class="employee-detail-page">
	<header class="employee-detail-intro">
		<a class="employee-detail-back" href="employees.php" aria-label="<?php etr("Employees") ?>">&#8592;</a>
		<div class="employee-detail-avatar" aria-hidden="true"><?php echo $new ? '+' : htmlspecialchars(strtoupper(substr($emp->givenname, 0, 1))) ?></div>
		<div class="employee-detail-heading"><span><?php etr("Payroll employee") ?></span><h1><?php echo $new ? tr("Create employee") : htmlspecialchars(trim($emp->givenname . " " . $emp->surname)) ?></h1><p><?php etr("Manage personal details, payroll policy, teams, and account information.") ?></p></div>
		<?php if (!$new) { ?><div class="employee-detail-meta"><span>#<?php echo htmlspecialchars($employeeid) ?></span><strong class="<?php echo $emp->active ? 'is-active' : 'is-inactive' ?>"><?php echo $emp->active ? tr("Active") : tr("Inactive") ?></strong></div><?php } ?>
	</header>

	<?php if (!$new) { ?><div class="employee-detail-tabs"><?php buildTabs($employeeid, 'general') ?></div><?php } ?>

	<form name="form1" action="employee_detail.php" method="POST">
	<?php hidden('employeeid', $employeeid); hidden('old_policyid', $old_policyid); ?>
	<section class="employee-detail-card card border-0 shadow-sm">
		<div class="card-header bg-white employee-detail-card-header"><div><span><?php etr("Profile") ?></span><h2><?php etr("Personal information") ?></h2></div></div>
		<div class="card-body"><div class="row g-3">
			<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="givenname"><?php etr("Givenname") ?></label><input id="givenname" type="text" name="givenname" value="<?php echo htmlspecialchars($emp->givenname) ?>" /></div>
			<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="surname"><?php etr("Surname") ?></label><input id="surname" type="text" name="surname" value="<?php echo htmlspecialchars($emp->surname) ?>" /></div>
			<div class="col-12 col-md-4"><label class="form-label fw-semibold" for="birthdate"><?php etr("Birth date") ?></label><?php datebox('birthdate', $emp->birthdate) ?></div>
			<div class="col-12 col-md-8"><label class="form-label fw-semibold" for="username"><?php etr("Username") ?></label><?php textbox('username', $emp->username, 30) ?></div>
			<?php if (!$new) { ?><div class="col-12"><div class="employee-active-toggle"><?php checkbox('active', $emp->active) ?><label for="active"><?php etr("Active employee") ?></label></div></div><?php } ?>
		</div></div>
	</section>

	<section class="employee-detail-card card border-0 shadow-sm">
		<div class="card-header bg-white employee-detail-card-header"><div><span><?php etr("Employment") ?></span><h2><?php etr("Policy and teams") ?></h2></div></div>
		<div class="card-body">
			<div class="row g-3"><div class="col-12 col-md-6"><label class="form-label fw-semibold" for="policyid"><?php etr("Policy") ?></label><?php comboBox("policyid", $policies, $policyid, false, 'onPolicyChange()') ?></div></div>
			<?php if ($attributes != null) { ?><div class="employee-attribute-grid">
			<?php $i = 0; while ($row = fetch($attributes)) { hidden("attributeid_$i", $row->attributeid); hidden("old_value_$i", $row->value); ?>
				<div class="employee-attribute-field"><label for="value_<?php echo $i ?>"><?php echo htmlspecialchars(formatCase($row->description)) ?></label><div><?php numberBox("value_$i", $row->value) ?><a href="employee_history.php?employeeid=<?php echo urlencode($employeeid) ?>&attributeid=<?php echo urlencode($row->attributeid) ?>" title="<?php etr("History") ?>"><?php image('history.gif') ?></a></div></div>
			<?php $i++; } hidden('count', $i); ?></div><?php } ?>
			<?php if ($teams != null) { ?><div class="employee-teams"><label><?php etr("Teams") ?></label><div class="employee-team-list">
			<?php $teamCount = 0; while ($row = fetch($teams)) { $teamCount++; ?><span class="employee-team-chip"><?php echo htmlspecialchars($row->description) ?><?php deleteIcon("employee_detail.php?employeeid=" . urlencode($employeeid) . "&del_teamid=" . urlencode($row->teamid)) ?></span><?php } ?>
			<?php if ($teamCount == 0) { ?><span class="employee-team-empty"><?php etr("No teams assigned") ?></span><?php } ?></div><div class="employee-team-add"><?php comboBox('teamid_new', $allTeams, null, true) ?></div></div><?php } ?>
		</div>
	</section>

	<section class="employee-detail-card card border-0 shadow-sm">
		<div class="card-header bg-white employee-detail-card-header"><div><span><?php etr("Payroll and address") ?></span><h2><?php etr("Payment and contact details") ?></h2></div></div>
		<div class="card-body"><div class="row g-3">
			<div class="col-12"><label class="form-label fw-semibold" for="bank_account"><?php etr("Bank account") ?></label><input id="bank_account" type="text" name="bank_account" value="<?php echo htmlspecialchars($emp->bank_account) ?>" /></div>
			<div class="col-12"><label class="form-label fw-semibold" for="street_address"><?php etr("Street") ?></label><?php textbox('street_address', $emp->street_address, 60) ?></div>
			<div class="col-12 col-md-4"><label class="form-label fw-semibold" for="zipcode"><?php etr("Zipcode") ?></label><?php textbox('zipcode', $emp->zipcode, 15) ?></div>
			<div class="col-12 col-md-8"><label class="form-label fw-semibold" for="city"><?php etr("City") ?></label><?php textbox('city', $emp->city, 30) ?></div>
		</div></div>
	</section>

	<div class="employee-detail-actions"><?php saveButton() ?></div>
	<input type="hidden" name="new" value="<?php echo $new ?>" />
	</form>
</main>

<?php bottom() ?>
</body>
