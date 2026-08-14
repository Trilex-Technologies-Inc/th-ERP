<?php
include('include.php');
include('policy.inc');

checkPermission(PERMISSION_CONFIGURATE_PAYROLL);

$del_teamid = getParam('del_teamid');
if (!isEmpty($del_teamid)) {
	$sql = "
	delete from team
	where teamid=$del_teamid";
	sql($sql);
}

if (isSave()) {
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$teamid = getParam("teamid_$i");
		$description = getParam("description_$i");
		if ($description != getParam("old_description_$i")) {
			sql("update team set description='$description' where teamid=$teamid");
		}
		$i++;
	}
	$teamid_new = getParam('teamid_new');
	$description_new = getParam('description_new');
	if (!isEmpty($teamid_new)) {
		$sql = "
		insert into team (teamid, description)
		values ($teamid_new, '$description_new')";
		sql($sql);
	}
}

$sql = "
select
  a.teamid,
  description
from team a
";

$rs = query($sql);
?>

<html>
<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Teams") ?></title>
<?php styleSheet() ?>
<LINK REL=StyleSheet HREF="tabs.css" TYPE="text/css">
</head>
<body>

<?php
menubar("configuration.php");
title(tr("Teams"))
?>

<form action="teams.php" method="POST">
<div class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white py-3">
	<h2 class="h5 fw-bold mb-1"><?php echo tr("Teams") ?></h2>
	<p class="text-secondary small mb-0"><?php echo tr("Employee teams") ?></p>
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
	$teamid = htmlspecialchars($row->teamid);
    echo "<tr>";
    echo "<td class='text-center'>";
	deleteIcon("teams.php?del_teamid=$teamid");
    echo "</td>";
    echo "<td class='text-end font-monospace'>$teamid<input type='hidden' name='teamid_$i' value='$teamid'/></td>";
    echo "<td>";
    textBox("description_$i", $row->description);
    echo "</td>";
    hidden("old_description_$i", $row->description);
    echo "</tr>";
    $i++;
}
hidden('count', $i);
?>
<tr class="table-light">
<td class="text-center text-secondary fw-semibold">+</td>
<td><?php textBox('teamid_new', '', 6) ?></td>
<td><?php textBox('description_new', '') ?></td>
</tr>
</tbody>
</table>
</div>
<div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
	<?php saveButton() ?>
	<span class="text-secondary small"><?php echo $i ?> <?php echo tr("records") ?></span>
</div>
</div>
</form>
<?php bottom() ?>
</body>
</html>
