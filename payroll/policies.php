<?php
include('include.php');

$del_policyid = getParam("del_policyid");
if (!isEmpty($del_policyid)) {
	sql("delete from policy_description where policyid=$del_policyid");
	sql("delete from policy_attribute where policyid=$del_policyid");
	sql("delete from policy_payitem where policyid=$del_policyid");
	sql("delete from policy where policyid=$del_policyid");
}

?>

<html>
<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Policies") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar("configuration.php", "policy") ?>
<?php title(tr("Policies")) ?>

<form action="policies.php" method="POST">
<div class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
	<div>
		<h2 class="h5 fw-bold mb-1"><?php echo tr("Policies") ?></h2>
		<p class="text-secondary small mb-0"><?php echo tr("Policy rules") ?></p>
	</div>
	<?php button("Add", "add", "policy.php") ?>
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

$sql = "select
          p.policyid,
          description
        from policy p
        left outer join policy_description d on d.policyid=p.policyid and language='" . getLanguage() . "'
        order by policyid
        ";
$q = sql($sql);
$count = 0;
while ($rec = fetch($q)) {
	$count++;
	$policyid = htmlspecialchars($rec->policyid);
	$description = htmlspecialchars($rec->description);
	echo "<tr>";
	echo "<td class='text-center'>";
	deleteIcon("policies.php?del_policyid=$policyid");
	echo "</td>";
	echo "<td class='text-end font-monospace'>$policyid</td>";
	echo "<td><a class='fw-semibold' href='policy.php?policyid=$policyid'>$description</a></td>";
	echo "</tr>\n";
}
if ($count == 0)
	echo "<tr><td colspan='3' class='text-center text-secondary py-5'>" . tr("No records found") . "</td></tr>";
?>
</tbody>
</table>
</div>
<div class="card-footer bg-white d-flex justify-content-end py-3">
	<span class="text-secondary small"><?php echo $count ?> <?php echo tr("records") ?></span>
</div>
</div>
</form>
<?php bottom() ?>
</body>
</html>
