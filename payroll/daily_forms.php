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
<input type=hidden name=policyid value='<?php echo $policyid ?>'/>
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Id") ?></th>
<th><?php echo tr("Description") ?></th>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name=formid_$i value='$row->formid'/>";
    echo "<tr class='$class'>";
    echo "<td align=center>";
	$href = "daily_forms.php?del_formid=$row->formid";
    echo "<a href='$href'>";
    image("delete.png'");
    echo "</a></td>";
    echo "<td>$row->formid</td>";
	if ($mode == 'select')
		$href = "attendence_day.php?formid=$row->formid";
	else
		$href = "daily_form.php?formid=$row->formid";
    echo "<td><a href='$href'>";
    echo $row->description;
    echo "</a></td>";
    echo "</tr>";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
hidden('count', $i);
?>
</table>
<br/>
<?php
newButton("daily_form.php") ;
?>
</form>
<?php bottom() ?>
</body>
</html>
