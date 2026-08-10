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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Id") ?>:</div>
	<div class="col-12 col-md-auto"><input type=text name='dimid' value='<?php echo $dimid ?>'/></div>
</div>
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Name") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="name" value="<?php echo $name ?>"/></div>
<?php $checked = $type ? 'checked' : '' ?>
</div><div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">
<?php saveButton() ?>
&nbsp;
</div>
</div>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
