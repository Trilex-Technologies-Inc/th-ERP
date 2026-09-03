<?php
include('include.php');
include('policy.inc');

$mode = getParam("mode");
if ($mode == 'select') {
	checkPermission(PERMISSION_REGISTER_PAYEVENTS);
	$count = findValue("select count(formid) from daily_form");
	if ($count == 1) {
		$formid = findValue("select formid from daily_form");
		header("Location: attendence_day.php?formid=$formid");
	}
} else
	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);


$del_formid = getParam('del_formid');
if (!isEmpty($del_formid)) {
	$sql = "
	delete from daily_form
	where formid=$del_formid";
	sql($sql);
}

$sql = "
select
  a.formid,
  description
from daily_form a
";

$rs = query($sql);
?>

<html>
<?php head("Daily forms") ?>
<body>

<?php
top("daily_forms.php", "Daily forms");
?>

<form action="daily_forms.php" method="POST">
<div class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
	<div>
		<h2 class="h5 fw-bold mb-1"><?php echo tr("Daily forms") ?></h2>
		<p class="text-secondary small mb-0"><?php echo tr("Daily report forms") ?></p>
	</div>
	<?php if ($mode != 'select') newButton("daily_form.php"); ?>
</div>
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead><tr>
<th class="text-center" style="width: 90px;"><?php echo tr("Delete") ?></th>
<th class="text-end" style="width: 120px;"><?php echo tr("Id") ?></th>
<th><?php echo tr("Description") ?></th>
</tr></thead>
<tbody>
<?php
$i = 0;
while ($row = fetch($rs)) {
	$formid = htmlspecialchars($row->formid);
	$description = htmlspecialchars($row->description);
    echo "<tr>";
    echo "<td class='text-center'>";
	deleteIcon("daily_forms.php?del_formid=$formid");
    echo "</td>";
    echo "<td class='text-end font-monospace'>$formid<input type='hidden' name='formid_$i' value='$formid'/></td>";
	if ($mode == 'select')
		$href = "attendence_day.php?formid=$formid";
	else
		$href = "daily_form.php?formid=$formid";
    echo "<td><a class='fw-semibold' href='$href'>$description</a></td>";
    echo "</tr>";
    $i++;
}
hidden('count', $i);
if ($i == 0)
	echo "<tr><td colspan='3' class='text-center text-secondary py-5'>" . tr("No records found") . "</td></tr>";
?>
</tbody>
</table>
</div>
<div class="card-footer bg-white d-flex justify-content-end py-3">
	<span class="text-secondary small"><?php echo $i ?> <?php echo tr("records") ?></span>
</div>
</div>
</form>
<?php bottom() ?>
</body>
</html>
