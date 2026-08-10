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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto">Id:</div>
<div class="col-12 col-md-auto">
<?php
	if ($new) {
	} else {
		echo $vatcatid;
		echo "<input type='hidden' name='vatcatid' value='$vatcatid'/>";
	}
?>
</div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Description") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="description" value="<?php echo $rec->description ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Percent") ?>:</div><div class="col-12 col-md-auto"><?php numberbox("percent", $rec->percent) ?></div>

</div><div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">
<?php 
saveButton();
echo "&nbsp;";
deleteButton();
?>
&nbsp;
</div>
</div>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
