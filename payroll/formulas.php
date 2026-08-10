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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Name") ?>:</div>
	<div class="col-12 col-md-auto"><?php textbox('name', $name) ?></div>
	</div><div class="row g-3 align-items-center mb-2">
		<div class="col-12 col-md-auto"><input type="submit" name="search" value="<?php etr("Search") ?>" /></div>
	</div>

</div>
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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php newButton("formula.php") ?></div>
</div>
</div>
</form>
<?php bottom() ?>
</body>
