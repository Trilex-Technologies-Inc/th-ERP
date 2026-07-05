<?php
	include('include.php');

    $description = getParam('description');

	$selectSQL = "
	select
	    vatcatid,
	    description,
		percent
	from vat_category
	where description like '$description%'";

?>

<head>
<title>thERP - <?php etr("VAT categories") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('configuration.php') ?>
<?php title(tr("VAT categories")) ?>

<form action="vatcategories.php" method="GET">
<div class="border">
<table>
<tr><td><?php etr("Description") ?>:</td><td><input type="text" model="description" value="<?php echo $description ?>"/></td>
<tr><td><input type="submit" model="search" value="<?php etr("Search") ?>" /></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="vatcategories.php" method=POST>
<table>
<th><?php etr("Id") ?></th>
<th><?php etr("Description") ?></th>
<th><?php etr("Percent") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
    	$href = "vatcategory.php?vatcatid=$row->vatcatid";
        echo "<tr class='$class'>";
        echo "<td>$row->vatcatid</td>";
        echo "<td><a href='$href'>$row->description</a></td>";
        echo "<td align=right>$row->percent</td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<table>
<tr>
<td><?php newButton("vatcategory.php") ?></td>
<td><?php saveButton() ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
