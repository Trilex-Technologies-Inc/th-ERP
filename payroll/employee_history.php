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

<div class="border">

<table>
<th><?php etr("From") ?></th>
<th><?php etr("Value") ?></th>
<?php
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
        echo "<td>" . date(DATE_PATTERN, $row->starttime) . "</td>";
        echo "<td align=right>";
		if ($row->type == ATTRIBUTE_TYPE_BOOLEAN)
			echo $row->value ? tr("Yes") : tr("No");
		else
			printf("%9.2f", $row->value);
        echo "</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
</body>
