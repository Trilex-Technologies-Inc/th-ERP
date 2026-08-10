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
<?php hidden('formid', $formid) ?>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php echo tr("Id") ?>:</div>
<div class="col-12 col-md-auto">
<?php 
if ($new)
	textbox('formid', '');
else 
	echo $formid 
?>
</div>
</div>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php echo tr("Description") ?>:</div>
<div class="col-12 col-md-auto"><?php textbox('description', $policy->description, 40) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php echo tr("Team") ?>:</div>
<div class="col-12 col-md-auto"><?php comboBox("teamid", $teams, $policy->teamid, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php echo tr("Pay account group") ?>:</div>
<div class="col-12 col-md-auto"><?php comboBox("groupid", $groups, $policy->groupid, false) ?></div>
</div>

<div class="row g-3 align-items-center mb-2">
</div><div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">
  <input type="submit" name="save" value="<?php echo tr("Save") ?>"/>
</div>
</div>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
</html>
