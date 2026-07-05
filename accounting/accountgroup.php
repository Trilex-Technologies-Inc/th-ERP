<?php
	include('include.php');

	$groupid = getParam('groupid');
	$new = true;
	$description = "";
	$type = 0;
	if (isSave()) {
		$groupid = getParam('groupid');
		$description = getParam('description');
		if (isNew()) {
			$sql = "insert into accountgroup (groupid, description)  values (";
			$sql = $sql . "$groupid,";
			$sql = $sql . "'$description'";
			$sql = $sql . ")";
			sql($sql);
			$groupid = insert_id();
			header("Location: accountgroups.php");
			die;
		} else {
            $updateSQL =
    			"update accountgroup set
    			    description='$description'
                where groupid=$groupid";
    		sql($updateSQL);
		}
	}

	if (!isEmpty($groupid)) {
	    $selectSQL =
  		"select groupid,
		       description
		from accountgroup
		where groupid=$groupid
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$groupid = $rec->groupid;
			$description = $rec->description;
			$new = false;
		}
	}

?>
<head>
<title>thERP - <?php etr("Account group") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php include("menubar.php") ?>
<?php
$title = $description;
if ($new)
	$title = tr("Create account group");
title("<a href='accountgroups.php'>" . tr("Account groups") . "</a> > $title") 
?>

<form action="accountgroup.php" method="POST">
<table>
<tr>
	<td><?php etr("Id") ?>:</td>
	<td><input type=text name='groupid' value='<?php echo $groupid ?>'/></td>
</tr>
<tr><td><?php etr("Description") ?>:</td><td><input type="text" name="description" value="<?php echo $description ?>"/></td>
<?php $checked = $type ? 'checked' : '' ?>
<tr>
<td colspan=2>
<?php saveButton() ?>
&nbsp;
</td>
</tr>
</table>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>

</body>
