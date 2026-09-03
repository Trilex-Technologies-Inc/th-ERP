<?php
include('include.php');
include('policy.inc');

$del_unittype = getParam('del_unittype');
if (!isEmpty($del_unittype)) {
	$sql = "
	delete from unittype
	where unittype=$del_unittype";
	sql($sql);
}

if (isSave()) {
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$unittype = getParam("unittype_$i");
		$description = getParam("description_$i");
		if ($description != getParam("old_description_$i")) {
			sql("update unittype set description='$description' where unittype=$unittype");
		}
		$i++;
	}
	$unittype_new = getParam('unittype_new');
	$description_new = getParam('description_new');
	if (!isEmpty($unittype_new)) {
		$sql = "
		insert into unittype (unittype, description)
		values ($unittype_new, '$description_new')";
		sql($sql);
	}
}

$sql = "
select
  a.unittype,
  description
from unittype a
";

$rs = query($sql);
?>

<html>
<head>
<?php metatag() ?>
<title>thERP - <?php echo tr("Unit types") ?></title>
<?php styleSheet() ?>
</head>
<body>

<?php
menubar("configuration.php");
title(tr("Unit types"))
?>

<form action="unittypes.php" method="POST">
<div class="card border-0 shadow-sm overflow-hidden">
	<div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
		<div>
			<h2 class="h5 fw-bold mb-1"><?php echo tr("Unit types") ?></h2>
			<p class="text-secondary small mb-0"><?php echo tr("Configuration") ?></p>
		</div>
		<span class="badge text-bg-light border"><?php echo tr("Unit types") ?></span>
	</div>
	<div class="card-body p-0">
		<div class="row g-0 align-items-center bg-light border-bottom fw-bold px-3 py-3">
			<div class="col-2 col-md-1 text-center"><?php echo tr("Delete") ?></div>
			<div class="col-3 col-md-2 px-2"><?php echo tr("Id") ?></div>
			<div class="col-7 col-md-9"><?php echo tr("Description") ?></div>
		</div>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	$unittype = htmlspecialchars($row->unittype);
	$description = htmlspecialchars($row->description);
	echo "<input type='hidden' name='unittype_$i' value='$unittype'/>";
	echo "<div class='row g-0 align-items-center border-bottom px-3 py-3 $class'>";
	echo "<div class='col-2 col-md-1 text-center'>";
	deleteIcon("unittypes.php?del_unittype=$row->unittype");
	echo "</div>";
	echo "<div class='col-3 col-md-2 px-2'><span class='badge text-bg-light border'>$unittype</span></div>";
	echo "<div class='col-7 col-md-9'><input class='form-control' type='text' name='description_$i' value='$description'/></div>";
	echo "<input type='hidden' name='old_description_$i' value='$description'/>";
	echo "</div>";
	$class = ($class == "odd" ? "even" : "odd");
	$i++;
}
hidden('count', $i);
?>
		<div class="bg-light p-3 p-md-4">
			<h3 class="h6 fw-bold mb-3"><?php echo tr("New") ?> — <?php echo tr("Unit types") ?></h3>
			<div class="row g-3 align-items-end">
				<div class="col-12 col-md-3">
					<label class="form-label fw-semibold" for="unittype_new"><?php echo tr("Id") ?></label>
					<input class="form-control" id="unittype_new" type="number" name="unittype_new"/>
				</div>
				<div class="col-12 col-md-9">
					<label class="form-label fw-semibold" for="description_new"><?php echo tr("Description") ?></label>
					<input class="form-control" id="description_new" type="text" name="description_new"/>
				</div>
			</div>
		</div>
	</div>
	<div class="card-footer bg-white py-3">
		<?php saveButton() ?>
	</div>
</div>
</form>
<?php bottom() ?>
</body>
</html>
