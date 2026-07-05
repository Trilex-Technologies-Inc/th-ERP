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

<form action="formulas.php" method="GET">
<div class="border">
<table>
<tr>
	<td><?php etr("Name") ?>:</td>
	<td><?php textbox('name', $name) ?></td>
	<tr>
		<td><input type="submit" name="search" value="<?php etr("Search") ?>" /></td>
	</tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="formulas.php" method=POST>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Id") ?></th>
<th><?php etr("Name") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		echo "<td align=center>";
		echo deleteIcon("formulas.php?del_formulaid=$row->formulaid");
		echo "</td>";
        echo "<td align=right>$row->formulaid</td>";
        echo "<td><a href='formula.php?formulaid=$row->formulaid'>$row->name</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<table>
<tr>
<td><?php newButton("formula.php") ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
