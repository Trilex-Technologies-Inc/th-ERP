<?php
	include('include.php');

	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);

    $name = getParam('name');

	$del_formulaid = getParam('del_formulaid');
	if (!isEmpty($del_formulaid)) {
		sql("delete from formula where formulaid=$del_formulaid");
	}

	$selectSQL = "
	select
	    a.formulaid,
	    name
	from formula a
	where name like '$name%'";

?>

<?php head("Formulas") ?>

<body>

<?php menubar("configuration.php") ?>
<?php title(tr("Formulas")) ?>

<form action="formulas.php" method="GET" class="mb-4">
	<div class="card border-0 shadow-sm">
		<div class="card-body">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h2 class="h6 fw-bold mb-0"><?php etr("Search") ?></h2>
				<span class="text-secondary small"><?php etr("Formulas") ?></span>
			</div>
			<div class="row g-3 align-items-end">
				<div class="col-12 col-md-8 col-lg-6">
					<label class="form-label fw-semibold" for="name"><?php etr("Name") ?></label>
					<input class="form-control" id="name" type="text" name="name" value="<?php echo htmlspecialchars($name) ?>"/>
				</div>
				<div class="col-12 col-md-auto">
					<input type="submit" name="search" value="<?php etr("Search") ?>" />
				</div>
			</div>
		</div>
	</div>
</form>

<form action="formulas.php" method="POST">
	<div class="card border-0 shadow-sm overflow-hidden">
		<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
			<div>
				<h2 class="h5 fw-bold mb-1"><?php etr("Formulas") ?></h2>
				<p class="text-secondary small mb-0"><?php etr("Payroll calculation formulas") ?></p>
			</div>
			<?php newButton("formula.php") ?>
		</div>
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead>
					<tr>
						<th class="text-center" style="width: 90px;"><?php etr("Delete") ?></th>
						<th class="text-end" style="width: 120px;"><?php etr("Id") ?></th>
						<th><?php etr("Name") ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				    $rs = query($selectSQL);
				    $count = 0;
				    while ($row = fetch_object($rs)) {
				    	$count++;
				    	$formulaid = htmlspecialchars($row->formulaid);
				    	$formulaName = htmlspecialchars($row->name);
				        echo "<tr>";
						echo "<td class='text-center'>";
						deleteIcon("formulas.php?del_formulaid=$formulaid");
						echo "</td>";
				        echo "<td class='text-end font-monospace'>$formulaid</td>";
				        echo "<td><a class='fw-semibold' href='formula.php?formulaid=$formulaid'>$formulaName</a></td>";
				        echo "</tr>";
				    }
				    if ($count == 0)
				    	echo "<tr><td colspan='3' class='text-center text-secondary py-5'>" . tr("No records found") . "</td></tr>";
				?>
				</tbody>
			</table>
		</div>
		<div class="card-footer bg-white d-flex justify-content-end py-3">
			<span class="text-secondary small"><?php echo $count ?> <?php etr("records") ?></span>
		</div>
	</div>
</form>
<?php bottom() ?>
</body>
