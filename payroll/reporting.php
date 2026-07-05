<?php
include('include.php');

$mode = getParam("mode");

$sql = "
select
  a.formid,
  description
from daily_form a
";

$rs = query($sql);
?>

<html>
<?php head("Reporting") ?>
<body>

<?php
top("reporting.php", "Reporting");
?>

<form action="daily_forms.php" method="POST">
<ul>
<li><a href='inout.php'><?php etr("In/Out") ?></a></li>
<?php
while ($row = fetch($rs)) {
    echo "<li>";
	$href = "attendence_day.php?formid=$row->formid";
    echo "<a href='$href'>";
    echo $row->description;
    echo "</a>";
    echo "</li>";
}
?>
</ul>
</form>
<?php bottom() ?>
</body>
</html>
