<?php
	include('include.php');

	checkPermission(PERMISSION_CONFIGURATE_PAYROLL);

    $description = getParam('description');

	$del_apid = getParam('del_apid');
	if (!isEmpty($del_apid)) {
		sql("delete from advanced_percent where apid=$del_apid");
	}

	$selectSQL = "
	select
	    apid,
	    name,
	    description
	from advanced_percent
	where description like '$description%'";

?>

<head>
<title>Payroll - <?php etr("Advanced perccent") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("configuration.php") ?>
<?php title(tr("Advanced percent")) ?>

<form action="advanced_percents.php" method="GET">
<div class="border">
<table>
<tr><td><?php etr("Description") ?>:</td><td><input type="text" name="description" value="<?php echo $description ?>"/></td>
<tr><td><input type="submit" name="search" value="<?php etr("Search") ?>" /></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="advanced_percents.php" method=POST>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Id") ?></th>
<th><?php etr("Name") ?></th>
<th><?php etr("Description") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		echo "<td align=center>";
		deleteIcon("advanced_percents.php?del_apid=$row->apid");
		echo "</td>";
        echo "<td>$row->apid</td>";
        echo "<td><a href='advanced_percent.php?apid=$row->apid'>$row->name</a></td>";
        echo "<td>$row->description</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<br/>
<table>
<tr>
<td><?php newButton("advanced_percent.php") ?></td>
<td><?php saveButton() ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
