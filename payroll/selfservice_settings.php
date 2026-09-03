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

<form name="form1" action="selfservice_settings.php" method="POST">
<div class="card border-0 shadow-sm">
<div class="card-header bg-white py-3">
<h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars(trim($emp->givenname . ' ' . $emp->surname)) ?></h2>
<p class="text-secondary small mb-0"><?php echo tr("Settings") ?></p>
</div>
<div class="card-body p-4">
<div class="row g-4">
<div class="col-12 col-lg-6"><label class="form-label fw-semibold" for="givenname"><?php echo tr("Givenname") ?></label><input class="form-control" id="givenname" type="text" name="givenname" value="<?php echo htmlspecialchars($emp->givenname) ?>"/></div>
<div class="col-12 col-lg-6"><label class="form-label fw-semibold" for="surname"><?php echo tr("Surname") ?></label><input class="form-control" id="surname" type="text" name="surname" value="<?php echo htmlspecialchars($emp->surname) ?>"/></div>
<?php

if ($teams != null) {
	echo "<div class='col-12'><label class='form-label fw-semibold'>" . tr("Teams") . "</label>";
	echo "<div class='d-flex flex-wrap align-items-center gap-2'>";
	while ($row = fetch($teams)) {
		$href = "selfservice_settings.php?del_teamid=$row->teamid";
		echo "<span class='badge text-bg-light border d-inline-flex align-items-center gap-2'>" . htmlspecialchars($row->description);
		deleteIcon($href);
		echo "</span>";
	}
	comboBox('teamid_new', $allTeams, null, true);
	echo "</div></div>";
}
?>
<div class="col-12 col-lg-6"><label class="form-label fw-semibold" for="bank_account"><?php echo tr("Bank account") ?></label><input class="form-control" id="bank_account" type="text" name="bank_account" value="<?php echo htmlspecialchars($emp->bank_account) ?>"/></div>
<div class="col-12"><label class="form-label fw-semibold" for="street_address"><?php etr("Street") ?></label><?php textbox('street_address', $emp->street_address, 60) ?></div>
<div class="col-12 col-lg-4"><label class="form-label fw-semibold" for="zipcode"><?php etr("Zipcode") ?></label><?php textbox('zipcode', $emp->zipcode, 15) ?></div>
<div class="col-12 col-lg-8"><label class="form-label fw-semibold" for="city"><?php etr("City") ?></label><?php textbox('city', $emp->city, 30) ?></div>
</div>
</div>
<div class="card-footer bg-white d-flex flex-wrap gap-2 py-3"><?php saveButton() ?></div>
</div>
</form>
<?php bottom() ?>

</body>
