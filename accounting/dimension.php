<?php
	include('include.php');

	$dimid = getParam('dimid');
	$new = true;
	$name = "";
	$type = 0;
	if (isSave()) {
		$dimid = getParam('dimid');
		$name = getParam('name');
		if (isNew()) {
			$sql = "insert into dimension (dimid, name)  values (";
			$sql = $sql . "$dimid,";
			$sql = $sql . "'$name'";
			$sql = $sql . ")";
			sql($sql);
			$dimid = insert_id();
			header("Location: dimensions.php");
			die;
		} else {
            $updateSQL =
    			"update dimension set
    			    name='$name'
                where dimid=$dimid";
    		sql($updateSQL);
		}
	}

	if (!isEmpty($dimid)) {
	    $selectSQL =
  		"select dimid,
		       name
		from dimension
		where dimid=$dimid
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$dimid = $rec->dimid;
			$name = $rec->name;
			$new = false;
		}
	}

?>
<head>
<title>thERP - <?php etr("Dimension") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar("configuration.php") ?>
<?php
$title = $name;
if ($new)
	$title = tr("Create");
title("<a href='dimensions.php'>" . tr("Dimensions") . "</a> > $title") 
?>

<form action="dimension.php" method="POST">
<table>
<tr>
	<td><?php etr("Id") ?>:</td>
	<td><input type=text name='dimid' value='<?php echo $dimid ?>'/></td>
</tr>
<tr><td><?php etr("Name") ?>:</td><td><input type="text" name="name" value="<?php echo $name ?>"/></td>
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
<?php bottom() ?>
</body>
