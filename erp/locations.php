<?php
	include('include.php');

    $name = getParam('name');

	$selectSQL = "
	select
	    locationid,
	    name
	from location
	where name like '$name%'";

?>

<head>
<title>thERP - <?php etr("Locations") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar('configuration.php') ?>
<?php title(tr("Locations")) ?>

<form action="locations.php" method="GET">
<div class="border">
<table>
<tr>
	<td><?php etr("Name") ?>:</td>
	<td><?php textbox('name', $name) ?></td>
<tr><td><?php searchButton() ?></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="locations.php" method=POST>
<table>
<th><?php etr("Id") ?></th>
<th><?php etr("Name") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
    	$href = "location.php?locationid=$row->locationid";
        echo "<tr class='$class'>";
        echo "<td>$row->locationid</td>";
        echo "<td><a href='$href'>$row->name</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<table>
<tr>
<td><?php newButton("location.php") ?></td>
<td><?php saveButton() ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
