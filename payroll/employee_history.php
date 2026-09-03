<?php
include('include.php');
$employeeid = getParam('employeeid');
$attributeid = getParam('attributeid');
$periodid = getCurrentPeriod();
$policyid = getPolicy($employeeid, $periodid);

$attrname = findValue("select name from policy_attribute pa
                       join attribute a on a.attributeid=pa.attributeid
                       where policyid=$policyid and a.attributeid=$attributeid");

$sql = "
select
  ea.value,
  unix_timestamp(ea.fromtime) as starttime,
  type
from emp_attribute ea
join attribute a on a.attributeid=ea.attributeid
where ea.employeeid=$employeeid and ea.attributeid=$attributeid
";

$rs = query($sql);

?>

<head>
<?php metatag() ?>
<title>Payroll - <?php etr("Employee attribute history") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php include("menubar.php") ?>
<?php
$emp = find("select givenname, surname from employee where employeeid=$employeeid");
title("Employees > <a href='employee_detail.php?employeeid=$employeeid'>$emp->givenname $emp->surname</a> > $attrname > History");
?>

<div class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white py-3">
<h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($attrname) ?></h2>
<p class="text-secondary small mb-0"><?php etr("Employee attribute history") ?></p>
</div>
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead><tr><th><?php etr("From") ?></th><th class="text-end"><?php etr("Value") ?></th></tr></thead>
<tbody>
<?php
	$count = 0;
    while ($row = fetch_object($rs)) {
        $count++;
        echo "<tr>";
        echo "<td>" . date(DATE_PATTERN, $row->starttime) . "</td>";
        echo "<td class='text-end'>";
		if ($row->type == ATTRIBUTE_TYPE_BOOLEAN)
			echo $row->value ? tr("Yes") : tr("No");
		else
			printf("%9.2f", $row->value);
        echo "</td>";
        echo "</tr>";
    }
    if ($count == 0)
        echo "<tr><td colspan='2' class='text-center text-secondary py-5'>" . tr("No records found") . "</td></tr>";
?>
</tbody>
</table>
</div>
</div>
<?php bottom() ?>
</body>
