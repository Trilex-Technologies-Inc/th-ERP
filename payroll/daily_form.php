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
<table>
<tr>
<td><?php echo tr("Id") ?>:</td>
<td>
<?php 
if ($new)
	textbox('formid', '');
else 
	echo $formid 
?>
</td>
</tr>
<tr>
<td><?php echo tr("Description") ?>:</td>
<td><?php textbox('description', $policy->description, 40) ?></td>
</tr>
<tr>
<td><?php echo tr("Team") ?>:</td>
<td><?php comboBox("teamid", $teams, $policy->teamid, false) ?></td>
</tr>
<tr>
<td><?php echo tr("Pay account group") ?>:</td>
<td><?php comboBox("groupid", $groups, $policy->groupid, false) ?></td>
</tr>

<tr height='10'/>
<tr>
<td colspan='2'>
  <input type="submit" name="save" value="<?php echo tr("Save") ?>"/>
</td>
</tr>
</table>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
</html>
