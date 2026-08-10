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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Description") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="description" value="<?php echo $description ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><input type="submit" name="search" value="<?php etr("Search") ?>" /></div></div>

</div>
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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php newButton("project.php") ?></div>
<div class="col-12 col-md-auto"><?php saveButton() ?></div>
</div>
</div>
</form>
</body>
