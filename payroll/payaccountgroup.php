<?php
	include('include.php');

	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);
	
	$groupid = getParam('groupid');
	$new = true;
	if (isSave()) {
		$groupid = getParam('groupid');
		$description = getParam('description');
		$name = getParam('name');
		$report = prepNull(getParam('report'));
		if (isNew()) {
			$sql = "insert into payaccountgroup (groupid, name, report, description)
			        values ($groupid, '$name', $report, '$description')";
			sql($sql);
			header("Location: payaccountgroups.php");
			die;
		} else {
            $updateSQL =
    			"update payaccountgroup set
    			    name='$name',
					report=$report,
					description='$description'
                where groupid=$groupid";
    		sql($updateSQL);
		}
	}

	$rec = new Dummy();
	if (!isEmpty($groupid)) {
	    $selectSQL =
  		"select g.groupid,
		       description,
		       name,
			   report
		from payaccountgroup g
		where g.groupid=$groupid
		";
		$rec = find($selectSQL, true);
		$new = false;
	}

?>
<head>
<title>Payroll - <?php etr("Pay account group") ?></title>
<?php
include_common();
styleSheet();
?>
</head>

<body>
<?php menubar("configuration.php") ?>
<?php
$title = $rec->description;
if ($new)
	$title = tr("Create payaccount group");
title("<a href='payaccountgroups.php'>" . tr("Pay account groups") . "</a> > $title")
?>

<form action="payaccountgroup.php" method="POST">
<table>
<tr>
	<td><?php etr("Id") ?>:</td>
	<td><?php numberbox('groupid', $groupid) ?></td>
</tr>
<tr><td><?php etr("Name") ?>:</td><td><input type="text" name="name" value="<?php echo $rec->name ?>"/></td>
<tr><td><?php etr("Description") ?>:</td><td><input type="text" name="description" value="<?php echo $rec->description ?>"/></td>
<?php hidden('old_description', $rec->description) ?>
<tr><td><?php etr("Show in report") ?>:</td><td><?php checkBox('report', $rec->report) ?></td>
<tr>
<td colspan=2>
<?php saveButton() ?>
&nbsp;
</td>
</tr>
</table>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
