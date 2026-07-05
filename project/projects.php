<?php
	include('include.php');

    $description = getParam('description');

	$del_projectid = getParam('del_projectid');
	if (!isEmpty($del_projectid)) {
		sql("delete from task where projectid=$del_projectid");
		sql("delete from project where projectid=$del_projectid");
	}
	
	$selectSQL = "
	select
	    projectid,
	    description
	from project
	where description like '$description%'";

?>

<head>
<title>thERP - <?php etr("Projects") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php top("projects", "Projects") ?>

<form action="projects.php" method="GET">
<div class="border">
<table>
<tr><td><?php etr("Description") ?>:</td><td><input type="text" name="description" value="<?php echo $description ?>"/></td>
<tr><td><input type="submit" name="search" value="<?php etr("Search") ?>" /></td></tr>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="projects.php" method=POST>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Id") ?></th>
<th><?php etr("Description") ?></th>
<?php
    $rs = query($selectSQL);
    $class = "odd";
    while ($row = fetch_object($rs)) {
        echo "<tr class='$class'>";
		deleteColumn("projects.php?del_projectid=$row->projectid");
        echo "<td>$row->projectid</td>";
        echo "<td><a href='project.php?projectid=$row->projectid'>$row->description</a></td>";
        echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
    }
?>
</table>
<table>
<tr>
<td><?php newButton("project.php") ?></td>
<td><?php saveButton() ?></td>
</tr>
</table>
</form>
</body>
