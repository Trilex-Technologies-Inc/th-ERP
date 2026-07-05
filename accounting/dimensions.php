<?php
	include('include.php');

    $name = getParam('name');

	$selectSQL = "
	select
	    dimid,
	    name
	from dimension
	where name like '$name%'";

?>

<head>
<title>thERP - <?php etr("Dimensions") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("configuration.php") ?>
<?php title(tr("Dimensions")) ?>

<form action="dimensions.php" method="GET">
<div class="border">
<table>
<tr><td><?php etr("Name") ?>:</td>
<td><?php textbox("name", $name) ?>&nbsp;
<?php searchButton() ?>
</td></tr>
</tr>
</table>
</div>
</form>

<form action="dimensions.php" method=POST>
<table>
<th><?php etr("Id") ?></th>
<th><?php etr("Name") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
        echo "<td>$row->dimid</td>";
        echo "<td><a href='dimension.php?dimid=$row->dimid'>$row->name</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<br/>
<table>
<tr>
<td><?php newButton("dimension.php") ?></td>
<td><?php saveButton() ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
