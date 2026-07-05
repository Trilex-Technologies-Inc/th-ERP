<?php
	include('include.php');

	checkPermission(PERMISSIONID_MANAGE_PRODUCTS);
	
	$vatcatid = getParam('vatcatid');
	$new = true;
	if (isSave()) {
		$vatcatid = getParam('vatcatid');
		$description = getParam('description');
		$percent = getParam('percent');
		if (isNew()) {
			$sql = "insert into vat_category (description, percent)  
			        values ('$description', $percent)";
			sql($sql);
			$vatcatid = insert_id();
		} else {
            $updateSQL =
    			"update vat_category set
    				description='$description',
    			    percent=$percent
                where vatcatid='$vatcatid'";
    		sql($updateSQL);
		}
	}
	if (isDelete()) {
		sql("delete from vat_category where vatcatid='$vatcatid'");
		$vatcatid = null;
	}

	$rec = new Dummy();
	if (!isEmpty($vatcatid)) {
	    $selectSQL =
  		"select vatcatid,
  		       description,
			   percent
		from vat_category
		where vatcatid='$vatcatid'
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$new = false;
		}
	}

?>
<head>
<title>thERP - <?php etr("VAT category") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar('configuration.php') ?>
<?php title("<a href='vatcategories.php'>" . tr("VAT categories") . "</a> > $rec->description") ?>

<form action="vatcategory.php" method="POST">
<table>
<tr><td>Id:</td>
<td>
<?php
	if ($new) {
	} else {
		echo $vatcatid;
		echo "<input type='hidden' name='vatcatid' value='$vatcatid'/>";
	}
?>
</td>
<tr><td><?php etr("Description") ?>:</td><td><input type="text" name="description" value="<?php echo $rec->description ?>"/></td>
<tr><td><?php etr("Percent") ?>:</td><td><?php numberbox("percent", $rec->percent) ?></td>

<tr>
<td colspan=2>
<?php 
saveButton();
echo "&nbsp;";
deleteButton();
?>
&nbsp;
</td>
</tr>
</table>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
