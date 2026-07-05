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
<table>
<tr>
	<td class=label><?php etr("Id") ?>:</td>
	<td><?php numberbox('formulaid', $formulaid, 5) ?></td>
</tr>
<tr><td class=label><?php etr("Name") ?>:</td><td><input type="text" name="name" value="<?php echo $row->name ?>" size='40' /></td>
<?php hidden('old_name', $row->name) ?>
<tr><td class=label valign=top><?php etr("Expression") ?>:</td><td><textarea name='expression' cols=60 rows=5><?php echo $row->expression ?></textarea></td>
</table>
<br/>
<?php saveButton() ?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
