<?php
	include('include.php');
	include('policy.inc');
	
	$formid = getParam("formid");
	$new = true;
	
	if (isSave()) {
		$teamid = prepNull(getParam('teamid'));
		$groupid = prepNull(getParam('groupid'));
		$description = getParam('description');
		if (isNew()) {
			sql("insert into daily_form (formid, teamid, groupid, description)
			     values ($formid, $teamid, $groupid, '$description')");			
		} else {
			sql("update daily_form set
				   teamid=$teamid,
				   groupid=$groupid,
				   description='$description'
				 where formid=$formid");
		}
	}

	$policy = new Dummy();
	if (!isEmpty($formid)) {
		$sql = "select
		          description,
				  teamid,
				  groupid
				from daily_form
				where formid=$formid
				";
		$policy = find($sql, true);
		$new = false;
	}

	$groups = rs2array(query("select a.groupid, description 
	                            from payaccountgroup a"));
	$teams = rs2array(query("select teamid, description from team"));

?>
<html>
<?php head("Daily form") ?>
<body>

<?php
$title = "<a href='daily_forms.php'>" . tr("Daily forms") . "</a>";
top("configuration.php", "Daily form", $title);
?>

<form action="daily_form.php" method="POST">
<div class="card border-0 shadow-sm">
<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
<div><h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($new ? tr("Daily form") : $policy->description) ?></h2><p class="text-secondary small mb-0"><?php echo tr("Daily forms") ?></p></div>
<span class="badge text-bg-light border"><?php echo $new ? tr("New") : '#' . htmlspecialchars($formid) ?></span>
</div>
<div class="card-body p-4"><div class="row g-4">
<div class="col-12 col-lg-3"><label class="form-label fw-semibold" for="formid"><?php echo tr("Id") ?></label><?php if ($new) { ?><input class="form-control" id="formid" type="text" name="formid" required /><?php } else { ?><div class="form-control-plaintext font-monospace"><?php echo htmlspecialchars($formid) ?></div><input type="hidden" name="formid" value="<?php echo htmlspecialchars($formid) ?>"/><?php } ?></div>
<div class="col-12 col-lg-9"><label class="form-label fw-semibold" for="description"><?php echo tr("Description") ?></label><input class="form-control" id="description" type="text" name="description" value="<?php echo htmlspecialchars($policy->description) ?>" required /></div>
<div class="col-12 col-lg-6"><label class="form-label fw-semibold" for="teamid"><?php echo tr("Team") ?></label><?php comboBox("teamid", $teams, $policy->teamid, false) ?></div>
<div class="col-12 col-lg-6"><label class="form-label fw-semibold" for="groupid"><?php echo tr("Pay account group") ?></label><?php comboBox("groupid", $groups, $policy->groupid, false) ?></div>
</div></div>
<div class="card-footer bg-white d-flex flex-wrap gap-2 py-3"><input type="submit" name="save" value="<?php echo tr("Save") ?>"/><a class="btn btn-outline-secondary" href="daily_forms.php"><?php etr("Back") ?></a></div>
</div>
<?php if ($new) { ?><input type="hidden" name="new" value="1"/><?php } ?>
</form>
<?php bottom() ?>
</body>
</html>
