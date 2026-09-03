<?php
	include('include.php');
	
	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);

	$formulaid = getParam('formulaid');
	$new = true;
	if (isSave()) {
		$formulaid = getParam('formulaid');
		$name = getParam('name');
		$expression = getParam('expression');
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
if ($new || isEmpty($title))
	$title = tr("Create formula");
	title("<a href='formulas.php'>" . tr("Formulas") . "</a> > " . htmlspecialchars($title))
?>

<form action="formula.php" method="POST" class="formula-editor">
	<div class="card border-0 shadow-sm">
		<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
			<div>
				<h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($title) ?></h2>
				<p class="text-secondary small mb-0"><?php etr("Formula") ?></p>
			</div>
			<span class="badge text-bg-light border"><?php echo $new ? tr("New") : '#' . htmlspecialchars($formulaid) ?></span>
		</div>
		<div class="card-body p-4">
			<div class="row g-4">
				<div class="col-12 col-lg-3">
					<label class="form-label fw-semibold" for="formulaid"><?php etr("Id") ?></label>
					<?php if ($new) { ?>
						<?php numberbox('formulaid', $formulaid, 5) ?>
					<?php } else { ?>
						<div class="form-control-plaintext font-monospace"><?php echo htmlspecialchars($formulaid) ?></div>
						<input type="hidden" name="formulaid" value="<?php echo htmlspecialchars($formulaid) ?>"/>
					<?php } ?>
				</div>
				<div class="col-12 col-lg-9">
					<label class="form-label fw-semibold" for="name"><?php etr("Name") ?></label>
					<input class="form-control" id="name" type="text" name="name" value="<?php echo htmlspecialchars($row->name) ?>" maxlength="40" required />
					<?php hidden('old_name', $row->name) ?>
				</div>
				<div class="col-12">
					<label class="form-label fw-semibold" for="expression"><?php etr("Expression") ?></label>
					<textarea class="form-control font-monospace" id="expression" name="expression" rows="8" spellcheck="false" required><?php echo htmlspecialchars($row->expression) ?></textarea>
				</div>
			</div>
		</div>
		<div class="card-footer bg-white d-flex flex-wrap gap-2 py-3">
			<?php saveButton() ?>
			<a class="btn btn-outline-secondary" href="formulas.php"><?php etr("Back") ?></a>
		</div>
	</div>
	<?php if ($new) { ?>
		<input type="hidden" name="new" value="1"/>
	<?php } ?>
</form>
<?php bottom() ?>
</body>
