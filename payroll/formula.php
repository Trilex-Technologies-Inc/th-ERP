<?php
	include('include.php');
	
	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);

	$formulaid = getParam('formulaid');
	$new = true;
	if (isSave()) {
		$formulaid = getParam('formulaid');
		$name = getParam('name');
		$formula = getParam('formula');
		if (isNew()) {
			$sql = "insert into formula (formulaid, expression, name)
			        values ($formulaid, '$expression', '$name')";
			sql($sql);
			header("Location: formulas.php");
			die;
		} else {
            $updateSQL =
    			"update formula set
    			    expression='$expression',
					name='$name'
                where formulaid=$formulaid";
    		sql($updateSQL);
		}
	}

	$groups = null;
	$row = new Dummy();
	if (!isEmpty($formulaid)) {
	    $selectSQL =
  		"select a.formulaid,
		       name,
		       expression
		from formula a
		where a.formulaid=$formulaid
		";
		$row = find($selectSQL, true);
		if ($row != null) {
			$new = false;
		}
	}

?>
<?php head("Formula") ?>

<body>
<?php menubar("configuration.php") ?>
<?php
$title = $row->name;
if ($new)
	$title = tr("Create formula");
title("<a href='formulas.php'>" . tr("Pay accounts") . "</a> > $title")
?>

<form action="formula.php" method="POST">
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Id") ?>:</div>
	<div class="col-12 col-md-auto"><?php numberbox('formulaid', $formulaid, 5) ?></div>
</div>
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Name") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="name" value="<?php echo $row->name ?>" size='40' /></div>
<?php hidden('old_name', $row->name) ?>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Expression") ?>:</div><div class="col-12 col-md-auto"><textarea name='expression' cols=60 rows=5><?php echo $row->expression ?></textarea></div>
</div></div>
<br/>
<?php saveButton() ?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
